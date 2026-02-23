@extends('layouts.admin')

@section('title', 'Detail Pesanan')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Detail Pesanan</h1>
    <a href="{{ route('admin.orders.index') }}" class="text-blue-600 hover:text-blue-800">&larr; Kembali</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Order Info -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
                <div>
                    <p class="font-semibold text-lg">#{{ $order->order_number }}</p>
                    <p class="text-sm text-gray-500">{{ $order->created_at->format('d M Y H:i') }}</p>
                </div>
                <div>
                    @if($order->status === 'pending')
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                    @elseif($order->status === 'paid')
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">Lunas</span>
                    @elseif($order->status === 'cancelled')
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">Dibatalkan</span>
                    @else
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">Kadaluarsa</span>
                    @endif
                </div>
            </div>

            <!-- Items -->
            <div class="divide-y">
                @foreach($order->items as $item)
                    <div class="px-6 py-4 flex justify-between items-center">
                        <div>
                            <p class="font-medium">{{ $item->orderable->title ?? $item->orderable->name ?? 'Item' }}</p>
                            @if($item->orderable_type === 'App\\Models\\QuestionPackage')
                                <span class="text-xs text-blue-600">Paket Soal</span>
                            @else
                                <span class="text-xs text-purple-600">Kelas</span>
                                @if($item->preferred_start_date)
                                    <span class="block text-xs text-gray-500 mt-1">
                                        Mulai: {{ $item->preferred_start_date->format('d M Y') }}
                                    </span>
                                @endif
                            @endif
                        </div>
                        <p class="font-medium">Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>

            <div class="px-6 py-4 bg-gray-50">
                <div class="flex justify-between items-center">
                    <span class="font-semibold">Total</span>
                    <span class="text-xl font-bold">{{ $order->formatted_total }}</span>
                </div>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50">
                <h2 class="font-semibold">Informasi Pelanggan</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Nama</p>
                        <p class="font-medium">{{ $order->user->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Email</p>
                        <p class="font-medium">{{ $order->user->email }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Telepon</p>
                        <p class="font-medium">{{ $order->user->phone ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Sekolah</p>
                        <p class="font-medium">{{ $order->user->school ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Info -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50">
                <h2 class="font-semibold">Pembayaran</h2>
            </div>
            <div class="p-6">
                @if($order->payment)
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-1">Status</p>
                        @if($order->payment->status === 'pending')
                            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu Verifikasi</span>
                        @elseif($order->payment->status === 'verified')
                            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">Terverifikasi</span>
                        @else
                            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                        @endif
                    </div>

                    @if($order->payment->proof_image)
                        <div class="mb-4">
                            <p class="text-sm text-gray-600 mb-2">Bukti Transfer</p>
                            <a href="{{ url(config('app.storage_url', '/storage') . '/' . $order->payment->proof_image) }}" target="_blank">
                                <img src="{{ url(config('app.storage_url', '/storage') . '/' . $order->payment->proof_image) }}" alt="Bukti pembayaran" class="w-full rounded-lg border hover:opacity-75 transition">
                            </a>
                        </div>
                    @endif

                    @if($order->payment->verified_at)
                        <div class="mb-4">
                            <p class="text-sm text-gray-600">Diverifikasi pada</p>
                            <p class="font-medium">{{ $order->payment->verified_at->format('d M Y H:i') }}</p>
                            @if($order->payment->verifier)
                                <p class="text-sm text-gray-500">oleh {{ $order->payment->verifier->name }}</p>
                            @endif
                        </div>
                    @endif

                    @if($order->payment->admin_notes)
                        <div class="mb-4 p-3 bg-gray-100 rounded">
                            <p class="text-sm text-gray-600">Catatan</p>
                            <p>{{ $order->payment->admin_notes }}</p>
                        </div>
                    @endif

                    @if($order->payment->status === 'pending')
                        <div class="space-y-3 mt-6">
                            <form action="{{ route('admin.payments.verify', $order->payment) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="block text-sm text-gray-600 mb-1">Catatan (opsional)</label>
                                    <textarea name="admin_notes" rows="2" class="w-full border rounded px-3 py-2 text-sm"></textarea>
                                </div>
                                <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700">
                                    Verifikasi Pembayaran
                                </button>
                            </form>

                            <form action="{{ route('admin.payments.reject', $order->payment) }}" method="POST" onsubmit="return confirm('Yakin ingin menolak pembayaran ini?')">
                                @csrf
                                <div class="mb-3">
                                    <label class="block text-sm text-gray-600 mb-1">Alasan penolakan</label>
                                    <textarea name="admin_notes" rows="2" required class="w-full border rounded px-3 py-2 text-sm"></textarea>
                                </div>
                                <button type="submit" class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700">
                                    Tolak Pembayaran
                                </button>
                            </form>
                        </div>
                    @endif
                @else
                    <p class="text-gray-500">Belum ada bukti pembayaran</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
