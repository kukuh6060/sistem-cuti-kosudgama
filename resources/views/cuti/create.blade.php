<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Formulir Pengajuan Cuti / Ijin Karyawan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900">
                    
                    <x-auth-session-status class="mb-4" :status="session('status')" />
                    <x-input-error :messages="$errors->get('error')" class="mb-4 p-4 bg-red-100 text-red-800 rounded-md" />
                    <x-input-error :messages="$errors->get('signature_mode')" class="mb-4 p-4 bg-red-100 text-red-800 rounded-md" />
                    <x-input-error :messages="$errors->get('signature_data')" class="mb-4 p-4 bg-red-100 text-red-800 rounded-md" />
                    <x-input-error :messages="$errors->get('signature_file')" class="mb-4 p-4 bg-red-100 text-red-800 rounded-md" />
                    
                    <form method="POST" action="{{ route('cuti.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div>
                            <x-input-label for="name" :value="__('Nama Karyawan')" />
                            <x-text-input id="name" class="block mt-1 w-full bg-gray-100" type="text" :value="Auth::user()->name" readonly disabled />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="unit_kerja" :value="__('Unit Kerja')" />
                            <x-text-input id="unit_kerja" class="block mt-1 w-full bg-gray-100" type="text" :value="ucwords(str_replace('_', ' ', Auth::user()->unit?->value ?? 'N/A'))" readonly disabled />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="tgl_pengajuan" :value="__('Tgl Pengajuan Cuti atau Ijin')" />
                            <x-text-input id="tgl_pengajuan" class="block mt-1 w-full bg-gray-100" type="text" :value="now()->format('d F Y')" readonly disabled />
                        </div>
                        <div class="mt-4">
                            <x-input-label :value="__('Mengajukan (centang salah satu)')" class="font-medium" />
                            <div class="mt-2 space-y-2 sm:space-y-0 sm:flex sm:space-x-6">
                                <label for="type_cuti" class="flex items-center cursor-pointer">
                                    <input id="type_cuti" type="radio" name="leave_type" value="cuti" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500" {{ old('leave_type') == 'cuti' ? 'checked' : '' }} required>
                                    <span class="ms-2 text-sm text-gray-700">Cuti</span>
                                </label>
                                <label for="type_ijin" class="flex items-center cursor-pointer">
                                    <input id="type_ijin" type="radio" name="leave_type" value="ijin" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500" {{ old('leave_type') == 'ijin' ? 'checked' : '' }} required>
                                    <span class="ms-2 text-sm text-gray-700">Ijin tidak masuk kerja</span>
                                </label>
                            </div>
                            <x-input-error :messages="$errors->get('leave_type')" class="mt-2" />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="start_date" :value="__('Hari & Tanggal Cuti (Mulai)')" />
                            <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date')" required />
                            <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="end_date" :value="__('Hari & Tanggal Cuti (Selesai)')" />
                            <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="old('end_date')" required />
                            <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="reason" :value="__('Untuk Kepentingan')" />
                            <textarea id="reason" name="reason" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>{{ old('reason') }}</textarea>
                            <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                        </div>

                        <div class="mt-6 border-t pt-6">
                            <h3 class="font-medium text-lg mb-4">{{ __('Yang Mengajukan Cuti (Tanda Tangan)') }}</h3>
                            <div class="mb-4 space-y-2">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="signature_mode" value="draw" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500" checked>
                                    <span class="ms-2 text-sm text-gray-700">Gambar Tanda Tangan Digital</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="signature_mode" value="upload" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
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
                            <x-input-label :value="__('Nama Terang')" class="mt-4" />
                            <x-text-input class="block mt-1 w-full bg-gray-100" type="text" :value="Auth::user()->name" readonly disabled />
                            <p class="mt-2 text-xs text-gray-500">
                                *Dengan menekan tombol "Kirim Pengajuan", Anda dianggap telah menandatangani formulir ini secara digital.
                            </p>
                        </div>
                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                Batal
                            </a>
                            <x-primary-button>
                                {{ __('Kirim Pengajuan') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script>
        // 'load' akan menunggu semua skrip (termasuk CDN) selesai dimuat
        window.addEventListener('load', function() {
            const canvas = document.getElementById('signaturePad');
            if (!canvas) return; // Hentikan jika elemen tidak ada

            const signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)'
            });
            const signatureDataInput = document.getElementById('signatureData');
            const signatureFileInput = document.getElementById('signature_file');
            const form = document.querySelector('form');
            const drawSection = document.getElementById('drawSignatureSection');
            const uploadSection = document.getElementById('uploadSignatureSection');

            // Fungsi untuk resize canvas
            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext('2d').scale(ratio, ratio);
                signaturePad.clear(); // Hapus ttd saat resize
                signatureDataInput.value = ''; // Kosongkan data
            }
            window.addEventListener('resize', resizeCanvas);
            resizeCanvas();

            
            // ==========================================================
            // LOGIKA BARU: Simpan data setiap kali selesai menggambar
            // ==========================================================
            signaturePad.addEventListener('endStroke', () => {
                signatureDataInput.value = signaturePad.toDataURL('image/png');
            });

            // Tombol Clear: Hapus ttd dan data di input
            document.getElementById('clearSignature').addEventListener('click', () => {
                signaturePad.clear();
                signatureDataInput.value = ''; 
            });

            // Tombol Undo: Hapus goresan terakhir dan update data di input
            document.getElementById('undoSignature').addEventListener('click', () => {
                const data = signaturePad.toData();
                if (data.length) {
                    data.pop(); // Hapus goresan terakhir
                    signaturePad.fromData(data);
                    signatureDataInput.value = signaturePad.toDataURL('image/png');
                } else {
                    signatureDataInput.value = ''; // Jika sudah kosong
                }
            });
            // ==========================================================


            // Logika untuk beralih mode (Draw / Upload)
            document.querySelectorAll('input[name="signature_mode"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'draw') {
                        drawSection.classList.remove('hidden');
                        uploadSection.classList.add('hidden');
                        signatureFileInput.value = ''; // Kosongkan file input
                    } else {
                        uploadSection.classList.remove('hidden');
                        drawSection.classList.add('hidden');
                        signaturePad.clear(); // Hapus gambar di pad
                        signatureDataInput.value = ''; // Kosongkan data ttd
                    }
                });
            });

            // Logika saat menekan tombol "Kirim Pengajuan"
            // (Sekarang hanya untuk validasi sisi klien, bukan untuk set data)
            form.addEventListener('submit', function(event) {
                const mode = document.querySelector('input[name="signature_mode"]:checked').value;

                if (mode === 'draw') {
                    // Cek apakah data di hidden input (yang diisi 'endStroke') sudah ada
                    if (signaturePad.isEmpty() || signatureDataInput.value === '') {
                        alert('Mohon berikan tanda tangan digital Anda di kotak yang tersedia.');
                        event.preventDefault(); // Hentikan submit
                        return;
                    }
                    // Kosongkan file input agar tidak konflik
                    signatureFileInput.value = '';

                } else if (mode === 'upload') {
                    // Kosongkan data gambar agar tidak konflik
                    signatureDataInput.value = '';
                    if (signatureFileInput.files.length === 0) {
                        alert('Mohon unggah file gambar tanda tangan Anda (PNG).');
                        event.preventDefault(); // Hentikan submit
                        return;
                    }
                }
            });
        });
    </script>
    @endpush
    </x-app-layout>