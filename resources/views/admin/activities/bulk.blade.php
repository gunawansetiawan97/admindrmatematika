@extends('layouts.admin')

@section('title', 'Upload Aktivitas Massal')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Upload Aktivitas Massal</h1>
        <p class="text-gray-500 mt-1">Tambahkan satu materi ke beberapa kelas sekaligus dengan tanggal rilis berbeda per kelas.</p>
    </div>

    <form action="{{ route('admin.activities.bulk.store') }}" method="POST" x-data="bulkUpload()">
        @csrf

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                <ul class="list-disc list-inside space-y-1 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Section 1: Detail Materi --}}
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Detail Materi</h2>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis</label>
                    <select name="type" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="youtube" {{ old('type') === 'youtube' ? 'selected' : '' }}>Video YouTube</option>
                        <option value="link"    {{ old('type') === 'link'    ? 'selected' : '' }}>Link</option>
                        <option value="text"    {{ old('type') === 'text'    ? 'selected' : '' }}>Materi / Teks</option>
                        <option value="announcement" {{ old('type') === 'announcement' ? 'selected' : '' }}>Pengumuman</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Judul aktivitas...">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Konten (URL / Teks)</label>
                <textarea name="content" rows="3" required
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Masukkan URL YouTube, link, atau teks...">{{ old('content') }}</textarea>
            </div>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_pinned" value="1" {{ old('is_pinned') ? 'checked' : '' }}
                    class="rounded border-gray-300">
                <span class="text-sm text-gray-700">Pin aktivitas ini di semua kelas yang dipilih</span>
            </label>
        </div>

        {{-- Section 2: Pilih Kelas --}}
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">Pilih Kelas & Tanggal Rilis</h2>
                <div class="flex gap-3 text-sm">
                    <button type="button" @click="selectAll()" class="text-blue-600 hover:underline">Pilih Semua</button>
                    <button type="button" @click="deselectAll()" class="text-gray-500 hover:underline">Hapus Semua</button>
                </div>
            </div>

            @forelse($classrooms as $subscriptionName => $group)
                <div class="mb-5">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">{{ $subscriptionName }}</p>
                    <div class="space-y-2">
                        @foreach($group as $classroom)
                            <div class="border rounded-lg p-3" :class="selected.includes({{ $classroom->id }}) ? 'border-blue-300 bg-blue-50' : 'border-gray-200'">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox"
                                        name="classroom_ids[]"
                                        value="{{ $classroom->id }}"
                                        @change="toggle({{ $classroom->id }})"
                                        {{ in_array($classroom->id, old('classroom_ids', [])) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-blue-600">
                                    <span class="font-medium text-sm flex-1">{{ $classroom->name }}</span>
                                    <span class="text-xs text-gray-400">{{ $classroom->members_count ?? $classroom->members()->count() }} anggota</span>
                                </label>

                                <div x-show="selected.includes({{ $classroom->id }})" x-cloak class="mt-2 pl-7">
                                    <label class="block text-xs text-gray-600 mb-1">Tanggal Rilis / Pertemuan</label>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <input type="date"
                                            name="meeting_dates[{{ $classroom->id }}]"
                                            value="{{ old("meeting_dates.{$classroom->id}") }}"
                                            @change="checkDate({{ $classroom->id }}, $event.target.value)"
                                            class="w-48 px-2 py-1.5 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <span class="text-xs text-gray-400">(opsional)</span>
                                        @if($classroom->subscription->days && count($classroom->subscription->days))
                                            <span class="text-xs text-gray-400">• Jadwal: {{ implode(', ', $classroom->subscription->days) }}</span>
                                        @endif
                                    </div>
                                    <p x-show="dateErrors[{{ $classroom->id }}]" x-cloak
                                        class="text-red-600 text-xs mt-1"
                                        x-text="dateErrors[{{ $classroom->id }}]"></p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-6">Belum ada kelas aktif.</p>
            @endforelse

            @error('classroom_ids')
                <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        {{-- Submit --}}
        <div class="flex justify-between items-center">
            <p class="text-sm text-gray-500">
                <span x-text="selected.length"></span> kelas dipilih
            </p>
            <div class="flex gap-3">
                <a href="{{ route('admin.classrooms.index') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Batal</a>
                <button type="submit" :disabled="selected.length === 0 || hasDateErrors"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    Upload ke <span x-text="selected.length"></span> Kelas
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function bulkUpload() {
    // Map nama hari Indonesia → nomor hari JS (0=Minggu, 1=Senin, ..., 6=Sabtu)
    const DAY_MAP = { 'Minggu': 0, 'Senin': 1, 'Selasa': 2, 'Rabu': 3, 'Kamis': 4, 'Jumat': 5, 'Sabtu': 6 };
    const DAY_NAME = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];

    return {
        selected: @json(old('classroom_ids', [])).map(Number),
        // classroom_id → array hari yang diizinkan (dari backend)
        classroomDays: @json($classroomDays),
        dateErrors: {},

        toggle(id) {
            if (this.selected.includes(id)) {
                this.selected = this.selected.filter(i => i !== id);
            } else {
                this.selected.push(id);
            }
        },
        selectAll() {
            this.selected = @json($classrooms->flatten()->pluck('id'));
            document.querySelectorAll('input[name="classroom_ids[]"]').forEach(cb => cb.checked = true);
        },
        deselectAll() {
            this.selected = [];
            document.querySelectorAll('input[name="classroom_ids[]"]').forEach(cb => cb.checked = false);
        },
        checkDate(classroomId, dateValue) {
            if (!dateValue) {
                delete this.dateErrors[classroomId];
                return;
            }
            const allowed = this.classroomDays[classroomId] || [];
            if (allowed.length === 0) return; // paket tidak ada batasan hari

            // Parse tanggal lokal (hindari offset UTC)
            const [y, m, d] = dateValue.split('-').map(Number);
            const dayNum  = new Date(y, m - 1, d).getDay(); // 0=Minggu
            const dayName = DAY_NAME[dayNum];

            const allowedNums = allowed.map(n => DAY_MAP[n]);
            if (!allowedNums.includes(dayNum)) {
                this.dateErrors[classroomId] = `${dayName} bukan hari jadwal paket ini (${allowed.join(', ')})`;
            } else {
                delete this.dateErrors[classroomId];
            }
        },
        get hasDateErrors() {
            return Object.keys(this.dateErrors).length > 0;
        }
    }
}
</script>
@endpush
@endsection
