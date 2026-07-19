<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register</title>
    @vite('resources/css/app.css')
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="flex items-center justify-center min-h-screen bg-gradient-to-br from-[#DCE0E6FF] to-[#2c5282] p-6">
    <div class="bg-white w-full max-w-4xl h-auto rounded-2xl shadow-2xl flex flex-col lg:flex-row overflow-hidden">

      <!-- Left Panel -->
      <div class="w-full lg:w-1/2 p-8 flex flex-col justify-center items-center bg-gradient-to-br from-[#DCE0E6FF] to-[#2c5282]">
        <img
          src="{{ asset('assets/logo_riccsl.png') }}"
          alt="Register Illustration"
          class="w-full max-w-xs lg:max-w-sm object-contain mx-auto"
        />
      </div>

      <!-- Right Panel: Form -->
      <div class="w-full lg:w-1/2 p-8 flex flex-col justify-center items-center bg-gray-50">
        <form action="{{ route('register.post') }}" method="POST" class="space-y-6 w-full max-w-md mx-auto">
          @csrf

          <!-- Hidden input -->
          <input type="hidden" name="redirect_to" value="{{ request()->query('from', 'login') }}">

          <!-- Title -->
          <h2 class="text-3xl font-semibold text-center text-[#2c5282] mb-6">Registration</h2>

          <!-- Error Messages -->
          @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl">
              <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <!-- Success Message -->
          @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl text-sm">
              {{ session('success') }}
            </div>
          @endif

          <!-- Full Name -->
          <div class="space-y-2">
            <label class="block text-lg font-medium">Full Name <span class="text-red-500">*</span></label>
            <input
              type="text"
              name="name"
              value="{{ old('name') }}"
              placeholder="Please enter your full name"
              class="w-full border rounded-xl px-4 py-3 text-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-red-400"
              required
            />
          </div>

          <!-- Username -->
          <div class="space-y-2">
            <label class="block text-lg font-medium">Username <span class="text-red-500">*</span></label>
            <input
              type="text"
              name="username"
              value="{{ old('username') }}"
              placeholder="Please enter your username"
              class="w-full border rounded-xl px-4 py-3 text-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-red-400"
              required
            />
          </div>

          <!-- Email -->
          <div class="space-y-2">
            <label class="block text-lg font-medium">Email <span class="text-red-500">*</span></label>
            <input
              type="email"
              name="email"
              value="{{ old('email') }}"
              placeholder="Please enter your email"
              class="w-full border rounded-xl px-4 py-3 text-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-red-400"
              required
            />
          </div>

          <!-- Password -->
          <div class="space-y-2">
            <label class="block text-lg font-medium">Password <span class="text-red-500">*</span></label>
            <input
              type="password"
              name="password"
              placeholder="Please enter your password"
              class="w-full border rounded-xl px-4 py-3 text-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-red-400"
              required
            />
          </div>

          <!-- Confirm Password -->
          <div class="space-y-2">
            <label class="block text-lg font-medium">Confirm Password <span class="text-red-500">*</span></label>
            <input
              type="password"
              name="password_confirmation"
              placeholder="Please confirm your password"
              class="w-full border rounded-xl px-4 py-3 text-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-red-400"
              required
            />
          </div>

          <!-- Role -->
          <div class="space-y-2">
            <label class="block text-lg font-medium">Position you are applying for <span class="text-red-500">*</span></label>
            <select
              name="role"
              class="w-full border rounded-xl px-4 py-3 text-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-red-400"
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

          <!-- Buttons -->
          <div class="space-y-3">
            <!-- Back Button -->
            <button
              type="button"
              onclick="history.back()"
              class="w-full border border-[#2c5282] text-[#2c5282] hover:bg-[#2c5282] hover:text-white font-semibold py-3 rounded-xl text-lg transition duration-300 shadow-md"
            >
              Kembali
            </button>

            <!-- Submit Button -->
            <button
              type="submit"
              class="w-full bg-[#2c5282] hover:bg-[#2c5282]/90 text-white font-semibold py-3 rounded-xl text-lg transition duration-300 shadow-md hover:shadow-lg"
            >
              Submit
            </button>
          </div>

        </form>
      </div>
    </div>
  </body>
</html>