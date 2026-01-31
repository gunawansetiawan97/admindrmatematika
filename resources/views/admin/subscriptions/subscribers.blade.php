@extends('layouts.admin')

@section('title', 'Daftar Pelanggan')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Daftar Pelanggan</h1>
    <a href="{{ route('admin.subscriptions.index') }}" class="text-blue-600 hover:text-blue-800">&larr; Kembali</a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pelanggan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paket</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mulai</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Berakhir</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($userSubscriptions as $userSub)
                <tr>
                    <td class="px-6 py-4">
                        <div class="font-medium">{{ $userSub->user->name }}</div>
                        <div class="text-sm text-gray-500">{{ $userSub->user->email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $userSub->subscription->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $userSub->starts_at->format('d M Y') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $userSub->expires_at->format('d M Y') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($userSub->isActive())
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                        @elseif($userSub->status === 'expired')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Kadaluarsa</span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Dibatalkan</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">Belum ada pelanggan</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">
    {{ $userSubscriptions->links() }}
</div>
@endsection
