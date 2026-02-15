@extends('layouts.admin')

@section('title', 'Kelola Kelas')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Kelola Paket Kelas</h1>
    <div class="space-x-2">
        <a href="{{ route('admin.subscriptions.subscribers') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
            Lihat Pelanggan
        </a>
        <a href="{{ route('admin.subscriptions.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            Tambah Paket
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Paket</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durasi</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pelanggan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($subscriptions as $subscription)
                <tr>
                    <td class="px-6 py-4">
                        <div class="font-medium">{{ $subscription->name }}</div>
                        @if($subscription->description)
                            <div class="text-sm text-gray-500">{{ Str::limit($subscription->description, 50) }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $subscription->formatted_price }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $subscription->duration_days }} hari</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $subscription->user_subscriptions_count }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($subscription->is_active)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap space-x-2">
                        <a href="{{ route('admin.subscriptions.edit', $subscription) }}" class="text-blue-600 hover:text-blue-800">Edit</a>
                        <form action="{{ route('admin.subscriptions.destroy', $subscription) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada paket kelas</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
