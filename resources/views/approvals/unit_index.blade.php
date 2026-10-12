<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Approval Kepala Unit') }}
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

                    <h3 class="font-bold text-lg mb-4">Daftar Pengajuan Cuti / Ijin (Harian)</h3>
                    <div class="overflow-x-auto mb-8">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Karyawan</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Alasan</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($requests as $request)
                                <tr x-data="{ showCutiSign: false }">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $request->user->name }}
                                        @if($request->signature_path)
                                        <a href="{{ asset('storage/' . $request->signature_path) }}" target="_blank" class="text-xs text-blue-500 hover:underline block">(Lihat TTD)</a>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($request->leave_type === 'cuti')
                                        <span class="font-semibold text-green-600">Cuti Harian</span>
                                        @else
                                        <span class="font-semibold text-blue-600">Ijin Tidak Masuk</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $request->start_date->format('d M') }} - {{ $request->end_date->format('d M Y') }} ({{$request->total_days}} hari)</td>
                                    <td class="px-6 py-4">{{ Str::limit($request->reason, 50) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button type="button" @click="showCutiSign = !showCutiSign; $nextTick(() => initSignaturePad('sig-pad-cuti-{{ $request->id }}', 'sig-data-cuti-{{ $request->id }}'))" class="text-green-600 hover:text-green-900 font-semibold">Setujui</button>
                                        |
                                        <form action="{{ route('approval.unit.reject', $request->id) }}" method="POST" class="inline"
                                            onsubmit="return getRejectionReason(event, 'reason-{{ $request->id }}')">
                                            @csrf
                                            <input type="hidden" name="rejection_reason" id="reason-{{ $request->id }}" value="">
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-semibold">Tolak</button>
                                        </form>

                                        <div x-show="showCutiSign" x-transition class="mt-4 p-4 border rounded-md bg-gray-50">
                                            <form method="POST" action="{{ route('approval.unit.approve', $request->id) }}" onsubmit="return saveSignature('sig-pad-cuti-{{ $request->id }}', 'sig-data-cuti-{{ $request->id }}')">
                                                @csrf
                                                <label class="block text-sm font-medium text-gray-700">Tanda Tangan (Kepala Unit):</label>
                                                <canvas id="sig-pad-cuti-{{ $request->id }}" class="w-full h-32 border border-gray-400 bg-white rounded-md mt-1"></canvas>
                                                <input type="hidden" name="signature_data" id="sig-data-cuti-{{ $request->id }}">
                                                <div class="flex justify-between mt-2">
                                                    <button type="button" @click="clearSignature('sig-pad-cuti-{{ $request->id }}')" class="text-xs text-red-500 hover:underline">Clear</button>
                                                    <x-primary-button class="text-xs">Simpan & Setujui</x-primary-button>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">Tidak ada pengajuan Cuti / Ijin untuk diproses.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
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
                                        <a href="{{ asset('storage/' . $request->signature_path) }}" target="_blank" class="text-xs text-blue-500 hover:underline block">(Lihat TTD)</a>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $request->leave_date->format('d M Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $request->start_time->format('H:i') }} - {{ $request->end_time->format('H:i') }}</td>
                                    <td class="px-6 py-4">{{ ucwords(str_replace('_', ' ', $request->purpose_category)) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button type="button" @click="showIzinSign = !showIzinSign; $nextTick(() => initSignaturePad('sig-pad-izin-{{ $request->id }}', 'sig-data-izin-{{ $request->id }}'))" class="text-green-600 hover:text-green-900 font-semibold">Setujui</button>
                                        |
                                        <form action="{{ route('approval.unit.partial.reject', $request->id) }}" method="POST" class="inline"
                                            onsubmit="return getRejectionReason(event, 'partial-reason-{{ $request->id }}')">
                                            @csrf
                                            <input type="hidden" name="rejection_reason" id="partial-reason-{{ $request->id }}" value="">
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-semibold">Tolak</button>
                                        </form>

                                        <div x-show="showIzinSign" x-transition class="mt-4 p-4 border rounded-md bg-gray-50">
                                            <form method="POST" action="{{ route('approval.unit.partial.approve', $request->id) }}" onsubmit="return saveSignature('sig-pad-izin-{{ $request->id }}', 'sig-data-izin-{{ $request->id }}')">
                                                @csrf
                                                <label class="block text-sm font-medium text-gray-700">Tanda Tangan (Kepala Unit):</label>
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
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">Tidak ada pengajuan Izin Tempat untuk diproses.</td>
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