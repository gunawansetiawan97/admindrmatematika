@extends('layouts.admin')

@section('title', 'Edit Soal')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Edit Soal - {{ $package->title }}</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.questions.update', [$package, $question]) }}" method="POST" x-data="{ questionType: '{{ old('question_type', $question->question_type) }}' }">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="order" class="block text-gray-700 font-medium mb-2">Nomor Urut</label>
                <input type="number" name="order" id="order" value="{{ old('order', $question->order) }}" min="1"
                    class="w-32 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
            </div>

            <div class="mb-4">
                <label for="question_type" class="block text-gray-700 font-medium mb-2">Tipe Soal</label>
                <select name="question_type" id="question_type" x-model="questionType"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="multiple_choice">Pilihan Ganda</option>
                    <option value="essay">Essay</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="question_text" class="block text-gray-700 font-medium mb-2">Teks Soal</label>
                <textarea name="question_text" id="question_text" rows="4"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>{{ old('question_text', $question->question_text) }}</textarea>
            </div>

            <div class="mb-4">
                <label for="points" class="block text-gray-700 font-medium mb-2">Poin</label>
                <input type="number" name="points" id="points" value="{{ old('points', $question->points) }}" min="1"
                    class="w-32 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
            </div>

            <div x-show="questionType === 'multiple_choice'" class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Opsi Jawaban</label>
                @php
                    $existingOptions = $question->options->keyBy('option_label');
                @endphp
                @foreach(['A', 'B', 'C', 'D', 'E'] as $label)
                    <div class="flex items-center mb-2">
                        <span class="w-8 font-medium">{{ $label }}.</span>
                        <input type="hidden" name="options[{{ $loop->index }}][label]" value="{{ $label }}">
                        <input type="text" name="options[{{ $loop->index }}][text]"
                            value="{{ old('options.' . $loop->index . '.text', $existingOptions[$label]->option_text ?? '') }}"
                            class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Teks opsi {{ $label }}">
                    </div>
                @endforeach

                <div class="mt-4">
                    <label for="correct_answer" class="block text-gray-700 font-medium mb-2">Jawaban Benar</label>
                    <select name="correct_answer" id="correct_answer"
                        class="w-32 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach(['A', 'B', 'C', 'D', 'E'] as $label)
                            <option value="{{ $label }}" {{ old('correct_answer', $question->correct_answer) === $label ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.packages.show', $package) }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Batal</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
