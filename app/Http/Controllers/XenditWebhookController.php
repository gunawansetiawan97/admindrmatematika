<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\OrderService;
use App\Services\XenditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XenditWebhookController extends Controller
{
    protected XenditService $xenditService;
    protected OrderService $orderService;

    public function __construct(XenditService $xenditService, OrderService $orderService)
    {
        $this->xenditService = $xenditService;
        $this->orderService = $orderService;
    }

    /**
     * Handle Xendit invoice webhook callback
     */
    public function handleInvoice(Request $request)
    {
        // Verify webhook token
        $callbackToken = $request->header('x-callback-token');

        if (!$this->xenditService->verifyWebhookToken($callbackToken)) {
            Log::warning('Xendit webhook: Invalid callback token');
            return response()->json(['error' => 'Invalid token'], 401);
        }

        $payload = $request->all();

        Log::info('Xendit webhook received', ['payload' => $payload]);

        // Get the external_id which is our order_number
        $orderNumber = $payload['external_id'] ?? null;
        $status = $payload['status'] ?? null;

        if (!$orderNumber || !$status) {
            Log::warning('Xendit webhook: Missing required fields');
            return response()->json(['error' => 'Missing required fields'], 400);
        }

        // Find the order
        $order = Order::where('order_number', $orderNumber)->first();

        if (!$order) {
            Log::warning('Xendit webhook: Order not found', ['order_number' => $orderNumber]);
            return response()->json(['error' => 'Order not found'], 404);
        }

        // Find the payment
        $payment = Payment::where('order_id', $order->id)->first();

        if (!$payment) {
            Log::warning('Xendit webhook: Payment not found', ['order_id' => $order->id]);
            return response()->json(['error' => 'Payment not found'], 404);
        }

        // Handle based on status
        switch ($status) {
            case 'PAID':
            case 'SETTLED':
                $this->handlePaidInvoice($order, $payment, $payload);
                break;

            case 'EXPIRED':
                $this->handleExpiredInvoice($order, $payment);
                break;

            default:
                Log::info('Xendit webhook: Unhandled status', ['status' => $status]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Handle paid invoice
     */
    protected function handlePaidInvoice(Order $order, Payment $payment, array $payload): void
    {
        // Skip if already verified
        if ($payment->status === 'verified') {
            Log::info('Xendit webhook: Payment already verified', ['payment_id' => $payment->id]);
            return;
        }

        // Update payment with Xendit details
        $payment->update([
            'status' => 'verified',
            'verified_at' => now(),
            'xendit_payment_method' => $payload['payment_method'] ?? null,
            'xendit_payment_channel' => $payload['payment_channel'] ?? null,
            'xendit_paid_at' => isset($payload['paid_at']) ? \Carbon\Carbon::parse($payload['paid_at']) : now(),
            'admin_notes' => 'Otomatis diverifikasi oleh Xendit',
        ]);

        // Update order status
        $order->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Grant access to packages/subscriptions
        $this->grantAccess($order);

        Log::info('Xendit webhook: Payment verified successfully', [
            'order_id' => $order->id,
            'payment_id' => $payment->id,
        ]);
    }

    /**
     * Handle expired invoice
     */
    protected function handleExpiredInvoice(Order $order, Payment $payment): void
    {
        // Skip if already processed
        if ($payment->status !== 'pending') {
            return;
        }

        $payment->update([
            'status' => 'expired',
            'admin_notes' => 'Invoice kadaluarsa',
        ]);

        $order->update([
            'status' => 'expired',
        ]);

        Log::info('Xendit webhook: Invoice expired', ['order_id' => $order->id]);
    }

    /**
     * Grant access to purchased items
     */
    protected function grantAccess(Order $order): void
    {
        foreach ($order->items as $item) {
            if ($item->orderable_type === \App\Models\QuestionPackage::class) {
                \App\Models\UserPackage::create([
                    'user_id' => $order->user_id,
                    'question_package_id' => $item->orderable_id,
                    'order_id' => $order->id,
                    'purchased_at' => now(),
                    'expires_at' => null,
                ]);
            } elseif ($item->orderable_type === \App\Models\Subscription::class) {
                $subscription = \App\Models\Subscription::find($item->orderable_id);
                if ($subscription) {
                    // Check for existing active subscription
                    $existingSubscription = \App\Models\UserSubscription::where('user_id', $order->user_id)
                        ->where('subscription_id', $subscription->id)
                        ->where('status', 'active')
                        ->where('expires_at', '>', now())
                        ->first();

                    if ($existingSubscription) {
                        // Extend existing subscription
                        $existingSubscription->update([
                            'expires_at' => $existingSubscription->expires_at->addDays($subscription->duration_days),
                        ]);
                    } else {
                        // Create new subscription
                        \App\Models\UserSubscription::create([
                            'user_id' => $order->user_id,
                            'subscription_id' => $subscription->id,
                            'starts_at' => now(),
                            'expires_at' => now()->addDays($subscription->duration_days),
                            'status' => 'active',
                        ]);
                    }
                }
            }
        }
    }
}
