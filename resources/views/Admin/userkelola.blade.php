@extends('layouts.app')

@section('title', 'Kelola Pengguna - RI CCSL')

@push('styles')
<style>
    /* Toast notification */
    #toast {
        transition: opacity 0.4s ease, transform 0.4s ease;
    }
    #toast.hide {
        opacity: 0;
        transform: translateY(-10px);
        pointer-events: none;
    }
</style>
@endpush

@section('content')
@php($authUser = auth()->user())

{{-- ── TOAST ──────────────────────────────────────────────── --}}
<div id="toast"
     class="hide fixed top-5 right-5 z-[999] flex items-center space-x-3 bg-white border border-gray-200 shadow-lg rounded-xl px-5 py-3 text-sm font-medium text-gray-700 max-w-xs">
    <span id="toast-icon" class="text-lg"></span>
    <span id="toast-msg"></span>
</div>

<div class="content">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Kelola Pengguna</h1>
        <p class="text-gray-400 text-sm mt-1">Manajemen akun dan status pengguna sistem</p>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-4 text-sm">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4 text-sm">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    {{-- ── Actions ──────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-4" style="margin-top: 15px;">
        <button type="button" onclick="openRegisterForm()"
           class="bg-green-500 hover:bg-green-600 text-white text-sm px-4 py-2 rounded-lg flex items-center space-x-2 transition shadow-sm cursor-pointer border-0">
            <i class="fas fa-plus text-xs"></i>
            <span>Tambah Pengguna</span>
        </button>
        <input type="text"
               id="search-input"
               placeholder="Cari pengguna..."
               class="p-2 border border-gray-300 rounded-lg w-1/3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1a2e4a]" />
    </div>

    {{-- ── Table ────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-700">Daftar Pengguna</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="user-table">
                <thead>
                    <tr class="bg-[#1a2e4a] text-white text-xs uppercase tracking-wider">
                        <th class="px-6 py-3.5 text-left font-semibold">ID</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Nama</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Email</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Role</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="user-tbody">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50/50 transition user-row" data-name="{{ strtolower($user->name) }}" data-email="{{ strtolower($user->email) }}">
                        <td class="px-6 py-4 text-gray-500 text-xs">{{ $user->id }}</td>
                        <td class="px-6 py-4 font-medium text-gray-800">{{ $user->name }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $user->email }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ ucfirst(optional($user->role)->name ?? 'N/A') }}</td>

                        {{-- Aksi: Edit & Hapus --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                {{-- Edit --}}
                                <a href="{{ route('admin.users.edit', $user->id) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-500 hover:bg-blue-100 hover:text-blue-700 transition"
                                   title="Edit pengguna" style="text-decoration:none">
                                    <i class="fas fa-pencil-alt text-xs"></i>
                                </a>

                                {{-- Hapus (disable jika diri sendiri) --}}
                                @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                      onsubmit="return confirmDelete(event, '{{ addslashes($user->name) }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-400 hover:bg-red-100 hover:text-red-600 transition border-0 cursor-pointer"
                                            title="Hapus pengguna">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </form>
                                @else
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-300 cursor-not-allowed"
                                      title="Tidak dapat menghapus akun sendiri">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-400 text-sm">
                            <i class="fas fa-users text-2xl mb-2 block"></i>
                            Belum ada data pengguna.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- No result from search --}}
        <div id="no-result" class="hidden px-6 py-10 text-center text-gray-400 text-sm">
            <i class="fas fa-search text-2xl mb-2 block"></i>
            Pengguna tidak ditemukan.
        </div>
    </div>

</div>

{{-- ── REGISTER FORM OVERLAY/MODAL ───────────────────────── --}}
<div id="register-modal" class="{{ ($errors->any() || old('name') || old('username') || old('email')) ? 'flex' : 'hidden' }} fixed inset-0 z-50 items-center justify-center bg-black/50 backdrop-blur-sm transition-all duration-300">
    <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden border border-gray-200 m-4 flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="bg-[#1a2e4a] text-white px-6 py-4 flex items-center justify-between">
            <h3 class="text-lg font-bold flex items-center space-x-2">
                <i class="fas fa-user-plus"></i>
                <span>Tambah Pengguna Baru</span>
            </h3>
            <button type="button" onclick="closeRegisterForm()" class="text-white/85 hover:text-white transition focus:outline-none bg-transparent border-0 cursor-pointer">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Form Content -->
        <div class="p-6 overflow-y-auto">
            <!-- Error Messages inside modal -->
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register.post') }}" method="POST" class="space-y-4">
                @csrf
                <!-- Hidden input to redirect back to admin user management -->
                <input type="hidden" name="redirect_to" value="admin">

                <!-- Grid for fields to make it compact and structured -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Full Name -->
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Masukkan nama lengkap"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1a2e4a]"
                            required
                        />
                    </div>

                    <!-- Username -->
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700">Username <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            name="username"
                            value="{{ old('username') }}"
                            placeholder="Masukkan username"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1a2e4a]"
                            required
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Email -->
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="nama@email.com"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1a2e4a]"
                            required
                        />
                    </div>

                    <!-- Role -->
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700">Role / Posisi <span class="text-red-500">*</span></label>
                        <select
                            name="role"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1a2e4a]"
                            required
                        >
                            <option value="">-- Pilih Posisi --</option>
                            <option value="admin"           {{ old('role') == 'admin'           ? 'selected' : '' }}>Admin</option>
                            <option value="manager"         {{ old('role') == 'manager'         ? 'selected' : '' }}>Manager</option>
                            <option value="asisten_manager" {{ old('role') == 'asisten_manager' ? 'selected' : '' }}>Asisten Manager</option>
                            <option value="staff"           {{ old('role') == 'staff'           ? 'selected' : '' }}>Staff</option>
                            <option value="dekan"           {{ old('role') == 'dekan'           ? 'selected' : '' }}>Dekan</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Password -->
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700">Password <span class="text-red-500">*</span></label>
                        <input
                            type="password"
                            name="password"
                            placeholder="Minimal 8 karakter"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1a2e4a]"
                            required
                        />
                    </div>

                    <!-- Confirm Password -->
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700">Konfirmasi Password <span class="text-red-500">*</span></label>
                        <input
                            type="password"
                            name="password_confirmation"
                            placeholder="Ulangi password"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1a2e4a]"
                            required
                        />
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button
                        type="button"
                        onclick="closeRegisterForm()"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition cursor-pointer bg-white"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg text-sm transition shadow-sm cursor-pointer border-0"
                    >
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    /* ── Toast helper ──────────────────────────── */
    let toastTimer;
    function showToast(msg, type = 'success') {
        const toast   = document.getElementById('toast');
        const icon    = document.getElementById('toast-icon');
        const msgEl   = document.getElementById('toast-msg');

        icon.textContent  = type === 'success' ? '✅' : '❌';
        msgEl.textContent = msg;

        toast.classList.remove('hide');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.add('hide'), 3000);
    }

    /* ── Konfirmasi hapus ──────────────────────── */
    function confirmDelete(e, name) {
        if (!confirm(`Hapus pengguna "${name}"? Tindakan ini tidak dapat dibatalkan.`)) {
            e.preventDefault();
            return false;
        }
        return true;
    }

    /* ── Search / filter lokal ─────────────────── */
    document.getElementById('search-input').addEventListener('input', function () {
        const q     = this.value.toLowerCase().trim();
        const rows  = document.querySelectorAll('.user-row');
        let   found = 0;

        rows.forEach(row => {
            const name  = row.dataset.name  || '';
            const email = row.dataset.email || '';
            const match = name.includes(q) || email.includes(q);
            row.style.display = match ? '' : 'none';
            if (match) found++;
        });

        document.getElementById('no-result').classList.toggle('hidden', found > 0 || q === '');
    });

    /* ── Register modal functions ──────────────── */
    function openRegisterForm() {
        const modal = document.getElementById('register-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeRegisterForm() {
        const modal = document.getElementById('register-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endpush