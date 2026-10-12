<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Approval Pembelian Barang (Kepala Unit)') }}
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

                    <h3 class="font-bold text-lg mb-4">Daftar Permohonan Menunggu Approval Unit Anda</h3>

                    <div class="space-y-6">
                        @forelse ($requests as $request)
                        <div class="p-4 border rounded-lg shadow-sm" x-data="{ showSign: false }">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-semibold">{{ $request->user->name }} ({{ $request->user->unit?->value }})</h4>
                                    <p class="text-sm text-gray-600">Tgl Ajuan: {{ $request->requisition_date->format('d M Y') }}</p>
                                    @if($request->signature_path)
                                    <a href="{{ asset('storage/' . $request->signature_path) }}" target="_blank" class="text-xs text-blue-500 hover:underline">(Lihat TTD Pemohon)</a>
                                    @endif
                                </div>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    {{ $request->status }}
                                </span>
                            </div>

                            <table class="min-w-full divide-y divide-gray-200 mt-4 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama Barang</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Perkiraan Harga</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($request->items as $item)
                                    <tr>
                                        <td class="px-4 py-2">{{ $item->nama_barang }}</td>
                                        <td class="px-4 py-2">{{ $item->jumlah_barang }}</td>
                                        <td class="px-4 py-2">Rp {{ number_format($item->perkiraan_harga, 0, ',', '.') }}</td>
                                        <td class="px-4 py-2">{{ $item->keterangan ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="mt-4 flex flex-col md:flex-row justify-end items-center gap-4">
                                <form method="POST" action="{{ route('purchase.approval.unit.reject', $request->id) }}"
                                    onsubmit="return getRejectionReason(event, 'reason-{{ $request->id }}')">
                                    @csrf
                                    <input type="hidden" name="rejection_reason" id="reason-{{ $request->id }}" value="">
                                    <button type="submit" class="w-full text-red-600 hover:text-red-900 font-semibold text-sm">Tolak</button>
                                </form>

                                <button type="button" @click="showSign = !showSign; $nextTick(() => initSignaturePad('sig-pad-unit-{{ $request->id }}', 'sig-data-unit-{{ $request->id }}'))" class="inline-flex items-center px-4 py-2 bg-indigo-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Setujui & Teruskan
                                </button>
                            </div>

                            <div x-show="showSign" x-transition class="mt-4 p-4 border rounded-md bg-gray-50">
                                <form method="POST" action="{{ route('purchase.approval.unit.approve', $request->id) }}" onsubmit="return saveSignature('sig-pad-unit-{{ $request->id }}', 'sig-data-unit-{{ $request->id }}')">
                                    @csrf
                                    <label class="block text-sm font-medium text-gray-700">Tanda Tangan (Kepala Unit):</label>
                                    <canvas id="sig-pad-unit-{{ $request->id }}" class="w-full h-32 border border-gray-400 bg-white rounded-md mt-1"></canvas>
                                    <input type="hidden" name="signature_data" id="sig-data-unit-{{ $request->id }}">
                                    <div class="flex justify-between mt-2">
                                        <button type="button" @click="clearSignature('sig-pad-unit-{{ $request->id }}')" class="text-xs text-red-500 hover:underline">Clear</button>
                                        <x-primary-button class="text-xs">Simpan & Setujui</x-primary-button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @empty
                        <p class="text-gray-500">Tidak ada permohonan pembelian untuk diproses.</p>
                        @endforelse
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