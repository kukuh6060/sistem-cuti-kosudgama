<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Formulir Izin Meninggalkan Tempat Kerja') }}
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
                    
                    <form method="POST" action="{{ route('partial-leave.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div>
                            <x-input-label for="name" :value="__('Nama Karyawan')" />
                            <x-text-input id="name" class="block mt-1 w-full bg-gray-100" type="text" :value="Auth::user()->name" readonly disabled />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="unit_kerja" :value="__('Unit Kerja')" />
                            <x-text-input id="unit_kerja" class="block mt-1 w-full bg-gray-100" type="text" :value="ucwords(str_replace('_', ' ', Auth::user()->unit?->value ?? 'N/A'))" readonly disabled />
                        </div>
                        
                        <p class="mt-6 text-sm text-gray-800">Bermaksud mengajukan izin untuk meninggalkan tempat kerja pada:</p>

                        <div class="mt-4">
                            <x-input-label for="leave_date" :value="__('Hari, Tanggal')" />
                            <x-text-input id="leave_date" class="block mt-1 w-full" type="date" name="leave_date" :value="old('leave_date')" required />
                            <x-input-error :messages="$errors->get('leave_date')" class="mt-2" />
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="start_time" :value="__('Jam Mulai (s.d.)')" />
                                <x-text-input id="start_time" class="block mt-1 w-full" type="time" name="start_time" :value="old('start_time')" required />
                                <x-input-error :messages="$errors->get('start_time')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="end_time" :value="__('Jam Selesai')" />
                                <x-text-input id="end_time" class="block mt-1 w-full" type="time" name="end_time" :value="old('end_time')" required />
                                <x-input-error :messages="$errors->get('end_time')" class="mt-2" />
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <x-input-label :value="__('Untuk Keperluan (pilih salah satu)')" class="font-medium" />
                            <div class="mt-2 space-y-2">
                                @php
                                    $keperluan = [
                                        'melayat' => 'Melayat',
                                        'mengurus_orang_tua' => 'Mengurus orang tua',
                                        'keperluan_sekolah_anak' => 'Mengurus keperluan sekolah anak',
                                        'berobat' => 'Berobat',
                                        'lain' => 'Keperluan pribadi lain...',
                                    ];
                                @endphp
                                @foreach ($keperluan as $value => $label)
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="purpose_category" value="{{ $value }}" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500" {{ old('purpose_category') == $value ? 'checked' : '' }} required>
                                    <span class="ms-2 text-sm text-gray-700">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors->get('purpose_category')" class="mt-2" />
                        </div>

                        <div id="purpose_other_container" class="mt-2 hidden">
                            <x-input-label for="purpose_other" :value="__('Sebutkan keperluan pribadi lainnya')" />
                            <textarea id="purpose_other" name="purpose_other" rows="2" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('purpose_other') }}</textarea>
                            <x-input-error :messages="$errors->get('purpose_other')" class="mt-2" />
                        </div>


                        <div class="mt-6 border-t pt-6">
                            <h3 class="font-medium text-lg mb-4">{{ __('Yang Mengajukan (Tanda Tangan)') }}</h3>
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
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                Batal
                            </a>
                            <x-primary-button>
                                {{ __('Kirim Pengajuan Izin') }}
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
            // --- LOGIKA TANDA TANGAN (SAMA SEPERTI SEBELUMNYA) ---
            const canvas = document.getElementById('signaturePad');
            if (!canvas) return; 

            const signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)'
            });
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
                if (data.length) {
                    data.pop();
                    signaturePad.fromData(data);
                    signatureDataInput.value = signaturePad.toDataURL('image/png');
                } else {
                    signatureDataInput.value = '';
                }
            });

            // --- LOGIKA FORM BARU ---
            const purposeRadios = document.querySelectorAll('input[name="purpose_category"]');
            const otherPurposeContainer = document.getElementById('purpose_other_container');

            function toggleOtherPurpose(value) {
                if (value === 'lain') {
                    otherPurposeContainer.classList.remove('hidden');
                } else {
                    otherPurposeContainer.classList.add('hidden');
                }
            }
            
            // Cek saat load
            const currentPurpose = document.querySelector('input[name="purpose_category"]:checked');
            if (currentPurpose) {
                toggleOtherPurpose(currentPurpose.value);
            }
            
            // Cek saat ganti
            purposeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    toggleOtherPurpose(this.value);
                });
            });


            // Logika Submit Form
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
                    if (signaturePad.isEmpty() || signatureDataInput.value === '') {
                        alert('Mohon berikan tanda tangan digital Anda di kotak yang tersedia.');
                        event.preventDefault();
                        return;
                    }
                    signatureFileInput.value = '';
                } else if (mode === 'upload') {
                    signatureDataInput.value = '';
                    if (signatureFileInput.files.length === 0) {
                        alert('Mohon unggah file gambar tanda tangan Anda (PNG).');
                        event.preventDefault();
                        return;
                    }
                }
            });
        });
    </script>
    @endpush
    </x-app-layout>