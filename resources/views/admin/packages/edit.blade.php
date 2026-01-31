@extends('layouts.admin')

@section('title', 'Edit Paket Soal')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Edit Paket Soal</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.packages.update', $package) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="title" class="block text-gray-700 font-medium mb-2">Judul Paket</label>
                <input type="text" name="title" id="title" value="{{ old('title', $package->title) }}"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror"
                    required>
                @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="description" class="block text-gray-700 font-medium mb-2">Deskripsi</label>
                <textarea name="description" id="description" rows="3"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror">{{ old('description', $package->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="duration_minutes" class="block text-gray-700 font-medium mb-2">Durasi (menit)</label>
                <input type="number" name="duration_minutes" id="duration_minutes" value="{{ old('duration_minutes', $package->duration_minutes) }}" min="1"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('duration_minutes') border-red-500 @enderror"
                    required>
                @error('duration_minutes')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="start_date" class="block text-gray-700 font-medium mb-2">Tanggal Mulai</label>
                    <input type="datetime-local" name="start_date" id="start_date"
                        value="{{ old('start_date', $package->start_date->format('Y-m-d\TH:i')) }}"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('start_date') border-red-500 @enderror"
                        required>
                    @error('start_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="end_date" class="block text-gray-700 font-medium mb-2">Tanggal Selesai</label>
                    <input type="datetime-local" name="end_date" id="end_date"
                        value="{{ old('end_date', $package->end_date->format('Y-m-d\TH:i')) }}"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('end_date') border-red-500 @enderror"
                        required>
                    @error('end_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="price" class="block text-gray-700 font-medium mb-2">Harga (Rp)</label>
                    <input type="number" name="price" id="price" value="{{ old('price', $package->price) }}" min="0"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('price') border-red-500 @enderror">
                    @error('price')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex items-end pb-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_free" value="1" {{ old('is_free', $package->is_free) ? 'checked' : '' }} class="mr-2">
                        <span class="text-gray-700">Gratis</span>
                    </label>
                </div>
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $package->is_active) ? 'checked' : '' }} class="mr-2">
                    <span class="text-gray-700">Aktifkan paket soal</span>
                </label>
            </div>

            <div class="flex justify-between">
                <form action="{{ route('admin.packages.destroy', $package) }}" method="POST"
                    onsubmit="return confirm('Yakin ingin menghapus paket soal ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Hapus</button>
                </form>

                <div class="flex space-x-4">
                    <a href="{{ route('admin.packages.show', $package) }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Batal</a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
