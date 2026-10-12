<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Rekap Data Absensi Tahunan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="GET" action="{{ route('absentee.recap') }}" class="mb-6 flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                        <div>
                            <x-input-label for="year" :value="__('Pilih Tahun Rekap')" />
                            <select name="year" id="year" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <x-primary-button class="mt-5">
                            Tampilkan
                        </x-primary-button>
                    </form>

                    <div class="mb-4">
                        <a href="{{ route('absentee.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; Kembali ke Input Data (Tahun Ini)</a>
                    </div>
                    
                    <h3 class="font-bold text-lg mb-4">Rekap Absensi Tahun {{ $selectedYear }}</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Karyawan</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Sakit</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Tanpa Kabar</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Terlambat</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">1/2 Hari</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Sisa Cuti (di-set)</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($records as $record)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $record->user->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $record->sakit }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $record->tanpa_kabar }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $record->terlambat }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $record->setengah_hari }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $record->sisa_cuti_input }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada data rekap untuk tahun {{ $selectedYear }}.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>