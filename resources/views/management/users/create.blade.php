<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah Akun Karyawan
        </h2>
    </x-slot>
    <div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">

            @if ($errors->any())
                <div class="mb-5 rounded-md bg-red-100 border border-red-300 text-red-700 px-4 py-3">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('management.users.store') }}">
                @csrf

                <div>
                    <x-input-label for="name" value="Nama Karyawan" />
                    <x-text-input
                        id="name"
                        name="name"
                        type="text"
                        class="block mt-1 w-full"
                        :value="old('name')"
                        required
                        autofocus
                    />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <x-input-label for="email" value="Email" />
                    <x-text-input
                        id="email"
                        name="email"
                        type="email"
                        class="block mt-1 w-full"
                        :value="old('email')"
                        required
                    />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <x-input-label for="unit" value="Unit Kerja" />
                    <select
                        id="unit"
                        name="unit"
                        class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                    >
                        <option value="">-- Pilih Unit Kerja --</option>
                        @foreach ($units as $unit)
                            <option
                                value="{{ $unit->value }}"
                                @selected(old('unit') === $unit->value)
                            >
                                {{ ucwords(str_replace('_', ' ', $unit->value)) }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('unit')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <x-input-label for="password" value="Kata Sandi Awal" />
                    <x-text-input
                        id="password"
                        name="password"
                        type="password"
                        class="block mt-1 w-full"
                        required
                        autocomplete="new-password"
                    />
                    <p class="text-sm text-gray-500 mt-1">
                        Minimal 8 karakter.
                    </p>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <x-input-label for="password_confirmation" value="Konfirmasi Kata Sandi" />
                    <x-text-input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        class="block mt-1 w-full"
                        required
                        autocomplete="new-password"
                    />
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <a
                        href="{{ route('management.all.index') }}"
                        class="px-4 py-2 rounded-md bg-gray-200 text-gray-800 hover:bg-gray-300"
                    >
                        Batal
                    </a>

                    <x-primary-button>
                        Simpan Akun
                    </x-primary-button>
                </div>
            </form>

        </div>
    </div>
</div>
</x-app-layout>