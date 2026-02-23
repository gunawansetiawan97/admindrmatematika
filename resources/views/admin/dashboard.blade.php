@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
<h1 class="text-2xl font-bold mb-6">Dashboard Admin</h1>

{{-- Stat Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm mb-1">Total Murid</h3>
        <p class="text-3xl font-bold text-blue-600">{{ $totalStudents }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm mb-1">Paket Kelas</h3>
        <p class="text-3xl font-bold text-purple-600">{{ $totalKelas }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm mb-1">Kelola Kelas</h3>
        <p class="text-3xl font-bold text-green-600">{{ $totalClassrooms }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm mb-1">Pembayaran Pending</h3>
        <p class="text-3xl font-bold text-yellow-600">{{ $pendingPayments }}</p>
        @if($pendingPayments > 0)
            <a href="{{ route('admin.orders.pending-payments') }}" class="text-xs text-yellow-600 hover:underline">Lihat semua</a>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Pembayaran Menunggu Verifikasi --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
            <h2 class="font-semibold text-lg">Menunggu Verifikasi</h2>
            @if($pendingPayments > 0)
                <a href="{{ route('admin.orders.pending-payments') }}" class="text-sm text-blue-600 hover:text-blue-800">Lihat semua</a>
            @endif
        </div>
        @if($pendingPaymentList->count() > 0)
            <div class="divide-y">
                @foreach($pendingPaymentList as $payment)
                <div class="px-6 py-3 flex justify-between items-center">
                    <div>
                        <p class="text-sm font-medium">{{ $payment->order->user->name }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $payment->order->items->map(fn($i) => $i->orderable->name ?? $i->orderable->title ?? '?')->join(', ') }}
                        </p>
                        <p class="text-xs text-gray-400">{{ $payment->created_at->format('d M Y H:i') }}</p>
                    </div>
                    <a href="{{ route('admin.orders.show', $payment->order) }}"
                       class="text-xs bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full hover:bg-yellow-200">
                        Verifikasi
                    </a>
                </div>
                @endforeach
            </div>
        @else
            <div class="px-6 py-8 text-center text-gray-400 text-sm">Tidak ada pembayaran pending.</div>
        @endif
    </div>

    {{-- Pesanan Terbaru Lunas --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h2 class="font-semibold text-lg">Pesanan Lunas Terbaru</h2>
        </div>
        @if($recentPaidOrders->count() > 0)
            <div class="divide-y">
                @foreach($recentPaidOrders as $order)
                <div class="px-6 py-3 flex justify-between items-center">
                    <div>
                        <p class="text-sm font-medium">{{ $order->user->name }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $order->items->map(fn($i) => $i->orderable->name ?? $i->orderable->title ?? '?')->join(', ') }}
                        </p>
                        <p class="text-xs text-gray-400">{{ $order->paid_at ? $order->paid_at->format('d M Y H:i') : '-' }}</p>
                    </div>
                    <span class="text-xs bg-green-100 text-green-800 px-3 py-1 rounded-full">Lunas</span>
                </div>
                @endforeach
            </div>
        @else
            <div class="px-6 py-8 text-center text-gray-400 text-sm">Belum ada pesanan lunas.</div>
        @endif
    </div>
</div>
@endsection
