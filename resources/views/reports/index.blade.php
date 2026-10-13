<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Rekapitulasi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="GET" action="{{ route('report.index') }}" class="mb-6 flex flex-wrap items-end gap-4 p-4 bg-gray-50 rounded-lg">

                        <div>
                            <x-input-label for="year" :value="__('Pilih Tahun Laporan')" />
                            <select name="year" id="year" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                @foreach($years as $year)
                                <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="start_date" :value="__('Dari Tanggal (Opsional)')" />
                            <x-text-input id="start_date" type="date" name="start_date" :value="$startDate" class="block mt-1 w-full" />
                        </div>

                        <div>
                            <x-input-label for="end_date" :value="__('Sampai Tanggal (Opsional)')" />
                            <x-text-input id="end_date" type="date" name="end_date" :value="$endDate" class="block mt-1 w-full" />
                        </div>

                        <div>
                            <x-primary-button>
                                Tampilkan
                            </x-primary-button>
                        </div>
                    </form>

                    <h3 class="font-bold text-lg mb-4">Laporan Cuti / Ijin (Harian) - Tahun {{ $selectedYear }}</h3>
                    <div class="overflow-x-auto mb-8">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    {{-- Karyawan --}}
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
                                        <a href="{{ route('report.index', array_merge(request()->query(), [
        'sort_leave' => 'name',
        'direction_leave' => ($sortLeave === 'name' && $directionLeave === 'asc') ? 'desc' : 'asc'
    ])) }}" class="hover:text-indigo-600">
                                            Karyawan
                                            @if($sortLeave === 'name')
                                            {{ $directionLeave === 'asc' ? '↑' : '↓' }}
                                            @endif
                                        </a>
                                    </th>

                                    {{-- Tipe --}}
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
                                        <a href="{{ route('report.index', array_merge(request()->query(), [
        'sort_leave' => 'type',
        'direction_leave' => ($sortLeave === 'type' && $directionLeave === 'asc') ? 'desc' : 'asc'
    ])) }}" class="hover:text-indigo-600">
                                            Tipe
                                            @if($sortLeave === 'type')
                                            {{ $directionLeave === 'asc' ? '↑' : '↓' }}
                                            @endif
                                        </a>
                                    </th>

                                    {{-- Tanggal --}}
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
                                        <a href="{{ route('report.index', array_merge(request()->query(), [
        'sort_leave' => 'date',
        'direction_leave' => ($sortLeave === 'date' && $directionLeave === 'asc') ? 'desc' : 'asc'
    ])) }}" class="hover:text-indigo-600">
                                            Tanggal
                                            @if($sortLeave === 'date')
                                            {{ $directionLeave === 'asc' ? '↑' : '↓' }}
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">TTD</th>

                                    {{-- Status --}}
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
                                        <a href="{{ route('report.index', array_merge(request()->query(), [
        'sort_leave' => 'status',
        'direction_leave' => ($sortLeave === 'status' && $directionLeave === 'asc') ? 'desc' : 'asc'
    ])) }}" class="hover:text-indigo-600">
                                            Status
                                            @if($sortLeave === 'status')
                                            {{ $directionLeave === 'asc' ? '↑' : '↓' }}
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Alasan Penolakan</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($leaveReports as $report)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $report->user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $report->leave_type }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $report->start_date->format('d M Y') }} - {{ $report->end_date->format('d M Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($report->signature_path)
                                        <a href="{{ asset('storage/' . $report->signature_path) }}" target="_blank" class="text-xs text-blue-500 hover:underline">(Lihat TTD)</a>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
    @if($report->status == \App\Enums\LeaveStatus::APPROVED_HR)
    bg-green-100 text-green-800
@elseif($report->status == \App\Enums\LeaveStatus::REJECTED)
    bg-red-100 text-red-800
@else
    bg-yellow-100 text-yellow-800
@endif">

                                            @if($report->status === \App\Enums\LeaveStatus::PENDING)
                                            Menunggu Kepala Unit
                                            @elseif($report->status === \App\Enums\LeaveStatus::APPROVED_HEAD)
                                            Disetujui Kepala Unit
                                            @elseif($report->status === \App\Enums\LeaveStatus::APPROVED_SDM)
                                            Disetujui SDM
                                            @elseif($report->status === \App\Enums\LeaveStatus::APPROVED_HR)
                                            Disetujui Pengurus
                                            @elseif($report->status === \App\Enums\LeaveStatus::REJECTED)
                                            Ditolak
                                            @else
                                            {{ $report->status->value }}
                                            @endif


                                        </span>
                                    </td>
                                    <td class="px-6 py-4">{{ $report->rejection_reason ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($report->status != \App\Enums\LeaveStatus::REJECTED)
                                        <a href="{{ route('report.leave.print', $report) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                            Cetak
                                        </a>
                                        @else
                                        -
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">Tidak ada data Cuti Harian untuk periode ini.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <h3 class="font-bold text-lg mb-4 mt-8 border-t pt-6">Laporan Izin Meninggalkan Tempat - Tahun {{ $selectedYear }}</h3>
                    <div class="overflow-x-auto mb-8">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    {{-- Karyawan --}}
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
                                        <a href="{{ route('report.index', array_merge(request()->query(), [
        'sort_partial' => 'name',
        'direction_partial' => ($sortPartial === 'name' && $directionPartial === 'asc') ? 'desc' : 'asc'
    ])) }}" class="hover:text-indigo-600">
                                            Karyawan
                                            @if($sortPartial === 'name')
                                            {{ $directionPartial === 'asc' ? '↑' : '↓' }}
                                            @endif
                                        </a>
                                    </th>

                                    {{-- Tanggal --}}
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
                                        <a href="{{ route('report.index', array_merge(request()->query(), [
        'sort_partial' => 'date',
        'direction_partial' => ($sortPartial === 'date' && $directionPartial === 'asc') ? 'desc' : 'asc'
    ])) }}" class="hover:text-indigo-600">
                                            Tanggal
                                            @if($sortPartial === 'date')
                                            {{ $directionPartial === 'asc' ? '↑' : '↓' }}
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Jam</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">TTD</th>
                                    {{-- Keperluan --}}
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
                                        <a href="{{ route('report.index', array_merge(request()->query(), [
        'sort_partial' => 'purpose',
        'direction_partial' => ($sortPartial === 'purpose' && $directionPartial === 'asc') ? 'desc' : 'asc'
    ])) }}" class="hover:text-indigo-600">
                                            Keperluan
                                            @if($sortPartial === 'purpose')
                                            {{ $directionPartial === 'asc' ? '↑' : '↓' }}
                                            @endif
                                        </a>
                                    </th>

                                    {{-- Status --}}
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
                                        <a href="{{ route('report.index', array_merge(request()->query(), [
        'sort_partial' => 'status',
        'direction_partial' => ($sortPartial === 'status' && $directionPartial === 'asc') ? 'desc' : 'asc'
    ])) }}" class="hover:text-indigo-600">
                                            Status
                                            @if($sortPartial === 'status')
                                            {{ $directionPartial === 'asc' ? '↑' : '↓' }}
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Alasan Penolakan</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($partialReports as $report)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $report->user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $report->leave_date->format('d M Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $report->start_time->format('H:i') }} - {{ $report->end_time->format('H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($report->signature_path)
                                        <a href="{{ asset('storage/' . $report->signature_path) }}" target="_blank" class="text-xs text-blue-500 hover:underline">(Lihat TTD)</a>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($report->purpose_category == 'lain')
                                        {{ $report->purpose_other ?? 'Lain' }}
                                        @else
                                        {{ ucwords(str_replace('_', ' ', $report->purpose_category)) }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
            @if($report->status === \App\Enums\LeaveStatus::APPROVED_SDM)
                bg-green-100 text-green-800
            @elseif($report->status === \App\Enums\LeaveStatus::REJECTED)
                bg-red-100 text-red-800
            @else
                bg-yellow-100 text-yellow-800
            @endif">

                                            @if($report->status === \App\Enums\LeaveStatus::PENDING)
                                            Menunggu Kepala Unit
                                            @elseif($report->status === \App\Enums\LeaveStatus::APPROVED_HEAD)
                                            Disetujui Kepala Unit
                                            @elseif($report->status === \App\Enums\LeaveStatus::APPROVED_SDM)
                                            Disetujui SDM
                                            @elseif($report->status === \App\Enums\LeaveStatus::REJECTED)
                                            Ditolak
                                            @else
                                            {{ $report->status->value }}
                                            @endif

                                        </span>
                                    </td>
                                    <td class="px-6 py-4">{{ $report->rejection_reason ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($report->status != \App\Enums\LeaveStatus::REJECTED)
                                        <a href="{{ route('report.partial.print', $report) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                            Cetak
                                        </a>
                                        @else
                                        -
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">Tidak ada data Izin Tempat untuk periode ini.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <h3 class="font-bold text-lg mb-4 mt-8 border-t pt-6">Laporan Permohonan Pembelian Barang - Tahun {{ $selectedYear }}</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    {{-- Pemohon --}}
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
                                        <a href="{{ route('report.index', array_merge(request()->query(), [
        'sort_purchase' => 'name',
        'direction_purchase' => ($sortPurchase === 'name' && $directionPurchase === 'asc') ? 'desc' : 'asc'
    ])) }}" class="hover:text-indigo-600">
                                            Pemohon
                                            @if($sortPurchase === 'name')
                                            {{ $directionPurchase === 'asc' ? '↑' : '↓' }}
                                            @endif
                                        </a>
                                    </th>

                                    {{-- Tanggal pengajuan --}}
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
                                        <a href="{{ route('report.index', array_merge(request()->query(), [
        'sort_purchase' => 'date',
        'direction_purchase' => ($sortPurchase === 'date' && $directionPurchase === 'asc') ? 'desc' : 'asc'
    ])) }}" class="hover:text-indigo-600">
                                            Tgl Ajuan
                                            @if($sortPurchase === 'date')
                                            {{ $directionPurchase === 'asc' ? '↑' : '↓' }}
                                            @endif
                                        </a>
                                    </th>

                                    {{-- Nomor surat --}}
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
                                        <a href="{{ route('report.index', array_merge(request()->query(), [
        'sort_purchase' => 'number',
        'direction_purchase' => ($sortPurchase === 'number' && $directionPurchase === 'asc') ? 'desc' : 'asc'
    ])) }}" class="hover:text-indigo-600">
                                            No. Surat
                                            @if($sortPurchase === 'number')
                                            {{ $directionPurchase === 'asc' ? '↑' : '↓' }}
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">TTD</th>
                                    {{-- Status --}}
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
                                        <a href="{{ route('report.index', array_merge(request()->query(), [
        'sort_purchase' => 'status',
        'direction_purchase' => ($sortPurchase === 'status' && $directionPurchase === 'asc') ? 'desc' : 'asc'
    ])) }}" class="hover:text-indigo-600">
                                            Status
                                            @if($sortPurchase === 'status')
                                            {{ $directionPurchase === 'asc' ? '↑' : '↓' }}
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Alasan Penolakan</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($purchaseReports as $report)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $report->user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $report->requisition_date->format('d M Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $report->requisition_number ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $report->items->pluck('nama_barang')->join(', ') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($report->signature_path)
                                        <a href="{{ asset('storage/' . $report->signature_path) }}" target="_blank" class="text-xs text-blue-500 hover:underline">(Lihat TTD)</a>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
        @if($report->status == 'approved')
            bg-green-100 text-green-800
        @elseif($report->status == 'rejected')
            bg-red-100 text-red-800
        @else
            bg-yellow-100 text-yellow-800
        @endif">

                                            @if($report->status == 'pending_unit')
                                            Menunggu Kepala Unit
                                            @elseif($report->status == 'approved_head')
                                            Disetujui Kepala Unit
                                            @elseif($report->status == 'approved_sdm')
                                            Disetujui SDM
                                            @elseif($report->status == 'approved')
                                            Disetujui Pengurus
                                            @elseif($report->status == 'rejected')
                                            Ditolak
                                            @else
                                            {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                                            @endif

                                        </span>
                                    </td>

                                    <td class="px-6 py-4">{{ $report->rejection_reason ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($report->status != 'rejected')
                                        <a href="{{ route('report.purchase.print', $report) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                            Cetak
                                        </a>
                                        @else
                                        -
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">Tidak ada data Pembelian Barang untuk periode ini.</td>
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