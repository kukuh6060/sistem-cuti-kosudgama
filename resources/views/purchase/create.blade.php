<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Formulir Permohonan Pembelian Barang') }}
        </h2>
    </x-slot>

    <script src="//unpkg.com/alpinejs" defer></script>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('purchase.store') }}" enctype="multipart/form-data" 
                      x-data="purchaseForm()"> @csrf
                    <div class="p-6 md:p-8 text-gray-900">
                        
                        @if ($errors->any())
                            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-md">
                                <ul class="list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="name" :value="__('Nama Pemohon')" />
                                <x-text-input id="name" class="block mt-1 w-full bg-gray-100" type="text" :value="Auth::user()->name" readonly disabled />
                            </div>
                            <div>
                                <x-input-label for="unit_kerja" :value="__('Unit Kerja')" />
                                <x-text-input id="unit_kerja" class="block mt-1 w-full bg-gray-100" type="text" :value="ucwords(str_replace('_', ' ', Auth::user()->unit?->value ?? 'N/A'))" readonly disabled />
                            </div>
                        </div>
                        <div class="mt-4">
                            <x-input-label for="requisition_date" :value="__('Tanggal Permohonan')" />
                            <x-text-input id="requisition_date" class="block mt-1 w-full" type="date" name="requisition_date" :value="old('requisition_date', now()->format('Y-m-d'))" required />
                        </div>

                        <div class="mt-6 border-t pt-6">
                            <h3 class="font-medium text-lg mb-4">Daftar Barang yang Diajukan</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead>
                                        <tr>
                                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama Barang</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase w-24">Jumlah</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase w-32">Perkiraan Harga</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase w-32">Harga (Total)</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                                            <th class="w-10"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(item, index) in items" :key="index">
                                            <tr class="border-b">
                                                <td class="p-2">
                                                    <input :name="'items[' + index + '][nama_barang]'" type="text" x-model="item.nama_barang" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                                </td>
                                                <td class="p-2">
                                                    <input :name="'items[' + index + '][jumlah_barang]'" type="number" x-model.number="item.jumlah_barang" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required min="1">
                                                </td>
                                                <td class="p-2">
                                                    <input :name="'items[' + index + '][perkiraan_harga]'" type="number" x-model.number="item.perkiraan_harga" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" min="0" placeholder="Opsional">
                                                </td>
                                                <td class="p-2">
                                                    <input :name="'items[' + index + '][harga]'" type="number" x-model.number="item.harga" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" min="0" placeholder="Opsional">
                                                </td>
                                                <td class="p-2">
                                                    <input :name="'items[' + index + '][keterangan]'" type="text" x-model="item.keterangan" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Opsional">
                                                </td>
                                                <td class="p-2 text-center">
                                                    <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700 font-bold">&times;</button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" @click="addItem()" class="mt-4 px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm hover:bg-gray-300">
                                + Tambah Barang
                            </button>
                        </div>

                        <div class="mt-6 border-t pt-6">
                            <h3 class="font-medium text-lg mb-4">{{ __('Pemohon (Tanda Tangan)') }}</h3>
                            <div class="mb-4 space-y-2">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="signature_mode" value="draw" class="h-4 w-4 text-indigo-600" checked>
                                    <span class="ms-2 text-sm text-gray-700">Gambar Tanda Tangan Digital</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="signature_mode" value="upload" class="h-4 w-4 text-indigo-600">
                                    <span class="ms-2 text-sm text-gray-700">Unggah File Tanda Tangan (PNG)</span>
                                </label>
                            </div>
                            <div id="drawSignatureSection">
                                <canvas id="signaturePad" class="w-full h-48 border border-gray-400 bg-white rounded-md cursor-crosshair"></canvas>
                                <div class="mt-2 flex justify-between items-center">
                                    <p class="text-xs text-gray-500">Silakan tanda tangan di kotak putih di atas.</p>
                                    <div>
                                        <button type="button" id="undoSignature" class="px-3 py-1 bg-gray-500 text-white rounded-md text-sm hover:bg-gray-600 mr-2">Undo</button>
                                        <button type="button" id="clearSignature" class="px-3 py-1 bg-red-500 text-white rounded-md text-sm hover:bg-red-600">Clear</button>
                                    </div>
                                </div>
                                <input type="hidden" name="signature_data" id="signatureData">
                            </div>
                            <div id="uploadSignatureSection" class="hidden">
                                <x-input-label for="signature_file" :value="__('Pilih file gambar tanda tangan Anda (PNG, maks 2MB)')" />
                                <x-text-input id="signature_file" class="block mt-1 w-full" type="file" name="signature_file" accept="image/png" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6 pt-6 border-t">
                            <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                Batal
                            </a>
                            <x-primary-button>
                                {{ __('Kirim Permohonan') }}
                            </x-primary-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script>
        // [1] Logika Alpine.js untuk Form Dinamis
        function purchaseForm() {
            return {
                items: [
                    { nama_barang: '', jumlah_barang: 1, perkiraan_harga: 0, harga: 0, keterangan: '' }
                ],
                addItem() {
                    this.items.push({
                        nama_barang: '',
                        jumlah_barang: 1,
                        perkiraan_harga: 0,
                        harga: 0,
                        keterangan: ''
                    });
                },
                removeItem(index) {
                    this.items.splice(index, 1);
                }
            }
        }

        // [2] Logika Tanda Tangan (Sama seperti form izin)
        window.addEventListener('load', function() {
            const canvas = document.getElementById('signaturePad');
            if (!canvas) return; 
            const signaturePad = new SignaturePad(canvas, { backgroundColor: 'rgb(255, 255, 255)' });
            const signatureDataInput = document.getElementById('signatureData');
            const signatureFileInput = document.getElementById('signature_file');
            const form = document.querySelector('form');
            const drawSection = document.getElementById('drawSignatureSection');
            const uploadSection = document.getElementById('uploadSignatureSection');

            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext('2d').scale(ratio, ratio);
                signaturePad.clear();
                signatureDataInput.value = '';
            }
            window.addEventListener('resize', resizeCanvas);
            resizeCanvas();

            signaturePad.addEventListener('endStroke', () => {
                signatureDataInput.value = signaturePad.toDataURL('image/png');
            });
            document.getElementById('clearSignature').addEventListener('click', () => {
                signaturePad.clear();
                signatureDataInput.value = ''; 
            });
            document.getElementById('undoSignature').addEventListener('click', () => {
                const data = signaturePad.toData();
                if (data.length) { data.pop(); signaturePad.fromData(data); signatureDataInput.value = signaturePad.toDataURL('image/png'); }
                else { signatureDataInput.value = ''; }
            });

            document.querySelectorAll('input[name="signature_mode"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'draw') {
                        drawSection.classList.remove('hidden');
                        uploadSection.classList.add('hidden');
                        signatureFileInput.value = '';
                    } else {
                        uploadSection.classList.remove('hidden');
                        drawSection.classList.add('hidden');
                        signaturePad.clear();
                        signatureDataInput.value = '';
                    }
                });
            });

            form.addEventListener('submit', function(event) {
                const mode = document.querySelector('input[name="signature_mode"]:checked').value;
                if (mode === 'draw') {
                    if (signaturePad.isEmpty()) {
                        alert('Mohon berikan tanda tangan digital Anda.');
                        event.preventDefault(); return;
                    }
                    signatureDataInput.value = signaturePad.toDataURL('image/png');
                    signatureFileInput.value = '';
                } else if (mode === 'upload') {
                    signatureDataInput.value = '';
                    if (signatureFileInput.files.length === 0) {
                        alert('Mohon unggah file tanda tangan Anda.');
                        event.preventDefault(); return;
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>