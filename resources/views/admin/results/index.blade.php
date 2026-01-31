@extends('layouts.admin')

@section('title', 'Hasil - ' . $package->title)

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold">Hasil Pengerjaan</h1>
        <p class="text-gray-600">{{ $package->title }}</p>
    </div>
    <a href="{{ route('admin.packages.show', $package) }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Kembali</a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peringkat</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Murid</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sekolah</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mulai</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Selesai</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Skor</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($attempts as $index => $attempt)
                <tr>
                    <td class="px-6 py-4 font-medium">{{ $attempts->firstItem() + $index }}</td>
                    <td class="px-6 py-4">{{ $attempt->user->name }}</td>
                    <td class="px-6 py-4">{{ $attempt->user->school }}</td>
                    <td class="px-6 py-4">{{ $attempt->started_at->format('d/m/Y H:i') }}</td>
                    <td class="px-6 py-4">{{ $attempt->finished_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td class="px-6 py-4 font-bold">{{ $attempt->score ?? '-' }}</td>
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.results.show', $attempt) }}" class="text-blue-600 hover:underline">Detail</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">Belum ada pengerjaan</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $attempts->links() }}
</div>
@endsection
