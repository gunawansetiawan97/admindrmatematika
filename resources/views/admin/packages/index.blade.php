@extends('layouts.admin')

@section('title', 'Paket Soal')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Paket Soal</h1>
    <div class="flex space-x-2">
        <a href="{{ route('admin.packages.import') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            Import Excel
        </a>
        <a href="{{ route('admin.packages.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            + Tambah Paket
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full min-w-[700px]">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judul</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah Soal</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durasi</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($packages as $package)
                <tr>
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.packages.show', $package) }}" class="text-blue-600 hover:underline">
                            {{ $package->title }}
                        </a>
                    </td>
                    <td class="px-6 py-4">{{ $package->questions_count }} soal</td>
                    <td class="px-6 py-4">{{ $package->duration_minutes }} menit</td>
                    <td class="px-6 py-4">
                        {{ $package->start_date->format('d/m/Y') }} - {{ $package->end_date->format('d/m/Y') }}
                    </td>
                    <td class="px-6 py-4">
                        @if($package->isActive())
                            <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">Aktif</span>
                        @elseif($package->isUpcoming())
                            <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">Akan Datang</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800">Berakhir</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.packages.edit', $package) }}" class="text-blue-600 hover:underline mr-3">Edit</a>
                        <a href="{{ route('admin.results.index', $package) }}" class="text-green-600 hover:underline">Hasil</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada paket soal</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<div class="mt-4">
    {{ $packages->links() }}
</div>
@endsection
