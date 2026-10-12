<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Approval Final Cuti & Izin (Pengurus)') }}
        </h2>
    </x-slot>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    @endpush

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">{{ session('success') }}</div>
                    @endif
                    <x-input-error :messages="$errors->get('rejection_reason')" class="mb-4 p-4 bg-red-100 text-red-800 rounded-md" />
                    <x-input-error :messages="$errors->get('signature_data')" class="mb-4 p-4 bg-red-100 text-red-800 rounded-md" />
                    <x-input-error :messages="$errors->get('error')" class="mb-4 p-4 bg-red-100 text-red-800 rounded-md" />

                    <h3 class="font-bold text-lg mb-4">Daftar Pengajuan Cuti / Ijin (Harian)</h3>
                    <div class="overflow-x-auto mb-8">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Karyawan</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Detail Ajuan</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">TTD Approval</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($requests as $request)
                                <tr x-data="{ showCutiSign: false }">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $request->user->name }}</td>
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
                                        @php
                                        $quota = $request->user->leaveQuotas->first();
                                        @endphp

                                        @if($request->leave_type === 'cuti' && $quota)
                                        @php
                                        $sisaCuti = $quota->total_quota - $quota->used_quota;
                                        @endphp

                                        <div class="text-sm text-gray-600">
                                            Sisa Cuti:
                                            <strong>{{ $sisaCuti }} hari</strong>
                                        </div>

                                        @if($request->total_days > $sisaCuti)
                                        <div class="text-xs font-bold text-red-500">
                                            JATAH CUTI TIDAK CUKUP
                                        </div>
                                        @endif

                                        @elseif($request->leave_type === 'ijin' && $quota)
                                        @php
                                        $sisaCuti = $quota->total_quota - $quota->used_quota;
                                        $sisaIjin = $quota->total_izin - $quota->used_izin;
                                        @endphp

                                        <div class="text-sm text-gray-600">
                                            Sisa Ijin:
                                            <strong>{{ $sisaIjin }} hari</strong>
                                        </div>

                                        @if($sisaCuti > 0)
                                        <div class="text-xs font-bold text-red-500">
                                            CUTI BELUM HABIS
                                        </div>
                                        @elseif($request->total_days > $sisaIjin)
                                        <div class="text-xs font-bold text-red-500">
                                            JATAH IJIN TIDAK CUKUP
                                        </div>
                                        @endif
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($request->signature_path)
                                        <a href="{{ asset('storage/' . $request->signature_path) }}" target="_blank" class="text-xs text-blue-500 hover:underline block">(Lihat TTD Karyawan)</a>
                                        @endif
                                        @if($request->signature_path_unit)
                                        <a href="{{ asset('storage/' . $request->signature_path_unit) }}" target="_blank" class="text-xs text-green-600 hover:underline block">(Lihat TTD Unit)</a>
                                        @endif
                                        @if($request->signature_path_sdm)
                                        <a href="{{ asset('storage/' . $request->signature_path_sdm) }}" target="_blank" class="text-xs text-purple-600 hover:underline block">(Lihat TTD SDM)</a>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button type="button" @click="showCutiSign = !showCutiSign; $nextTick(() => initSignaturePad('sig-pad-cuti-{{ $request->id }}', 'sig-data-cuti-{{ $request->id }}'))" class="text-green-600 hover:text-green-900 font-semibold">Setujui</button>
                                        |
                                        <form action="{{ route('approval.pengurus.leave.reject', $request->id) }}" method="POST" class="inline"
                                            onsubmit="return getRejectionReason(event, 'reason-{{ $request->id }}')">
                                            @csrf
                                            <input type="hidden" name="rejection_reason" id="reason-{{ $request->id }}" value="">
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-semibold">Tolak</button>
                                        </form>

                                        <div x-show="showCutiSign" x-transition class="mt-4 p-4 border rounded-md bg-gray-50">
                                            <form method="POST" action="{{ route('approval.pengurus.leave.approve', $request->id) }}" onsubmit="return saveSignature('sig-pad-cuti-{{ $request->id }}', 'sig-data-cuti-{{ $request->id }}')">
                                                @csrf
                                                <label class="block text-sm font-medium text-gray-700">Tanda Tangan (Pengurus):</label>
                                                <canvas id="sig-pad-cuti-{{ $request->id }}" class="w-full h-32 border border-gray-400 bg-white rounded-md mt-1"></canvas>
                                                <input type="hidden" name="signature_data" id="sig-data-cuti-{{ $request->id }}">
                                                <div class="flex justify-between mt-2">
                                                    <button type="button" @click="clearSignature('sig-pad-cuti-{{ $request->id }}')" class="text-xs text-red-500 hover:underline">Clear</button>
                                                    <x-primary-button class="text-xs">Simpan & Setujui Final</x-primary-button>
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

                    
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        window.signaturePads = {};

        function initSignaturePad(canvasId, dataInputId) {
            if (window.signaturePads[canvasId]) {
                window.signaturePads[canvasId].clear();
            }
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;
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
                setTimeout(resizeCanvas, 200);
            }
        }

        function saveSignature(canvasId, dataInputId) {
            const signaturePad = window.signaturePads[canvasId];
            if (signaturePad.isEmpty()) {
                alert('Mohon berikan tanda tangan Anda untuk menyetujui.');
                return false;
            }
            document.getElementById(dataInputId).value = signaturePad.toDataURL('image/png');
            return true;
        }

        function clearSignature(canvasId) {
            const signaturePad = window.signaturePads[canvasId];
            signaturePad.clear();
        }

        function getRejectionReason(event, inputId) {
            const reason = prompt('Harap masukkan alasan penolakan (wajib diisi):');
            if (reason === null || reason.trim() === '') {
                alert('Alasan penolakan tidak boleh kosong.');
                event.preventDefault();
                return false;
            }
            document.getElementById(inputId).value = reason;
            return true;
        }
    </script>
    @endpush
</x-app-layout>