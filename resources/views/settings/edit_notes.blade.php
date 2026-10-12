<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Catatan Penting Dashboard Karyawan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('settings.notes.update') }}" class="p-6">
                    @csrf
                    @method('PATCH')

                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">
                            {{ session('success') }}
                        </div>
                    @endif

                    <p class="text-sm text-gray-600 mb-2">
                        Masukkan catatan penting yang akan dilihat oleh semua karyawan di dashboard mereka.
                        (Gunakan Enter untuk baris baru).
                    </p>

                    <div>
                        <x-input-label for="catatan_karyawan" :value="__('Isi Catatan Penting')" />
                        <textarea id="catatan_karyawan" name="catatan_karyawan" rows="10" 
                                  class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >{{ old('catatan_karyawan', $catatan->value) }}</textarea>
                        <x-input-error :messages="$errors->get('catatan_karyawan')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <x-primary-button>
                            {{ __('Simpan Perubahan') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>