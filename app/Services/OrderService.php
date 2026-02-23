<?php

namespace App\Services;

use App\Mail\AdminRegistrationNotificationMail;
use App\Mail\StudentRegistrationMail;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\QuestionPackage;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserPackage;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderService
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function createOrderFromCart(User $user): Order
    {
        $cartItems = $this->cartService->getCartItems($user);

        if ($cartItems->isEmpty()) {
            throw new \Exception('Keranjang kosong');
        }

        return DB::transaction(function () use ($user, $cartItems) {
            $totalAmount = 0;

            foreach ($cartItems as $item) {
                if ($item->cartable) {
                    $totalAmount += $item->cartable->price * $item->quantity;
                }
            }

            // Create order
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user->id,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'expires_at' => now()->addHours(24), // 24 hours to pay
            ]);

            // Create order items
            foreach ($cartItems as $item) {
                if ($item->cartable) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'orderable_type' => $item->cartable_type,
                        'orderable_id' => $item->cartable_id,
                        'price' => $item->cartable->price,
                        'quantity' => $item->quantity,
                    ]);
                }
            }

            // Clear cart
            $this->cartService->clearCart($user);

            return $order;
        });
    }

    public function uploadPaymentProof(Order $order, string $proofImagePath): Payment
    {
        // Check if payment already exists
        if ($order->payment) {
            $order->payment->update([
                'proof_image' => $proofImagePath,
                'status' => 'pending',
            ]);
            return $order->payment;
        }

        return Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'bank_transfer',
            'amount' => $order->total_amount,
            'proof_image' => $proofImagePath,
            'status' => 'pending',
        ]);
    }

    public function verifyPayment(Payment $payment, int $adminId, ?string $notes = null): bool
    {
        return DB::transaction(function () use ($payment, $adminId, $notes) {
            $payment->update([
                'status' => 'verified',
                'verified_by' => $adminId,
                'verified_at' => now(),
                'admin_notes' => $notes,
            ]);

            $order = $payment->order;
            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            // Grant access to packages/subscriptions
            foreach ($order->items as $item) {
                if ($item->orderable_type === QuestionPackage::class) {
                    UserPackage::create([
                        'user_id' => $order->user_id,
                        'question_package_id' => $item->orderable_id,
                        'order_id' => $order->id,
                        'purchased_at' => now(),
                        'expires_at' => null, // Permanent access
                    ]);
                } elseif ($item->orderable_type === Subscription::class) {
                    $subscription = Subscription::find($item->orderable_id);
                    if ($subscription) {
                        // Gunakan tanggal mulai pilihan user, atau hari ini jika tidak dipilih
                        $startsAt = $item->preferred_start_date ?? now()->startOfDay();

                        // Check if user has existing active subscription for this subscription type
                        $existingSubscription = UserSubscription::where('user_id', $order->user_id)
                            ->where('subscription_id', $subscription->id)
                            ->where('status', 'active')
                            ->where('expires_at', '>', now())
                            ->first();

                        if ($existingSubscription) {
                            // Extend existing subscription duration
                            $existingSubscription->update([
                                'expires_at' => $existingSubscription->expires_at->addDays($subscription->duration_days),
                            ]);
                        } else {
                            // Create new subscription dengan tanggal mulai pilihan user
                            UserSubscription::create([
                                'user_id' => $order->user_id,
                                'subscription_id' => $subscription->id,
                                'starts_at' => $startsAt,
                                'expires_at' => \Carbon\Carbon::parse($startsAt)->addDays($subscription->duration_days),
                                'status' => 'active',
                            ]);

                            // Kirim email konfirmasi ke murid dan notifikasi ke admin
                            $startDateStr = \Carbon\Carbon::parse($startsAt)->toDateString();
                            $order->load('user');
                            Mail::to($order->user->email)
                                ->send(new StudentRegistrationMail($order, $subscription, $startDateStr));
                            Mail::to('css.gunawansetiawan@gmail.com')
                                ->send(new AdminRegistrationNotificationMail($order, $subscription, $startDateStr));
                        }
                    }
                }
            }

            return true;
        });
    }

    public function rejectPayment(Payment $payment, int $adminId, ?string $notes = null): bool
    {
        $payment->update([
            'status' => 'rejected',
            'verified_by' => $adminId,
            'verified_at' => now(),
            'admin_notes' => $notes,
        ]);

        return true;
    }

    public function cancelOrder(Order $order): bool
    {
        if ($order->status !== 'pending') {
            return false;
        }

        $order->update(['status' => 'cancelled']);
        return true;
    }

    public function expireOldOrders(): int
    {
        return Order::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);
    }
}
