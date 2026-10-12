<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Isi Data Absensi untuk') }}: {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                
                <form method="POST" action="{{ route('absentee.update', $user) }}" class="p-6 md:p-8" id="form-update-absensi">
                    @csrf
                    @method('PATCH')
                    
                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">{{ session('success') }}</div>
                    @endif

                    <h3 class="text-lg font-semibold text-gray-800">DATA ABSENSI TAHUN {{ $record->year }}</h3>
                    <p class="text-sm text-gray-600 mb-6">Diisi oleh Bag. Umum & SDM</p>
                    
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-md">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <fieldset class="mt-6 border-t pt-6">
                        
                        <legend class="text-base font-medium text-gray-900 mb-4">Tidak masuk bekerja dengan alasan</legend>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <x-input-label for="sakit" :value="__('Sakit (hari)')" />
                                <x-text-input id="sakit" name="sakit" type="number" class="block mt-1 w-full" :value="old('sakit', $record->sakit)" required />
                            </div>
                            <div>
                                <x-input-label for="tanpa_kabar" :value="__('Tanpa kabar (hari)')" />
                                <x-text-input id="tanpa_kabar" name="tanpa_kabar" type="number" class="block mt-1 w-full" :value="old('tanpa_kabar', $record->tanpa_kabar)" required />
                            </div>
                            <div>
                                <x-input-label for="ijin_meninggalkan_tempat" :value="__('Ijin meninggalkan tempat (kali)')" />
                                <x-text-input id="ijin_meninggalkan_tempat" name="ijin_meninggalkan_tempat" type="number" class="block mt-1 w-full" :value="old('ijin_meninggalkan_tempat', $record->ijin_meninggalkan_tempat)" required />
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="mt-8 border-t pt-6">
                        <legend class="text-base font-medium text-gray-900 mb-4">Masuk bekerja tidak penuh dengan alasan</legend>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <x-input-label for="terlambat" :value="__('Terlambat (kali)')" />
                                <x-text-input id="terlambat" name="terlambat" type="number" class="block mt-1 w-full" :value="old('terlambat', $record->terlambat)" required />
                            </div>
                            <div>
                                <x-input-label for="setengah_hari" :value="__('1/2 hari kerja (hari)')" />
                                <x-text-input id="setengah_hari" name="setengah_hari" type="number" class="block mt-1 w-full" :value="old('setengah_hari', $record->setengah_hari)" required />
                            </div>
                            <div>
                                <x-input-label for="pulang_awal" :value="__('Pulang awal (kali)')" />
                                <x-text-input id="pulang_awal" name="pulang_awal" type="number" class="block mt-1 w-full" :value="old('pulang_awal', $record->pulang_awal)" required />
                            </div>
                        </div>
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="ijin_lebih_1_jam" :value="__('Ijin lebih dari 1 jam (kali)')" />
                                <x-text-input id="ijin_lebih_1_jam" name="ijin_lebih_1_jam" type="number" class="block mt-1 w-full" :value="old('ijin_lebih_1_jam', $record->ijin_lebih_1_jam)" required />
                            </div>
                            <div>
                                <x-input-label for="ijin_kurang_1_jam" :value="__('Ijin kurang dari 1 jam (kali)')" />
                                <x-text-input id="ijin_kurang_1_jam" name="ijin_kurang_1_jam" type="number" class="block mt-1 w-full" :value="old('ijin_kurang_1_jam', $record->ijin_kurang_1_jam)" required />
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="mt-8 border-t pt-6">
                        <legend class="text-base font-medium text-gray-900">Sisa Cuti atau Ijin yang Belum Diambil</legend>
                        <p class="text-xs text-gray-500">Mengubah "Sisa Cuti" di sini akan me-reset jatah cuti karyawan di sistem.</p>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="sisa_cuti_input" :value="__('Sisa Cuti (hari)')" />
                                <x-text-input id="sisa_cuti_input" name="sisa_cuti_input" type="number" class="block mt-1 w-full" :value="old('sisa_cuti_input', $record->sisa_cuti_input)" required />
                            </div>
                            <div>
                                <x-input-label for="sisa_ijin_input" :value="__('Sisa Ijin (hari)')" />
                                <x-text-input id="sisa_ijin_input" name="sisa_ijin_input" type="number" class="block mt-1 w-full" :value="old('sisa_ijin_input', $record->sisa_ijin_input)" required />
                            </div>
                        </div>
                    </fieldset>
                    
                    <div class="flex items-center justify-between mt-6 pt-6 border-t">
                        
                        <div class="flex flex-col sm:flex-row gap-x-4 gap-y-2">
                            <button type="submit" form="form-reset-total" formnovalidate class="text-sm text-red-600 hover:text-red-900 hover:underline">
                                Reset Total (1 Tahun)
                            </button>
                            
                            <button type="submit" form="form-reset-absence" formnovalidate class="text-sm text-yellow-600 hover:text-yellow-900 hover:underline">
                                Reset Rekap Bulanan
                            </button>
                        </div>
            
                        <div>
                            <a href="{{ route('absentee.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                Batal
                            </a>
                            
                            <button type="submit" form="form-update-absensi" class="inline-flex items-center px-4 py-2 bg-indigo-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Simpan Data Absensi') }}
                            </button>
                        </div>
                    </div>
                </form>

                <form method="POST" action="{{ route('absentee.reset.absence', $user) }}" id="form-reset-absence" class="hidden"
                      onsubmit="return confirm('Anda yakin ingin me-reset data absensi bulanan (Semua data \n"Tidak Masuk Bekerja" \nDAN \n"Masuk Bekerja Tidak Penuh") \nmenjadi 0?');">
                    @csrf
                    @method('PATCH')
                </form>

                <form method="POST" action="{{ route('absentee.reset', $user) }}" id="form-reset-total" class="hidden"
                      onsubmit="return confirm('Anda yakin ingin MENGHAPUS SEMUA data absensi dan ME-RESET jatah cuti untuk karyawan ini? Tindakan ini tidak bisa dibatalkan.');">
                    @csrf
                    @method('DELETE')
                </form>

            </div>
        </div>
    </div>
</x-app-layout>