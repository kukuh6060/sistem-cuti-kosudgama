<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Pengajuan Cuti - {{ $data->user->name }}</title>
    <style>
        /* ... (Semua CSS Anda dari sebelumnya biarkan apa adanya) ... */
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.6; margin: 0; padding: 0; background-color: #f0f0f0; }
        .container { width: 90%; max-width: 800px; margin: 20px auto; background-color: #fff; padding: 1.5cm; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { display: flex; align-items: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header .logo { flex-shrink: 0; margin-right: 15px; }
        .header .logo img { height: 70px; width: auto; }
        .header .text-content { flex-grow: 1; text-align: center; }
        .header h1 { font-size: 14pt; font-weight: bold; margin: 0; }
        .header p { font-size: 11pt; margin: 2px 0; }
        .title { text-align: center; font-size: 14pt; font-weight: bold; text-decoration: underline; margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; }
        table tr td { padding: 5px 0; vertical-align: top; }
        table tr td:first-child { width: 200px; }
        table tr td:nth-child(2) { width: 15px; text-align: center; }
        .checkbox-label { display: inline-block; margin-right: 20px; }
        .checkbox { display: inline-block; width: 16px; height: 16px; border: 1px solid #000; margin-right: 10px; vertical-align: middle; text-align: center; line-height: 16px; }
        .purpose-box { border: 1px solid #000; padding: 10px; min-height: 80px; margin-top: 5px; }
        .signature-area { margin-top: 30px; width: 100%; display: table; }
        .signature-box { display: table-cell; width: 33.3%; text-align: center; vertical-align: top; position: relative; height: 120px; }
        .signature-content { height: 60px; margin-bottom: 10px; display: flex; align-items: center; justify-content: center; }
        .signature-content img { max-height: 60px; max-width: 150px; object-fit: contain; }
        .signature-name { border-top: 1px solid #000; width: 80%; max-width: 200px; margin-left: auto; margin-right: auto; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); }
        .print-button { width: 100%; text-align: center; padding: 15px 0; background-color: #f0f0f0; border-top: 1px solid #ccc; margin-top: 20px; }
        .print-button button { padding: 8px 16px; font-size: 14px; background-color: #4a4a4a; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .print-button button:hover { background-color: #6a6a6a; }
        @media print { .print-button { display: none; } body { background-color: #fff; } .container { margin: 0; padding: 0; width: 100%; max-width: 100%; box-shadow: none; border: none; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <img src="{{ asset('images/kosudgama_logo.png') }}" alt="Logo Koperasi">
            </div>
            <div class="text-content">
                <h1>KOPERASI KONSUMEN</h1>
                <h1>KOSUDGAMA DAYA GEMILANG</h1>
                <h1>YOGYAKARTA</h1>
                <p>Jl. Monjali No.153, Karangjati, Sinduadi, Kec. Mlati, Kabupaten Sleman, Daerah Istimewa Yogyakarta 55284</p>
            </div>
        </div>
        <h2 class="title">FORMULIR APLIKASI PENGAJUAN CUTI ATAU IJIN KARYAWAN</h2>
        <table>
            <tbody>
                <tr>
                    <td>Nama Karyawan</td>
                    <td>:</td>
                    <td>{{ $data->user->name }}</td>
                </tr>
                <tr>
                    <td>Unit Kerja</td>
                    <td>:</td>
                    <td>{{ $data->user->unit ? ucwords(str_replace('_', ' ', $data->user->unit->value)) : 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Tgl Pengajuan Cuti atau Ijin</td>
                    <td>:</td>
                    <td>{{ $data->created_at->isoFormat('dddd, D MMMM YYYY') }}</td>
                </tr>
            </tbody>
        </table>
        <p style="margin-top: 20px;">Mengajukan: <span style="font-size: 10pt; font-style: italic;">*centang salah satu</span></p>
        <div class="checkbox-label">
            <span class="checkbox">@if($data->leave_type == 'cuti') &#10003; @endif</span>
            <span>Cuti</span>
        </div>
        <div class="checkbox-label">
            <span class="checkbox">@if($data->leave_type == 'ijin') &#10003; @endif</span>
            <span>Ijin tidak masuk kerja</span>
        </div>
        <table style="margin-top: 20px;">
            <tbody>
                <tr>
                    <td>Hari & Tanggal Cuti</td>
                    <td>:</td>
                    <td>{{ $data->start_date->isoFormat('D MMMM YYYY') }} s.d. {{ $data->end_date->isoFormat('D MMMM YYYY') }}</td>
                </tr>
                <tr>
                    <td>Total Hari</td>
                    <td>:</td>
                    <td>{{ $data->total_days }} hari</td>
                </tr>
                <tr>
                    <td style="vertical-align: top;">Untuk Kepentingan</td>
                    <td style="vertical-align: top;">:</td>
                    <td>
                        <div class="purpose-box">
                            {{ $data->reason }}
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- === BLOK TANDA TANGAN YANG DIPERBARUI === -->
        <div class="signature-area">
            <div class="signature-box">
                Mengetahui,<br>
                Pengurus
                <div class="signature-content">
                    @if($data->signature_path_pengurus)
                        <img src="{{ asset('storage/' . $data->signature_path_pengurus) }}" alt="TTD Pengurus">
                    @endif
                </div>
                <div class="signature-name">
                    ( {{ $data->approverPengurus->name ?? '..................' }} )
                </div>
            </div>
            <div class="signature-box">
                Mengetahui,<br>
                Bag.Umum & SDM
                <div class="signature-content">
                    @if($data->signature_path_sdm)
                        <img src="{{ asset('storage/' . $data->signature_path_sdm) }}" alt="TTD SDM">
                    @endif
                </div>
                <div class="signature-name">
                    ( {{ $data->approverSdm->name ?? '..................' }} )
                </div>
            </div>
            <div class="signature-box">
                Mengizinkan,<br>
                Unit Kerja
                <div class="signature-content">
                    @if($data->signature_path_unit)
                        <img src="{{ asset('storage/' . $data->signature_path_unit) }}" alt="TTD Kepala Unit">
                    @endif
                </div>
                <div class="signature-name">
                    ( {{ $data->approverUnit->name ?? '..................' }} )
                </div>
            </div>
        </div>
        <div class="signature-area" style="margin-top: 20px; width: 33.3%; margin-left: 66.6%;">
             <div class="signature-box">
                Yogyakarta, {{ $data->created_at->isoFormat('D MMMM YYYY') }}<br>
                Yg mengajukan cuti,
                <div class="signature-content">
                    @if($data->signature_path)
                        <img src="{{ asset('storage/' . $data->signature_path) }}" alt="TTD Pemohon">
                    @endif
                </div>
                <div class="signature-name">
                    ( {{ $data->user->name }} )
                </div>
            </div>
        </div>
        <!-- === AKHIR BLOK TANDA TANGAN === -->

    </div>
    
    <div class="print-button">
        <button onclick="window.print()">Cetak Halaman Ini</button>
    </div>

</body>
</html>