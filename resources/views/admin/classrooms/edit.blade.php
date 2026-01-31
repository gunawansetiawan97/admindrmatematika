@extends('layouts.admin')

@section('title', 'Edit Kelas')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Edit Kelas</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.classrooms.update', $classroom) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="subscription_id" class="block text-gray-700 font-medium mb-2">Langganan</label>
                <select name="subscription_id" id="subscription_id"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('subscription_id') border-red-500 @enderror"
                    required>
                    <option value="">Pilih Langganan</option>
                    @foreach($subscriptions as $subscription)
                        <option value="{{ $subscription->id }}" {{ old('subscription_id', $classroom->subscription_id) == $subscription->id ? 'selected' : '' }}>
                            {{ $subscription->name }} ({{ $subscription->formatted_price }})
                        </option>
                    @endforeach
                </select>
                @error('subscription_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="name" class="block text-gray-700 font-medium mb-2">Nama Kelas</label>
                <input type="text" name="name" id="name" value="{{ old('name', $classroom->name) }}"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                    required>
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="description" class="block text-gray-700 font-medium mb-2">Deskripsi</label>
                <textarea name="description" id="description" rows="3"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror">{{ old('description', $classroom->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $classroom->is_active) ? 'checked' : '' }} class="mr-2">
                    <span class="text-gray-700">Aktifkan kelas</span>
                </label>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.classrooms.show', $classroom) }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Batal</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan</button>
            </div>
        </form>

        <!-- Delete Form (separate from update form) -->
        <div class="mt-6 pt-6 border-t">
            <form action="{{ route('admin.classrooms.destroy', $classroom) }}" method="POST"
                onsubmit="return confirm('Yakin ingin menghapus kelas ini? Semua anggota dan aktivitas akan dihapus.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Hapus Kelas
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
