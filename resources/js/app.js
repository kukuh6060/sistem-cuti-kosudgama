import './bootstrap';

import Alpine from 'alpinejs';
import SignaturePad from 'signature_pad'; // Tambahkan ini

window.Alpine = Alpine;
window.SignaturePad = SignaturePad; // Tambahkan ini

Alpine.start();

// Anda bisa menambahkan logika inisialisasi signature pad di sini jika mau
// Atau lebih baik langsung di blade file dengan window.SignaturePad