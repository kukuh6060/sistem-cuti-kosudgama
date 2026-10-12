<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">
            Buat Akun Baru
        </h2>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="role" :value="__('Role / Jabatan')" />
            <select id="role" name="role" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                <option value="">-- Pilih Role --</option>
                <option value="karyawan" {{ old('role') == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                <option value="kepala_unit" {{ old('role') == 'kepala_unit' ? 'selected' : '' }}>Kepala Unit</option>
                <option value="sdm" {{ old('role') == 'sdm' ? 'selected' : '' }}>Bagian Umum & SDM</option>
                <option value="pengurus" {{ old('role') == 'pengurus' ? 'selected' : '' }}>Pengurus Kosudgama</option>
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="unit" :value="__('Unit Kerja')" />
            <select id="unit" name="unit" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">-- Pilih Unit Anda --</option>
                <option value="simpan_pinjam" {{ old('unit') == 'simpan_pinjam' ? 'selected' : '' }}>Unit Simpan Pinjam</option>
                <option value="apotek" {{ old('unit') == 'apotek' ? 'selected' : '' }}>Unit Apotek</option>
                <option value="niaga" {{ old('unit') == 'niaga' ? 'selected' : '' }}>Unit Niaga</option>
                <option value="jasa" {{ old('unit') == 'jasa' ? 'selected' : '' }}>Unit Jasa</option>
                <option value="sdm" {{ old('unit') == 'sdm' ? 'selected' : '' }}>Unit Umum & SDM</option>
                <option value="lainnya" {{ old('unit') == 'lainnya' ? 'selected' : '' }}>Lainnya / Non-Unit</option>
            </select>
            <x-input-error :messages="$errors->get('unit')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-6">
            <button type="submit" 
                    class="w-full inline-flex items-center justify-center px-4 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-300 ease-in-out hover:scale-[1.02] hover:shadow-lg">
                {{ __('Register') }}
            </button>
        </div>

        <div class="mt-6 pt-6 border-t border-gray-200">
            <a class="inline-block w-full text-center text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 px-4 py-3 rounded-md transition-all duration-300 ease-in-out hover:shadow-sm" 
               href="{{ route('login') }}">
                Sudah punya akun? <span class="font-bold text-indigo-600">Masuk di sini</span>
            </a>
        </div>
    </form>
</x-guest-layout>