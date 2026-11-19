<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Approval SDM') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">{{ session('success') }}</div>
                    @endif
                    <x-input-error :messages="$errors->get('rejection_reason')" class="mb-4 p-4 bg-red-100 text-red-800 rounded-md" />
                    <x-input-error :messages="$errors->get('signature_data')" class="mb-4 p-4 bg-red-100 text-red-800 rounded-md" />

                    <h3 class="font-bold text-lg mb-4">Daftar Pengajuan Cuti / Ijin (Harian)</h3>
                    <div class="overflow-x-auto mb-8">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Karyawan</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Detail Ajuan</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Info Karyawan (Sisa Cuti & Absensi)</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($requests as $request)
                                <tr x-data="{ showCutiSign: false }">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $request->user->name }}
                                        @if($request->signature_path)
                                        <a href="{{ asset('storage/' . $request->signature_path) }}" target="_blank" class="text-xs text-blue-500 hover:underline block">(Lihat TTD Karyawan)</a>
                                        @endif
                                        @if($request->signature_path_unit)
                                        <a href="{{ asset('storage/' . $request->signature_path_unit) }}" target="_blank" class="text-xs text-green-600 hover:underline block">(Lihat TTD Unit)</a>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            Tipe:
                                            @if($request->leave_type === 'cuti')
                                            <span class="text-green-600">Cuti Harian</span>
                                            @else
                                            <span class="text-blue-600">Ijin Tidak Masuk</span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $request->start_date->format('d M') }} - {{ $request->end_date->format('d M Y') }}</div>
                                        <div class="text-sm text-gray-500">Total: <strong>{{$request->total_days}} hari</strong></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        @php
                                        $quota = $request->user->leaveQuotas()
                                        ->where('year', now()->year)
                                        ->first();
                                        $absensi = $request->user->absenteeRecord;
                                        $isCuti = $request->leave_type == 'cuti';
                                        @endphp
                                        @if ($isCuti && $quota)
                                        @php
                                        $sisa = $quota->total_quota - $quota->used_quota;
                                        @endphp

                                        <div>
                                            Sisa Cuti:
                                            <strong>{{ $sisa }} hari</strong>
                                        </div>

                                        @if ($sisa < $request->total_days)
                                            <div class="text-xs font-bold text-red-500">
                                                JATAH TIDAK CUKUP
                                            </div>
                                            @endif

                                            @elseif ($isCuti && !$quota)

                                            <div class="text-red-500">
                                                Data jatah tidak ada.
                                            </div>

                                            @elseif (!$isCuti && $quota)

                                            @php
                                            $sisaIjin = $quota->total_izin - $quota->used_izin;
                                            $sisaCuti = $quota->total_quota - $quota->used_quota;
                                            @endphp

                                            <div>
                                                Sisa Ijin:
                                                <strong>{{ $sisaIjin }} hari</strong>
                                            </div>

                                            @if ($sisaCuti > 0)
                                            <div class="text-xs font-bold text-red-500">
                                                CUTI BELUM HABIS
                                            </div>
                                            @elseif ($sisaIjin < $request->total_days)
                                                <div class="text-xs font-bold text-red-500">
                                                    JATAH IJIN TIDAK CUKUP
                                                </div>
                                                @endif

                                                @else

                                                <div class="text-red-500">
                                                    Data jatah tidak ada.
                                                </div>
                                                @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button type="button" @click="showCutiSign = !showCutiSign; $nextTick(() => initSignaturePad('sig-pad-cuti-{{ $request->id }}', 'sig-data-cuti-{{ $request->id }}'))" class="text-green-600 hover:text-green-900 font-semibold">Setujui</button>
                                        |
                                        <form action="{{ route('approval.sdm.reject', $request->id) }}" method="POST" class="inline"
                                            onsubmit="return getRejectionReason(event, 'reason-{{ $request->id }}')">
                                            @csrf
                                            <input type="hidden" name="rejection_reason" id="reason-{{ $request->id }}" value="">
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-semibold">Tolak</button>
                                        </form>

                                        <div x-show="showCutiSign" x-transition class="mt-4 p-4 border rounded-md bg-gray-50">
                                            <form method="POST" action="{{ route('approval.sdm.approve', $request->id) }}" onsubmit="return saveSignature('sig-pad-cuti-{{ $request->id }}', 'sig-data-cuti-{{ $request->id }}')">
                                                @csrf
                                                <label class="block text-sm font-medium text-gray-700">Tanda Tangan (SDM):</label>
                                                <canvas id="sig-pad-cuti-{{ $request->id }}" class="w-full h-32 border border-gray-400 bg-white rounded-md mt-1"></canvas>
                                                <input type="hidden" name="signature_data" id="sig-data-cuti-{{ $request->id }}">
                                                <div class="flex justify-between mt-2">
                                                    <button type="button" @click="clearSignature('sig-pad-cuti-{{ $request->id }}')" class="text-xs text-red-500 hover:underline">Clear</button>
                                                    <x-primary-button class="text-xs">Simpan & Teruskan</x-primary-button>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">Tidak ada pengajuan Cuti / Ijin untuk diproses.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- DAFTAR MENUNGGU PERSETUJUAN FINAL CUTI / IJIN --}}
                    <div class="mt-10 border-t pt-6">

                        <h3 class="font-bold text-lg mb-4">
                            Daftar Pengajuan Cuti / Ijin Menunggu Persetujuan Final
                        </h3>

                        <div class="space-y-6">

                            @forelse ($finalRequests as $request)

                            <div class="p-4 border border-indigo-200 rounded-lg shadow-sm bg-indigo-50">

                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-semibold">
                                            {{ $request->user->name }}
                                            ({{ $request->user->unit?->value }})
                                        </h4>

                                        <p class="text-sm text-gray-600">
                                            Jenis:
                                            {{ $request->leave_type === 'cuti' ? 'Cuti Harian' : 'Ijin Tidak Masuk' }}
                                        </p>

                                        <p class="text-sm text-gray-600">
                                            Tanggal:
                                            {{ $request->start_date->format('d M Y') }}
                                            -
                                            {{ $request->end_date->format('d M Y') }}
                                        </p>

                                        <p class="text-sm text-gray-600">
                                            Total: {{ $request->total_days }} hari
                                        </p>

                                        @if($request->signature_path_unit)
                                        <a href="{{ asset('storage/' . $request->signature_path_unit) }}"
                                            target="_blank"
                                            class="text-xs text-green-600 hover:underline block mt-2">
                                            Lihat TTD Kepala/Wakil Kepala Unit
                                        </a>
                                        @endif

                                        @if($request->signature_path_sdm)
                                        <a href="{{ asset('storage/' . $request->signature_path_sdm) }}"
                                            target="_blank"
                                            class="text-xs text-blue-600 hover:underline block">
                                            Lihat TTD SDM
                                        </a>
                                        @endif
                                    </div>

                                    <span class="text-sm font-medium text-indigo-700">
                                        Menunggu Persetujuan Final
                                    </span>
                                </div>

                                <div class="mt-4 p-3 bg-white border rounded-md">

                                    <p class="text-sm text-gray-700 mb-3">
                                        Pastikan dokumen sudah dicetak dan ditandatangani
                                        Pengurus secara offline sebelum memberikan persetujuan final.
                                    </p>

                                    <a href="{{ route('report.leave.print', $request->id) }}"
                                        target="_blank"
                                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md text-xs font-semibold uppercase hover:bg-gray-700">
                                        Cetak Formulir
                                    </a>

                                    <form method="POST"
                                        action="{{ route('approval.sdm.final', $request->id) }}"
                                        class="mt-4"
                                        onsubmit="return confirm('Pastikan dokumen sudah ditandatangani Pengurus . Lanjutkan persetujuan final?')">

                                        @csrf

                                        <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                            Persetujuan Final SDM
                                        </button>

                                    </form>
                                </div>

                            </div>

                            @empty

                            <p class="text-gray-500">
                                Tidak ada pengajuan Cuti / Ijin yang menunggu persetujuan final.
                            </p>

                            @endforelse

                        </div>
                    </div>

                    <h3 class="font-bold text-lg mb-4 border-t pt-6">Daftar Pengajuan Izin Meninggalkan Tempat</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Karyawan</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Jam</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Keperluan</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($partialRequests as $request)
                                <tr x-data="{ showIzinSign: false }">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $request->user->name }}
                                        @if($request->signature_path)
                                        <a href="{{ asset('storage/' . $request->signature_path) }}" target="_blank" class="text-xs text-blue-500 hover:underline block">(Lihat TTD Karyawan)</a>
                                        @endif
                                        @if($request->signature_path_unit)
                                        <a href="{{ asset('storage/' . $request->signature_path_unit) }}" target="_blank" class="text-xs text-green-600 hover:underline block">(Lihat TTD Unit)</a>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $request->leave_date->format('d M Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $request->start_time->format('H:i') }} - {{ $request->end_time->format('H:i') }}</td>
                                    <td class="px-6 py-4">{{ ucwords(str_replace('_', ' ', $request->purpose_category)) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button type="button" @click="showIzinSign = !showIzinSign; $nextTick(() => initSignaturePad('sig-pad-izin-{{ $request->id }}', 'sig-data-izin-{{ $request->id }}'))" class="text-green-600 hover:text-green-900 font-semibold">Setujui</button>
                                        |
                                        <form action="{{ route('approval.sdm.partial.reject', $request->id) }}" method="POST" class="inline"
                                            onsubmit="return getRejectionReason(event, 'partial-reason-{{ $request->id }}')">
                                            @csrf
                                            <input type="hidden" name="rejection_reason" id="partial-reason-{{ $request->id }}" value="">
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-semibold">Tolak</button>
                                        </form>

                                        <div x-show="showIzinSign" x-transition class="mt-4 p-4 border rounded-md bg-gray-50">
                                            <form method="POST" action="{{ route('approval.sdm.partial.approve', $request->id) }}" onsubmit="return saveSignature('sig-pad-izin-{{ $request->id }}', 'sig-data-izin-{{ $request->id }}')">
                                                @csrf
                                                <label class="block text-sm font-medium text-gray-700">Tanda Tangan (SDM):</label>
                                                <canvas id="sig-pad-izin-{{ $request->id }}" class="w-full h-32 border border-gray-400 bg-white rounded-md mt-1"></canvas>
                                                <input type="hidden" name="signature_data" id="sig-data-izin-{{ $request->id }}">
                                                <div class="flex justify-between mt-2">
                                                    <button type="button" @click="clearSignature('sig-pad-izin-{{ $request->id }}')" class="text-xs text-red-500 hover:underline">Clear</button>
                                                    <x-primary-button class="text-xs">Simpan & Setujui</x-primary-button>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">Tidak ada pengajuan Izin Meninggalkan Tempat untuk diproses.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script>
        window.addEventListener('load', function() {
            window.signaturePads = {};

            window.initSignaturePad = function(canvasId, dataInputId) {
                if (window.signaturePads[canvasId]) {
                    window.signaturePads[canvasId].clear();
                }
                const canvas = document.getElementById(canvasId);
                if (!canvas) return;

                if (typeof SignaturePad === 'undefined') {
                    console.error('SignaturePad library not loaded.');
                    return;
                }

                const signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgb(255, 255, 255)'
                });
                window.signaturePads[canvasId] = signaturePad;

                function resizeCanvas() {
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    canvas.getContext('2d').scale(ratio, ratio);
                    signaturePad.clear();
                }

                if (canvas.offsetWidth > 0) {
                    resizeCanvas();
                } else {
                    setTimeout(resizeCanvas, 300);
                }
            }

            window.saveSignature = function(canvasId, dataInputId) {
                const signaturePad = window.signaturePads[canvasId];
                if (!signaturePad) {
                    alert('Kesalahan: Signature pad tidak terinisialisasi.');
                    return false;
                }
                if (signaturePad.isEmpty()) {
                    alert('Mohon berikan tanda tangan Anda untuk menyetujui.');
                    return false;
                }
                document.getElementById(dataInputId).value = signaturePad.toDataURL('image/png');
                return true;
            }

            window.clearSignature = function(canvasId) {
                const signaturePad = window.signaturePads[canvasId];
                if (signaturePad) {
                    signaturePad.clear();
                }
            }

            window.getRejectionReason = function(event, inputId) {
                const reason = prompt('Harap masukkan alasan penolakan (wajib diisi):');
                if (reason === null) {
                    event.preventDefault();
                    return false;
                }
                if (reason.trim() === '') {
                    alert('Alasan penolakan tidak boleh kosong.');
                    event.preventDefault();
                    return false;
                }
                document.getElementById(inputId).value = reason;
                return true;
            }
        });
    </script>
    @endpush
</x-app-layout>