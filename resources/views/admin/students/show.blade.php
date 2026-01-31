@extends('layouts.admin')

@section('title', 'Detail Murid')

@section('content')
<h1 class="text-2xl font-bold mb-6">Detail Murid</h1>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-bold mb-4">Informasi Pribadi</h2>
        <dl class="space-y-2">
            <div class="flex">
                <dt class="w-32 text-gray-500">Nama:</dt>
                <dd class="font-medium">{{ $student->name }}</dd>
            </div>
            <div class="flex">
                <dt class="w-32 text-gray-500">Username:</dt>
                <dd class="font-medium">{{ $student->username }}</dd>
            </div>
            <div class="flex">
                <dt class="w-32 text-gray-500">Email:</dt>
                <dd class="font-medium">{{ $student->email }}</dd>
            </div>
            <div class="flex">
                <dt class="w-32 text-gray-500">Tanggal Lahir:</dt>
                <dd class="font-medium">{{ $student->birth_date?->format('d/m/Y') ?? '-' }}</dd>
            </div>
            <div class="flex">
                <dt class="w-32 text-gray-500">Kota:</dt>
                <dd class="font-medium">{{ $student->city ?? '-' }}</dd>
            </div>
            <div class="flex">
                <dt class="w-32 text-gray-500">No HP:</dt>
                <dd class="font-medium">{{ $student->phone ?? '-' }}</dd>
            </div>
            <div class="flex">
                <dt class="w-32 text-gray-500">Sekolah:</dt>
                <dd class="font-medium">{{ $student->school ?? '-' }}</dd>
            </div>
        </dl>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-bold mb-4">Statistik</h2>
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-50 p-4 rounded">
                <p class="text-gray-500 text-sm">Total Ujian</p>
                <p class="text-2xl font-bold">{{ $student->packageAttempts->count() }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded">
                <p class="text-gray-500 text-sm">Total Latihan</p>
                <p class="text-2xl font-bold">{{ $student->practiceAttempts->count() }}</p>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b">
        <h2 class="text-xl font-bold">Riwayat Pengerjaan Ujian</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paket Soal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mulai</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Selesai</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Skor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($attempts as $attempt)
                    <tr>
                        <td class="px-6 py-4">{{ $attempt->package->title }}</td>
                        <td class="px-6 py-4">{{ $attempt->started_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4">{{ $attempt->finished_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $attempt->score ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.results.show', $attempt) }}" class="text-blue-600 hover:underline">Detail</a>
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
