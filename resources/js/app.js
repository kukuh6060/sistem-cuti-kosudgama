import './bootstrap';

import Alpine from 'alpinejs';
import SignaturePad from 'signature_pad'; // Tambahkan ini

window.Alpine = Alpine;
window.SignaturePad = SignaturePad; // Tambahkan ini

Alpine.start();

/*
|--------------------------------------------------------------------------
| Responsive Tables
|--------------------------------------------------------------------------
| Membungkus semua tabel yang belum memiliki pembungkus responsif.
*/

function enableResponsiveTables() {
    document.querySelectorAll('table').forEach((table) => {
        // Jangan membungkus tabel yang sudah responsif.
        if (table.closest('.table-responsive')) {
            return;
        }

        // Memungkinkan tabel tertentu dikecualikan bila diperlukan.
        if (table.closest('[data-no-table-scroll]')) {
            return;
        }

        const wrapper = document.createElement('div');

        wrapper.className = 'table-responsive';
        wrapper.setAttribute('role', 'region');
        wrapper.setAttribute('tabindex', '0');
        wrapper.setAttribute(
            'aria-label',
            'Tabel. Geser ke samping untuk melihat seluruh kolom.'
        );

        table.parentNode.insertBefore(wrapper, table);
        wrapper.appendChild(table);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        enableResponsiveTables
    );
} else {
    enableResponsiveTables();
}
// Anda bisa menambahkan logika inisialisasi signature pad di sini jika mau
// Atau lebih baik langsung di blade file dengan window.SignaturePad