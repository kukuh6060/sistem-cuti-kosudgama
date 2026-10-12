<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Input Data Absensi Karyawan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">{{ session('success') }}</div>
            @endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <!-- ===== BLOK TAMBAHAN UNTUK LINK REKAP ===== -->
                    <div class="mb-4 pb-4 border-b text-right">
                        <a href="{{ route('absentee.recap') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500">
                            Lihat Rekap Tahunan
                        </a>
                    </div>
                    <!-- ===== AKHIR BLOK TAMBAHAN ===== -->

                    <p class="mb-4">Pilih karyawan untuk mengisi data absensi (Periode Tahun {{ now()->year }}).</p>
                    
                    <!-- ===== BLOK TABEL YANG DIPERBARUI ===== -->
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Nama Karyawan</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($karyawan as $user)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->unit ? ucwords(str_replace('_', ' ', $user->unit->name)) : 'N/A' }}</td>
                                    
                                    <!-- Ini adalah kolom 'Aksi' yang sudah diperbaiki -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('absentee.show', $user) }}" class="text-gray-600 hover:text-gray-900">
                                            Preview
                                        </a>
                                        <span class="text-gray-300 mx-1">|</span>
                                        <a href="{{ route('absentee.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900">
                                            Isi / Edit Data
                                        </a>
                                    </td>
                                    
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">Tidak ada data karyawan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <!-- ===== AKHIR BLOK TABEL ===== -->

                </div>
            </div>
        </div>
    </div>
</x-app-layout>