@extends('layouts.admin')

@section('title', 'Import Paket Soal')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Import Paket Soal dari Excel</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.packages.process-import') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label for="title" class="block text-gray-700 font-medium mb-2">Judul Paket</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror"
                    required>
                @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="description" class="block text-gray-700 font-medium mb-2">Deskripsi</label>
                <textarea name="description" id="description" rows="3"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="duration_minutes" class="block text-gray-700 font-medium mb-2">Durasi (menit)</label>
                <input type="number" name="duration_minutes" id="duration_minutes" value="{{ old('duration_minutes', 60) }}" min="1"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('duration_minutes') border-red-500 @enderror"
                    required>
                @error('duration_minutes')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="start_date" class="block text-gray-700 font-medium mb-2">Tanggal Mulai</label>
                    <input type="datetime-local" name="start_date" id="start_date" value="{{ old('start_date') }}"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('start_date') border-red-500 @enderror"
                        required>
                    @error('start_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="end_date" class="block text-gray-700 font-medium mb-2">Tanggal Selesai</label>
                    <input type="datetime-local" name="end_date" id="end_date" value="{{ old('end_date') }}"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('end_date') border-red-500 @enderror"
                        required>
                    @error('end_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }} class="mr-2">
                    <span class="text-gray-700">Aktifkan paket soal</span>
                </label>
            </div>

            <hr class="my-6">

            <div class="mb-4">
                <label for="file" class="block text-gray-700 font-medium mb-2">File Excel</label>
                <input type="file" name="file" id="file" accept=".xlsx,.xls"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('file') border-red-500 @enderror"
                    required>
                <p class="text-gray-500 text-sm mt-1">Format: .xlsx atau .xls (Maks. 5MB)</p>
                @error('file')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                @if($errors->has('file') && is_array($errors->get('file')[0] ?? null))
                    <div class="mt-2 p-3 bg-red-50 rounded-lg">
                        <p class="text-red-700 font-medium mb-2">Error pada file:</p>
                        <ul class="list-disc list-inside text-red-600 text-sm">
                            @foreach($errors->get('file') as $errorGroup)
                                @if(is_array($errorGroup))
                                    @foreach($errorGroup as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                @else
                                    <li>{{ $errorGroup }}</li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                <h3 class="font-medium text-blue-800 mb-2">Format File Excel:</h3>
                <p class="text-blue-700 text-sm mb-2">File harus memiliki kolom berikut (baris pertama sebagai header):</p>
                <ul class="list-disc list-inside text-blue-700 text-sm space-y-1">
                    <li><strong>question_text</strong> - Teks soal (wajib)</li>
                    <li><strong>type</strong> - Tipe soal: multiple_choice / essay (wajib)</li>
                    <li><strong>option_a</strong> - Pilihan A (wajib untuk pilihan ganda)</li>
                    <li><strong>option_b</strong> - Pilihan B (wajib untuk pilihan ganda)</li>
                    <li><strong>option_c</strong> - Pilihan C (opsional)</li>
                    <li><strong>option_d</strong> - Pilihan D (opsional)</li>
                    <li><strong>option_e</strong> - Pilihan E (opsional)</li>
                    <li><strong>correct_answer</strong> - Jawaban benar: A/B/C/D/E (wajib untuk pilihan ganda)</li>
                    <li><strong>points</strong> - Poin soal (wajib)</li>
                </ul>
                <a href="{{ route('admin.packages.template') }}" class="inline-block mt-3 text-blue-600 hover:underline font-medium">
                    Download Template Excel
                </a>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.packages.index') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Batal</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Import</button>
            </div>
        </form>
    </div>
</div>
@endsection
