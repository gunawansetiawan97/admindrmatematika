<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $query = Order::with(['user', 'payment'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->whereHas('payment', function ($q) use ($request) {
                $q->where('status', $request->payment_status);
            });
        }

        $orders = $query->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.orderable', 'payment.verifier']);

        return view('admin.orders.show', compact('order'));
    }

    public function verifyPayment(Request $request, Payment $payment)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $this->orderService->verifyPayment(
            $payment,
            auth('admin')->id(),
            $request->admin_notes
        );

        return back()->with('success', 'Pembayaran berhasil diverifikasi.');
    }

    public function rejectPayment(Request $request, Payment $payment)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:500',
        ]);

        $this->orderService->rejectPayment(
            $payment,
            auth('admin')->id(),
            $request->admin_notes
        );

        return back()->with('success', 'Pembayaran ditolak.');
    }

    public function pendingPayments()
    {
        $payments = Payment::where('status', 'pending')
            ->with(['order.user', 'order.items.orderable'])
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return view('admin.orders.pending-payments', compact('payments'));
    }
}
