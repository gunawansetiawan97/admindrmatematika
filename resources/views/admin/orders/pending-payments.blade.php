@extends('layouts.admin')

@section('title', 'Pembayaran Pending')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Pembayaran Menunggu Verifikasi</h1>
    <a href="{{ route('admin.orders.index') }}" class="text-blue-600 hover:text-blue-800">&larr; Kembali ke Pesanan</a>
</div>

@if($payments->isEmpty())
    <div class="bg-white rounded-lg shadow p-8 text-center">
        <svg class="mx-auto h-16 w-16 text-green-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <p class="text-gray-500">Tidak ada pembayaran yang menunggu verifikasi</p>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($payments as $payment)
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-4 py-3 border-b bg-gray-50">
                    <div class="flex justify-between items-center">
                        <span class="font-semibold">#{{ $payment->order->order_number }}</span>
                        <span class="text-sm text-gray-500">{{ $payment->created_at->diffForHumans() }}</span>
                    </div>
                </div>

                @if($payment->proof_image)
                    <a href="{{ url(config('app.storage_url', '/storage') . '/' . $payment->proof_image) }}" target="_blank">
                        <img src="{{ url(config('app.storage_url', '/storage') . '/' . $payment->proof_image) }}" alt="Bukti" class="w-full h-48 object-cover hover:opacity-75 transition">
                    </a>
                @endif

                <div class="p-4">
                    <div class="mb-3">
                        <p class="text-sm text-gray-600">Pelanggan</p>
                        <p class="font-medium">{{ $payment->order->user->name }}</p>
                    </div>
                    <div class="mb-3">
                        <p class="text-sm text-gray-600">Total</p>
                        <p class="font-bold text-lg">{{ $payment->formatted_amount }}</p>
                    </div>
                    <div class="mb-4">
                        <p class="text-sm text-gray-600">Items</p>
                        @foreach($payment->order->items as $item)
                            <p class="text-sm">{{ $item->orderable->title ?? $item->orderable->name ?? 'Item' }}</p>
                        @endforeach
                    </div>

                    <div class="flex space-x-2">
                        <form action="{{ route('admin.payments.verify', $payment) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700 text-sm">
                                Verifikasi
                            </button>
                        </form>
                        <a href="{{ route('admin.orders.show', $payment->order) }}" class="flex-1 text-center bg-gray-200 text-gray-700 py-2 rounded hover:bg-gray-300 text-sm">
                            Detail
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $payments->links() }}
    </div>
@endif
@endsection
