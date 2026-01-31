@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
<h1 class="text-2xl font-bold mb-6">Dashboard Admin</h1>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm">Total Murid</h3>
        <p class="text-3xl font-bold text-blue-600">{{ $totalStudents }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm">Total Paket Soal</h3>
        <p class="text-3xl font-bold text-green-600">{{ $totalPackages }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm">Paket Aktif</h3>
        <p class="text-3xl font-bold text-yellow-600">{{ $activePackages }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm">Total Pengerjaan</h3>
        <p class="text-3xl font-bold text-purple-600">{{ $totalAttempts }}</p>
    </div>
</div>

<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <h2 class="text-xl font-bold">Pengerjaan Terbaru</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Murid</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paket Soal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mulai</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Skor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($recentAttempts as $attempt)
                    <tr>
                        <td class="px-6 py-4">{{ $attempt->user->name }}</td>
                        <td class="px-6 py-4">{{ $attempt->package->title }}</td>
                        <td class="px-6 py-4">{{ $attempt->started_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4">{{ $attempt->score ?? '-' }}</td>
                        <td class="px-6 py-4">
                            @if($attempt->finished_at)
                                <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">Selesai</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-800">Berlangsung</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">Belum ada pengerjaan</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
