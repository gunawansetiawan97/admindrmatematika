<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\CreateInvoiceRequest;

class XenditService
{
    protected InvoiceApi $invoiceApi;

    public function __construct()
    {
        Configuration::setXenditKey(config('xendit.secret_key'));
        $this->invoiceApi = new InvoiceApi();
    }

    /**
     * Create a Xendit invoice for an order
     */
    public function createInvoice(Order $order): array
    {
        $user = $order->user;

        // Build invoice items description
        $items = $order->items->map(function ($item) {
            $name = $item->orderable->title ?? $item->orderable->name ?? 'Item';
            return [
                'name' => $name,
                'quantity' => $item->quantity,
                'price' => (int) $item->price,
            ];
        })->toArray();

        $createInvoiceRequest = new CreateInvoiceRequest([
            'external_id' => $order->order_number,
            'amount' => (int) $order->total_amount,
            'payer_email' => $user->email,
            'description' => 'Pembayaran untuk Order #' . $order->order_number,
            'invoice_duration' => config('xendit.invoice.expiry_seconds', 86400),
            'customer' => [
                'given_names' => $user->name,
                'email' => $user->email,
            ],
            'items' => $items,
            'success_redirect_url' => url(config('xendit.invoice.success_url', '/orders')),
            'failure_redirect_url' => url(config('xendit.invoice.failure_url', '/orders')),
            'currency' => 'IDR',
        ]);

        try {
            $invoice = $this->invoiceApi->createInvoice($createInvoiceRequest);

            // SDK v7 returns object, convert to array if needed
            $invoiceId = is_array($invoice) ? $invoice['id'] : $invoice->getId();
            $invoiceUrl = is_array($invoice) ? $invoice['invoice_url'] : $invoice->getInvoiceUrl();
            $expiryDate = is_array($invoice) ? $invoice['expiry_date'] : $invoice->getExpiryDate();

            // Create or update payment record
            Payment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'payment_method' => 'xendit',
                    'amount' => $order->total_amount,
                    'status' => 'pending',
                    'xendit_invoice_id' => $invoiceId,
                    'xendit_invoice_url' => $invoiceUrl,
                    'xendit_expiry_date' => $expiryDate,
                ]
            );

            return [
                'success' => true,
                'invoice_id' => $invoiceId,
                'invoice_url' => $invoiceUrl,
                'expiry_date' => $expiryDate,
            ];
        } catch (\Exception $e) {
            \Log::error('Xendit Invoice Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get invoice details from Xendit
     */
    public function getInvoice(string $invoiceId): ?array
    {
        try {
            $invoice = $this->invoiceApi->getInvoiceById($invoiceId);
            return $invoice;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Verify webhook callback token
     */
    public function verifyWebhookToken(string $token): bool
    {
        return $token === config('xendit.webhook_token');
    }
}
