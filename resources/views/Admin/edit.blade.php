@extends('layouts.app')

@section('title', 'Edit Pengguna - RI CCSL')

@section('content')
<div class="content">

    <div class="mb-6 flex items-center space-x-3">
        <a href="{{ route('admin.user') }}" class="text-gray-400 hover:text-gray-600 transition" style="margin-right: 8px;">
            <i class="fas fa-arrow-left text-lg"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Edit Pengguna</h1>
            <p class="text-gray-400 text-sm mt-0.5">Perbarui informasi akun pengguna</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 max-w-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-700" style="font-size: 15px;">Informasi Pengguna</h2>
        </div>
        <div class="px-6 py-6">
            <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                {{-- Nama --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Lengkap <span class="text-red-400">*</span>
                    </label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name', $user->name) }}" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1a2e4a] @error('name') border-red-400 @enderror" />
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email <span class="text-red-400">*</span>
                    </label>
                    <input type="email" id="email" name="email"
                           value="{{ old('email', $user->email) }}" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1a2e4a] @error('email') border-red-400 @enderror" />
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Role --}}
                <div>
                    <label for="role_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Role <span class="text-red-400">*</span>
                    </label>
                    <select id="role_id" name="role_id" required
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1a2e4a] bg-white @error('role_id') border-red-400 @enderror">
                        <option value="">-- Pilih Role --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}"
                                {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Buttons --}}
                <div class="flex items-center space-x-3 pt-2">
                    <button type="submit"
                            class="bg-[#1a2e4a] hover:bg-[#2d4a6e] text-white text-sm px-5 py-2.5 rounded-lg transition font-medium border-0 cursor-pointer">
                        <i class="fas fa-save mr-2"></i>Simpan Perubahan
                    </button>
                    <a href="{{ route('admin.user') }}"
                       class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2.5 rounded-lg border border-gray-200 hover:bg-gray-50 transition bg-white text-center" style="text-decoration: none;">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection