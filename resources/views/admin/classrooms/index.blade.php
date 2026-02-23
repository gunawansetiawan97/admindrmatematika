@extends('layouts.admin')

@section('title', 'Kelola Kelas')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Kelola Kelas</h1>
        <a href="{{ route('admin.classrooms.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            + Tambah Kelas
        </a>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form action="{{ route('admin.classrooms.index') }}" method="GET" class="flex items-center space-x-4">
            <div class="flex-1">
                <select name="subscription_id" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Kelas</option>
                    @foreach($subscriptions as $subscription)
                        <option value="{{ $subscription->id }}" {{ request('subscription_id') == $subscription->id ? 'selected' : '' }}>
                            {{ $subscription->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                Filter
            </button>
            @if(request('subscription_id'))
                <a href="{{ route('admin.classrooms.index') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($classrooms->count() > 0)
            <div class="overflow-x-auto">
            <table class="w-full min-w-[700px]">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kelas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Anggota</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Belum Terdaftar</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aktivitas</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($classrooms as $classroom)
                        <tr>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium">{{ $classroom->name }}</p>
                                    @if($classroom->description)
                                        <p class="text-sm text-gray-500">{{ Str::limit($classroom->description, 50) }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded bg-purple-100 text-purple-800">
                                    {{ $classroom->subscription->name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-medium">{{ $classroom->members_count }}</span>
                                @if($classroom->active_members_count > 0)
                                    <div class="text-xs text-green-600">{{ $classroom->active_members_count }} aktif</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($classroom->unregistered_count > 0)
                                    <a href="{{ route('admin.classrooms.show', $classroom) }}" class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 hover:bg-red-200">
                                        {{ $classroom->unregistered_count }} siswa
                                    </a>
                                @else
                                    <span class="text-gray-400 text-sm">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-medium">{{ $classroom->activities_count }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($classroom->is_active)
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Aktif</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('admin.classrooms.show', $classroom) }}" class="text-blue-600 hover:text-blue-800">Lihat</a>
                                <a href="{{ route('admin.classrooms.edit', $classroom) }}" class="text-yellow-600 hover:text-yellow-800">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            <div class="px-6 py-4 border-t">
                {{ $classrooms->links() }}
            </div>
        @else
            <div class="p-8 text-center text-gray-500">
                <p>Belum ada kelas.</p>
                <a href="{{ route('admin.classrooms.create') }}" class="text-blue-600 hover:text-blue-800 mt-2 inline-block">
                    Buat kelas pertama
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
