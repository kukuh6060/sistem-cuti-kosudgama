<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah Pengelola
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-6 shadow">
                <form method="POST" action="{{ route('management.managers.store') }}">
                    @csrf

                    <div class="space-y-5">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nama lengkap</label>
                            <input id="name" name="name" type="text"
                                value="{{ old('name') }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input id="email" name="email" type="email"
                                value="{{ old('email') }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700">Jabatan</label>
                            <select id="role" name="role" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Pilih jabatan</option>
                                @foreach ($roles as $role)
                                <option value="{{ $role->value }}" @selected(old('role')===$role->value)>
                                    {{ [
                                            'kepala_unit' => 'Kepala Unit',
                                            'sdm' => 'SDM',
                                            'pengurus' => 'Pengurus',
                                        ][$role->value] }}
                                </option>
                                @endforeach
                            </select>
                            @error('role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="unit" class="block text-sm font-medium text-gray-700">Unit kerja</label>
                            <select id="unit" name="unit" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Pilih unit kerja</option>
                                @foreach ($units as $unit)
                                <option value="{{ $unit->value }}" @selected(old('unit')===$unit->value)>
                                    {{ ucwords(str_replace('_', ' ', $unit->value)) }}
                                </option>
                                @endforeach
                            </select>
                            @error('unit') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Kata sandi</label>
                            <input id="password" name="password" type="password" required minlength="8"
                                autocomplete="new-password"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi kata sandi</label>
                            <input id="password_confirmation" name="password_confirmation"
                                type="password" required minlength="8"
                                autocomplete="new-password"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="submit"
                            class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                            Simpan
                        </button>
                        <a href="{{ route('management.managers.index') }}"
                            class="rounded-md bg-gray-200 px-4 py-2 text-gray-800 hover:bg-gray-300">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>