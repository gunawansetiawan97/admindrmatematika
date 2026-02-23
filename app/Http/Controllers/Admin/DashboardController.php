<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalStudents = User::count();
        $totalKelas = Subscription::where('is_active', true)->count();
        $totalClassrooms = Classroom::where('is_active', true)->count();
        $pendingPayments = Payment::where('status', 'pending')->count();

        // Pembayaran menunggu verifikasi
        $pendingPaymentList = Payment::with(['order.user', 'order.items.orderable'])
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Pesanan terbaru yang sudah lunas
        $recentPaidOrders = Order::with(['user', 'items.orderable'])
            ->where('status', 'paid')
            ->orderByDesc('paid_at')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalStudents',
            'totalKelas',
            'totalClassrooms',
            'pendingPayments',
            'pendingPaymentList',
            'recentPaidOrders'
        ));
    }
}
