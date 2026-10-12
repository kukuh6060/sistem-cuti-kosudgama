<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    /**
     * Menampilkan halaman untuk mengedit catatan penting.
     */
    public function edit()
    {
        // Cari catatan di DB, atau buat baru jika belum ada
        $catatan = Setting::firstOrCreate(
            ['key' => 'catatan_penting_karyawan'],
            ['value' => ''] // Default value jika belum ada
        );
        
        return view('settings.edit_notes', compact('catatan'));
    }

    /**
     * Simpan perubahan catatan penting.
     */
    public function update(Request $request)
    {
        $request->validate(['catatan_karyawan' => 'required|string']);

        Setting::updateOrCreate(
            ['key' => 'catatan_penting_karyawan'],
            ['value' => $request->catatan_karyawan]
        );

        return redirect()->route('settings.notes.edit')->with('success', 'Catatan penting berhasil diperbarui!');
    }
}