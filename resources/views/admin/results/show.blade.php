@extends('layouts.admin')

@section('title', 'Detail Hasil')

@section('content')
<div class="flex justify-between items-start mb-6">
    <div>
        <h1 class="text-2xl font-bold">Detail Hasil Pengerjaan</h1>
        <p class="text-gray-600">{{ $attempt->package->title }} - {{ $attempt->user->name }}</p>
    </div>
    <a href="{{ route('admin.results.index', $attempt->package) }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Kembali</a>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="text-gray-500 text-sm">Total Skor</h3>
        <p class="text-2xl font-bold text-blue-600">{{ $statistics['score'] }} / {{ $statistics['total_points'] }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="text-gray-500 text-sm">Persentase</h3>
        <p class="text-2xl font-bold">{{ $statistics['percentage'] }}%</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="text-gray-500 text-sm">Pilihan Ganda</h3>
        <p class="text-2xl font-bold text-green-600">{{ $statistics['multiple_choice']['correct'] }} / {{ $statistics['multiple_choice']['total'] }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="text-gray-500 text-sm">Essay Dinilai</h3>
        <p class="text-2xl font-bold text-purple-600">{{ $statistics['essay']['graded'] }} / {{ $statistics['essay']['total'] }}</p>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-4 mb-6">
    <div class="grid grid-cols-3 gap-4 text-sm">
        <div>
            <span class="text-gray-500">Waktu Mulai:</span>
            <span class="font-medium">{{ $attempt->started_at->format('d/m/Y H:i:s') }}</span>
        </div>
        <div>
            <span class="text-gray-500">Waktu Selesai:</span>
            <span class="font-medium">{{ $attempt->finished_at?->format('d/m/Y H:i:s') ?? '-' }}</span>
        </div>
        <div>
            <span class="text-gray-500">Total Waktu:</span>
            <span class="font-medium">{{ gmdate('H:i:s', $statistics['time_spent']) }}</span>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b">
        <h2 class="text-xl font-bold">Detail Jawaban</h2>
    </div>
    <div class="divide-y">
        @foreach($attempt->answers->sortBy('question.order') as $answer)
            <div class="p-4">
                <div class="flex justify-between items-start mb-2">
                    <div class="flex items-center space-x-2">
                        <span class="bg-gray-200 px-2 py-1 rounded text-sm">Soal {{ $answer->question->order }}</span>
                        <span class="text-sm text-gray-500">
                            {{ $answer->question->isMultipleChoice() ? 'Pilihan Ganda' : 'Essay' }}
                        </span>
                        <span class="text-sm text-gray-500">{{ $answer->question->points }} poin</span>
                    </div>
                    <div class="text-sm text-gray-500">
                        Waktu: {{ $answer->getTimeSpentSeconds() ? gmdate('i:s', $answer->getTimeSpentSeconds()) : '-' }}
                    </div>
                </div>

                <p class="text-gray-800 mb-3 math-content">{!! nl2br(e($answer->question->question_text)) !!}</p>

                @if($answer->question->isMultipleChoice())
                    <div class="ml-4 text-sm mb-2">
                        @foreach($answer->question->options as $option)
                            <div class="math-content {{ $option->option_label === $answer->question->correct_answer ? 'text-green-600' : '' }}
                                        {{ $option->option_label === $answer->answer_text ? 'font-bold' : '' }}">
                                {{ $option->option_label }}. {{ $option->option_text }}
                                @if($option->option_label === $answer->question->correct_answer)
                                    <span class="text-green-600">(Benar)</span>
                                @endif
                                @if($option->option_label === $answer->answer_text)
                                    <span class="text-blue-600">(Jawaban Murid)</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-2">
                        @if($answer->is_correct)
                            <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">Benar (+{{ $answer->question->points }})</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-800">Salah</span>
                        @endif
                    </div>
                @else
                    <div class="bg-gray-50 p-3 rounded mb-3">
                        <p class="text-sm text-gray-500 mb-1">Jawaban:</p>
                        <p class="text-gray-800">{!! nl2br(e($answer->answer_text ?? 'Tidak dijawab')) !!}</p>
                    </div>

                    <form action="{{ route('admin.results.grade', $answer) }}" method="POST" class="flex items-center space-x-2">
                        @csrf
                        <label class="text-sm text-gray-600">Nilai:</label>
                        <input type="number" name="score" value="{{ $answer->score }}" min="0" max="{{ $answer->question->points }}" step="0.5"
                            class="w-20 px-2 py-1 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <span class="text-sm text-gray-500">/ {{ $answer->question->points }}</span>
                        <button type="submit" class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">Simpan</button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection
