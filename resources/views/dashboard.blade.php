<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if(Auth::user()->role == \App\Enums\UserRole::KARYAWAN)
                    <h3 class="font-bold text-lg">Selamat Datang, {{ Auth::user()->name }}</h3>
                    <div class="mb-4">
                        <p class="text-sm text-gray-600">
                            Unit Kerja:
                            <strong class="text-gray-800">
                                {{ Auth::user()->unit
                    ? ucwords(str_replace('_', ' ', Auth::user()->unit->value))
                    : 'Belum ditentukan' }}
                            </strong>
                        </p>
                    </div>
                    @if($myQuota)
                    <div class="mb-6 p-4 bg-blue-100 border border-blue-300 rounded-md">
                        <h3 class="font-bold text-lg">Jatah Cuti Tahun {{ $myQuota->year }}</h3>
                        <p>Total Jatah: <strong>{{ $myQuota->total_quota }} hari</strong></p>
                        <p>Terpakai: <strong>{{ $myQuota->used_quota }} hari</strong></p>
                        <p class="text-xl font-bold">Sisa Cuti: <strong>{{ $myQuota->total_quota - $myQuota->used_quota }} hari</strong></p>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="{{ route('cuti.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500">
                                Ajukan Cuti / Ijin (Harian)
                            </a>
                            <a href="{{ route('partial-leave.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500">
                                Ajukan Izin Meninggalkan Tempat
                            </a>
                            <!-- TOMBOL BARU DITAMBAHKAN DI SINI -->
                            <a href="{{ route('purchase.create') }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-500">
                                Ajukan Pembelian Barang
                            </a>
                        </div>
                    </div>
                    @endif

                    @if($catatanPenting)
                    <div class="mb-6 p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                        <h3 class="font-bold text-lg mb-3 text-gray-800">Catatan Penting</h3>
                        <div class="prose prose-sm text-gray-600 max-w-none">
                            {!! nl2br(e($catatanPenting)) !!}
                        </div>
                    </div>
                    @endif

                    <h3 class="font-bold text-lg mb-4">Riwayat Pengajuan Saya</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Total Hari</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($myRequests as $request)
                                <tr>
                                    <td class="px-6 py-4">{{ $request->leave_type }}</td>
                                    <td class="px-6 py-4">{{ $request->start_date->format('d M Y') }} - {{ $request->end_date->format('d M Y') }}</td>
                                    <td class="px-6 py-4">{{ $request->total_days }} hari</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
    @if($request->status == \App\Enums\LeaveStatus::APPROVED_HR) bg-green-100 text-green-800 
    @elseif($request->status == \App\Enums\LeaveStatus::REJECTED) bg-red-100 text-red-800
    @else bg-yellow-100 text-yellow-800 @endif">

                                            @if($request->status == \App\Enums\LeaveStatus::PENDING)
                                            Menunggu Kepala Unit
                                            @elseif($request->status == \App\Enums\LeaveStatus::APPROVED_HEAD)
                                            Disetujui Kepala Unit
                                            @elseif($request->status === \App\Enums\LeaveStatus::APPROVED_SDM)
                                            Disetujui SDM
                                            @elseif($request->status === \App\Enums\LeaveStatus::APPROVED_HR)
                                            Disetujui Pengurus
                                            @elseif($request->status === \App\Enums\LeaveStatus::REJECTED)
                                            Ditolak
                                            @else
                                            {{ $request->status->value }}
                                            @endif

                                        </span>

                                        @if($request->status == \App\Enums\LeaveStatus::REJECTED)
                                        <div class="text-xs text-red-700 mt-1 italic">
                                            Alasan: {{ $request->rejection_reason }}
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">Belum ada data pengajuan.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <h3 class="font-bold text-lg mb-4 mt-8">Riwayat Izin Meninggalkan Tempat</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Jam</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Keperluan</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($myPartialRequests as $request)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $request->leave_date->format('d M Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $request->start_time ? $request->start_time->format('H:i') : '' }} - {{ $request->end_time ? $request->end_time->format('H:i') : '' }}</td>
                                    <td class="px-6 py-4">
                                        @if($request->purpose_category == 'lain')
                                        {{ $request->purpose_other ?? 'Lain' }}
                                        @else
                                        {{ ucwords(str_replace('_', ' ', $request->purpose_category)) }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                @if($request->status == \App\Enums\LeaveStatus::APPROVED_SDM)
                    bg-green-100 text-green-800
                @elseif($request->status == \App\Enums\LeaveStatus::REJECTED)
                    bg-red-100 text-red-800
                @else
                    bg-yellow-100 text-yellow-800
                @endif">

                                            @if($request->status == \App\Enums\LeaveStatus::PENDING)
                                            Menunggu Kepala Unit
                                            @elseif($request->status == \App\Enums\LeaveStatus::APPROVED_HEAD)
                                            Disetujui Kepala Unit
                                            @elseif($request->status == \App\Enums\LeaveStatus::APPROVED_SDM)
                                            Disetujui SDM
                                            @elseif($request->status == \App\Enums\LeaveStatus::REJECTED)
                                            Ditolak
                                            @else
                                            {{ $request->status->value }}
                                            @endif

                                        </span>

                                        @if($request->status == \App\Enums\LeaveStatus::REJECTED)
                                        <div class="text-xs text-red-700 mt-1 italic">
                                            Alasan: {{ $request->rejection_reason }}
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">Belum ada data pengajuan izin.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- TABEL BARU DITAMBAHKAN DI SINI -->
                    <h3 class="font-bold text-lg mb-4 mt-8">Riwayat Permohonan Pembelian Barang</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Tgl Ajuan</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">No. Surat</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($myPurchaseRequests as $request)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $request->requisition_date->format('d M Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $request->requisition_number ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        {{ $request->items->pluck('nama_barang')->join(', ') }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
        @if($request->status == 'approved')
            bg-green-100 text-green-800
        @elseif($request->status == 'rejected')
            bg-red-100 text-red-800
        @else
            bg-yellow-100 text-yellow-800
        @endif">

                                            @if($request->status == 'pending_unit')
                                            Menunggu Kepala Unit
                                            @elseif($request->status == 'approved_head')
                                            Disetujui Kepala Unit
                                            @elseif($request->status == 'approved_sdm')
                                            Disetujui SDM
                                            @elseif($request->status == 'approved')
                                            Disetujui Pengurus
                                            @elseif($request->status == 'rejected')
                                            Ditolak
                                            @else
                                            {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                            @endif

                                        </span>

                                        @if($request->status == 'rejected')
                                        <div class="text-xs text-red-700 mt-1 italic">
                                            Alasan: {{ $request->rejection_reason }}
                                        </div>
                                        @endif
                                    </td>

                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">Belum ada data permohonan pembelian.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @else
                    <h3 class="font-bold text-lg">Selamat Datang, {{ Auth::user()->name }}</h3>
                    <p class="mt-2">Anda login sebagai <strong>{{ Auth::user()->role->value }}</strong>. Silakan gunakan menu navigasi di atas untuk mengakses fitur Anda.</p>
                    @if(in_array(Auth::user()->role, [
                    \App\Enums\UserRole::KEPALA_UNIT
                    ]))
                    <div class="mb-4">
                        <p class="text-sm text-gray-600">
                            Unit Kerja:
                            <strong class="text-gray-800">
                                {{ Auth::user()->unit
                    ? ucwords(str_replace('_', ' ', Auth::user()->unit->value))
                    : 'Belum ditentukan' }}
                            </strong>
                        </p>
                    </div>
                    @endif
                    <div class="mt-6">
                        @if(Auth::user()->role == \App\Enums\UserRole::KEPALA_UNIT)
                        <a href="{{ route('approval.unit.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                            Buka Halaman Approval Unit
                        </a>
                        @endif
                        @if(Auth::user()->role == \App\Enums\UserRole::SDM)
                        <a href="{{ route('approval.sdm.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                            Buka Halaman Approval SDM
                        </a>
                        @endif
                        @if(Auth::user()->role == \App\Enums\UserRole::PENGURUS)
                        <a href="{{ route('report.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                            Buka Halaman Laporan
                        </a>
                        @endif
                    </div>

                    @if($catatanPenting)
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="font-bold text-lg mb-3 text-gray-800">Catatan Penting (Pengumuman Karyawan)</h3>
                        <div class="prose prose-sm text-gray-600 max-w-none p-4 bg-gray-50 rounded-md">
                            {!! nl2br(e($catatanPenting)) !!}
                        </div>
                    </div>
                    @endif
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>