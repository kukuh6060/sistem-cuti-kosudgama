<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Preview Data Absensi untuk') }}: {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900">
                    
                    <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-6 pb-4 border-b">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">DATA ABSENSI TAHUN {{ $record->year }}</h3>
                            <p class="text-sm text-gray-600">Karyawan: {{ $user->name }} (Unit: {{ $user->unit ? ucwords(str_replace('_', ' ', $user->unit->name)) : 'N/A' }})</p>
                        </div>
                        <a href="{{ route('absentee.edit', $user) }}" class="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                            Isi / Edit Data
                        </a>
                    </div>

                    <fieldset class="mt-6">
                        <legend class="text-base font-medium text-gray-900">Tidak masuk bekerja dengan alasan</legend>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <x-input-label :value="__('Sakit (hari)')" class="text-xs" />
                                <p class="mt-1 text-lg font-semibold text-gray-800">{{ $record->sakit }}</p>
                            </div>
                            <div>
                                <x-input-label :value="__('Tanpa kabar (hari)')" class="text-xs" />
                                <p class="mt-1 text-lg font-semibold text-gray-800">{{ $record->tanpa_kabar }}</p>
                            </div>
                            <div>
                                <x-input-label :value="__('Ijin meninggalkan tempat (kali)')" class="text-xs" />
                                <p class="mt-1 text-lg font-semibold text-gray-800">{{ $record->ijin_meninggalkan_tempat }}</p>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="mt-6 border-t pt-4">
                        <legend class="text-base font-medium text-gray-900">Masuk bekerja tidak penuh dengan alasan</legend>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <x-input-label :value="__('Terlambat (kali)')" class="text-xs" />
                                <p class="mt-1 text-lg font-semibold text-gray-800">{{ $record->terlambat }}</p>
                            </div>
                            <div>
                                <x-input-label :value="__('1/2 hari kerja (hari)')" class="text-xs" />
                                <p class="mt-1 text-lg font-semibold text-gray-800">{{ $record->setengah_hari }}</p>
                            </div>
                            <div>
                                <x-input-label :value="__('Pulang awal (kali)')" class="text-xs" />
                                <p class="mt-1 text-lg font-semibold text-gray-800">{{ $record->pulang_awal }}</p>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label :value="__('Ijin lebih dari 1 jam (kali)')" class="text-xs" />
                                <p class="mt-1 text-lg font-semibold text-gray-800">{{ $record->ijin_lebih_1_jam }}</p>
                            </div>
                            <div>
                                <x-input-label :value="__('Ijin kurang dari 1 jam (kali)')" class="text-xs" />
                                <p class="mt-1 text-lg font-semibold text-gray-800">{{ $record->ijin_kurang_1_jam }}</p>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="mt-6 border-t pt-4">
                        <legend class="text-base font-medium text-gray-900">Sisa Cuti atau Ijin yang Belum Diambil (Tercatat)</legend>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label :value="__('Sisa Cuti (hari)')" class="text-xs" />
                                <p class="mt-1 text-lg font-semibold text-gray-800">{{ $record->sisa_cuti_input }}</p>
                            </div>
                            <div>
                                <x-input-label :value="__('Sisa Ijin (hari)')" class="text-xs" />
                                <p class="mt-1 text-lg font-semibold text-gray-800">{{ $record->sisa_ijin_input }}</p>
                            </div>
                        </div>
                    </fieldset>

                    <div class="flex items-center justify-end mt-6 pt-6 border-t">
                        <a href="{{ route('absentee.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                            Kembali ke Daftar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>