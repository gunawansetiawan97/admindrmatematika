@extends('layouts.admin')

@section('title', $package->title)

@section('content')
<div class="flex justify-between items-start mb-6">
    <div>
        <h1 class="text-2xl font-bold">{{ $package->title }}</h1>
        <p class="text-gray-600 mt-1">{{ $package->description }}</p>
    </div>
    <div class="flex space-x-2">
        <a href="{{ route('admin.packages.edit', $package) }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Edit</a>
        <a href="{{ route('admin.results.index', $package) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Lihat Hasil</a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="text-gray-500 text-sm">Durasi</h3>
        <p class="text-xl font-bold">{{ $package->duration_minutes }} menit</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="text-gray-500 text-sm">Jumlah Soal</h3>
        <p class="text-xl font-bold">{{ $package->questions->count() }} soal</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="text-gray-500 text-sm">Total Poin</h3>
        <p class="text-xl font-bold">{{ $package->getTotalPoints() }} poin</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="text-gray-500 text-sm">Pengerjaan</h3>
        <p class="text-xl font-bold">{{ $package->package_attempts_count }}</p>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-4 mb-6">
    <div class="grid grid-cols-2 gap-4 text-sm">
        <div>
            <span class="text-gray-500">Periode:</span>
            <span class="font-medium">{{ $package->start_date->format('d/m/Y H:i') }} - {{ $package->end_date->format('d/m/Y H:i') }}</span>
        </div>
        <div>
            <span class="text-gray-500">Status:</span>
            @if($package->isActive())
                <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">Aktif</span>
            @elseif($package->isUpcoming())
                <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">Akan Datang</span>
            @else
                <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800">Berakhir</span>
            @endif
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b flex justify-between items-center">
        <h2 class="text-xl font-bold">Daftar Soal</h2>
        <a href="{{ route('admin.questions.create', $package) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            + Tambah Soal
        </a>
    </div>

    <div class="divide-y">
        @forelse($package->questions as $question)
            <div class="p-4">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="bg-gray-200 px-2 py-1 rounded text-sm">Soal {{ $question->order }}</span>
                            <span class="text-sm text-gray-500">
                                {{ $question->isMultipleChoice() ? 'Pilihan Ganda' : 'Essay' }}
                            </span>
                            <span class="text-sm text-gray-500">{{ $question->points }} poin</span>
                        </div>
                        <p class="text-gray-800 math-content">{!! nl2br(e(Str::limit($question->question_text, 200))) !!}</p>

                        @if($question->isMultipleChoice())
                            <div class="mt-2 ml-4 text-sm text-gray-600">
                                @foreach($question->options as $option)
                                    <div class="math-content {{ $option->option_label === $question->correct_answer ? 'text-green-600 font-medium' : '' }}">
                                        {{ $option->option_label }}. {{ $option->option_text }}
                                        @if($option->option_label === $question->correct_answer)
                                            <span class="text-green-600">(Benar)</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="flex space-x-2 ml-4">
                        <a href="{{ route('admin.questions.edit', [$package, $question]) }}" class="text-blue-600 hover:underline">Edit</a>
                        <form action="{{ route('admin.questions.destroy', [$package, $question]) }}" method="POST"
                            onsubmit="return confirm('Yakin ingin menghapus soal ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-gray-500">
                Belum ada soal. <a href="{{ route('admin.questions.create', $package) }}" class="text-blue-600 hover:underline">Tambah soal pertama</a>
            </div>
        @endforelse
    </div>
</div>
@endsection
