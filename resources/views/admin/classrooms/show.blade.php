@extends('layouts.admin')

@section('title', $classroom->name)

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-start mb-6">
        <div>
            <div class="flex items-center space-x-2 text-sm text-gray-500 mb-2">
                <a href="{{ route('admin.classrooms.index') }}" class="hover:text-gray-700">Kelas</a>
                <span>/</span>
                <span>{{ $classroom->name }}</span>
            </div>
            <h1 class="text-2xl font-bold">{{ $classroom->name }}</h1>
            <div class="flex items-center space-x-3 mt-2">
                <span class="px-2 py-1 text-xs rounded bg-purple-100 text-purple-800">
                    {{ $classroom->subscription->name }}
                </span>
                @if($classroom->is_active)
                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Aktif</span>
                @else
                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Nonaktif</span>
                @endif
            </div>
            @if($classroom->description)
                <p class="text-gray-600 mt-2">{{ $classroom->description }}</p>
            @endif
        </div>
        <a href="{{ route('admin.classrooms.edit', $classroom) }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
            Edit Kelas
        </a>
    </div>

    <!-- Tabs -->
    <div x-data="{ activeTab: 'members' }" class="bg-white rounded-lg shadow">
        <div class="border-b">
            <nav class="flex -mb-px">
                <button @click="activeTab = 'members'"
                    :class="activeTab === 'members' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="px-6 py-4 border-b-2 font-medium text-sm focus:outline-none">
                    Anggota ({{ $members->count() }})
                </button>
                <button @click="activeTab = 'activities'"
                    :class="activeTab === 'activities' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="px-6 py-4 border-b-2 font-medium text-sm focus:outline-none">
                    Aktivitas ({{ $activities->count() }})
                </button>
            </nav>
        </div>

        <!-- Members Tab -->
        <div x-show="activeTab === 'members'" class="p-6">
            <!-- Add Member Form -->
            <div class="mb-6">
                <form action="{{ route('admin.classrooms.members.add', $classroom) }}" method="POST" class="flex items-end space-x-4">
                    @csrf
                    <div class="flex-1">
                        <label class="block text-gray-700 font-medium mb-2">Tambah Anggota</label>
                        <select name="user_id" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Pilih murid...</option>
                            @foreach($availableStudents as $student)
                                <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->email }})</option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Tambah
                    </button>
                </form>
                @if($availableStudents->isEmpty())
                    <p class="text-sm text-gray-500 mt-2">Tidak ada subscriber aktif yang tersedia untuk ditambahkan.</p>
                @endif
            </div>

            <!-- Members List -->
            @if($members->count() > 0)
                <div class="border rounded-lg divide-y">
                    @foreach($members as $member)
                        <div class="p-4 flex justify-between items-center">
                            <div>
                                <p class="font-medium">{{ $member->user->name }}</p>
                                <p class="text-sm text-gray-500">{{ $member->user->email }}</p>
                                <p class="text-xs text-gray-400 mt-1">
                                    Bergabung: {{ $member->joined_at->format('d M Y H:i') }}
                                    @if($member->addedBy)
                                        oleh {{ $member->addedBy->name }}
                                    @endif
                                </p>
                            </div>
                            <form action="{{ route('admin.classrooms.members.remove', [$classroom, $member->user]) }}" method="POST"
                                onsubmit="return confirm('Hapus murid ini dari kelas?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-500 py-8">Belum ada anggota di kelas ini.</p>
            @endif
        </div>

        <!-- Activities Tab -->
        <div x-show="activeTab === 'activities'" class="p-6">
            <!-- Add Activity Form -->
            <div class="mb-6 border rounded-lg p-4 bg-gray-50">
                <h3 class="font-medium mb-4">Tambah Aktivitas Baru</h3>
                <form action="{{ route('admin.classrooms.activities.store', $classroom) }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2">Jenis</label>
                            <select name="type" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="youtube">Video YouTube</option>
                                <option value="link">Link</option>
                                <option value="text">Materi / Teks</option>
                                <option value="announcement">Pengumuman</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2">Judul</label>
                            <input type="text" name="title" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Konten (URL / Teks)</label>
                        <textarea name="content" rows="3" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required
                            placeholder="Masukkan URL YouTube, link, atau teks..."></textarea>
                    </div>
                    <div class="flex justify-between items-center">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_pinned" value="1" class="mr-2">
                            <span class="text-sm text-gray-700">Pin aktivitas ini</span>
                        </label>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Tambah Aktivitas
                        </button>
                    </div>
                </form>
            </div>

            <!-- Activities List -->
            @if($activities->count() > 0)
                <div class="space-y-4">
                    @foreach($activities as $activity)
                        <div class="border rounded-lg p-4 {{ $activity->is_pinned ? 'bg-yellow-50 border-yellow-200' : '' }}">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-2">
                                        @if($activity->is_pinned)
                                            <svg class="h-4 w-4 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M5 5a2 2 0 012-2h6a2 2 0 012 2v2a2 2 0 01-2 2H7a2 2 0 01-2-2V5zm2 0v2h6V5H7z"></path>
                                            </svg>
                                        @endif
                                        <span class="px-2 py-1 text-xs rounded {{ $activity->type === 'announcement' ? 'bg-red-100 text-red-800' : ($activity->type === 'youtube' ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-800') }}">
                                            {{ $activity->type_label }}
                                        </span>
                                        <h4 class="font-medium">{{ $activity->title }}</h4>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-2">
                                        @if($activity->type === 'youtube' || $activity->type === 'link')
                                            <a href="{{ $activity->content }}" target="_blank" class="text-blue-600 hover:underline">
                                                {{ Str::limit($activity->content, 60) }}
                                            </a>
                                        @else
                                            {{ Str::limit($activity->content, 200) }}
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        Oleh {{ $activity->admin->name }} | {{ $activity->created_at->format('d M Y H:i') }}
                                    </p>
                                </div>
                                <div class="flex items-center space-x-2 ml-4">
                                    <form action="{{ route('admin.activities.pin', $activity) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="p-2 text-gray-400 hover:text-yellow-600" title="{{ $activity->is_pinned ? 'Unpin' : 'Pin' }}">
                                            <svg class="h-5 w-5" fill="{{ $activity->is_pinned ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                            </svg>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.activities.destroy', $activity) }}" method="POST"
                                        onsubmit="return confirm('Hapus aktivitas ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-600">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-500 py-8">Belum ada aktivitas di kelas ini.</p>
            @endif
        </div>
    </div>
</div>
@endsection
