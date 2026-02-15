@extends('layouts.admin')

@section('title', 'Tambah Paket Kelas')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Tambah Paket Kelas</h1>
        <a href="{{ route('admin.subscriptions.index') }}" class="text-blue-600 hover:text-blue-800">&larr; Kembali</a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <form action="{{ route('admin.subscriptions.store') }}" method="POST" class="p-6">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Paket</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                <textarea name="description" rows="3"
                    class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Harga (Rp)</label>
                    <input type="number" name="price" value="{{ old('price') }}" min="0" required
                        class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('price') border-red-500 @enderror">
                    @error('price')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Durasi (hari)</label>
                    <input type="number" name="duration_days" value="{{ old('duration_days', 30) }}" min="1" required
                        class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('duration_days') border-red-500 @enderror">
                    @error('duration_days')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-4" x-data="{ features: [''] }">
                <label class="block text-sm font-medium text-gray-700 mb-2">Fitur</label>
                <template x-for="(feature, index) in features" :key="index">
                    <div class="flex space-x-2 mb-2">
                        <input type="text" :name="'features[' + index + ']'" x-model="features[index]"
                            class="flex-1 px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Masukkan fitur">
                        <button type="button" @click="features.splice(index, 1)" x-show="features.length > 1"
                            class="px-3 py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200">&times;</button>
                    </div>
                </template>
                <button type="button" @click="features.push('')"
                    class="text-sm text-blue-600 hover:text-blue-800">+ Tambah Fitur</button>
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Aktif</span>
                </label>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
