<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permohonan Pembelian Barang - {{ $data->requisition_number ?? $data->user->name }}</title>
    <style>
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
        table.info tr td { padding: 2px 0; vertical-align: top; }
        table.info tr td:first-child { width: 100px; }
        table.info tr td:nth-child(2) { width: 15px; text-align: center; }
        table.items { margin-top: 20px; border: 1px solid #000; }
        table.items th, table.items td { border: 1px solid #000; padding: 8px; text-align: left; }
        table.items th { background-color: #f0f0f0; font-size: 11pt; }
        table.items td { font-size: 11pt; }
        
        /* === PERBAIKAN JARAK DI SINI === */
        .signature-area {
            margin-top: 80px; /* Diubah dari 50px ke 80px */
            width: 100%;
            display: table;
        }
        /* ============================== */
        
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
        <h2 class="title">PERMOHONAN PEMBELIAN BARANG</h2>
        <table class="info">
            <tbody>
                <tr>
                    <td>No</td>
                    <td>:</td>
                    <td>{{ $data->requisition_number ?? '..............................' }}</td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>:</td>
                    <td>{{ $data->requisition_date->isoFormat('D MMMM YYYY') }}</td>
                </tr>
            </tbody>
        </table>
        <table class="items">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Barang</th>
                    <th>Jumlah Barang</th>
                    <th>Perkiraan Harga</th>
                    <th>Harga</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data->items as $item)
                <tr>
                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td style="text-align: center;">{{ $item->jumlah_barang }}</td>
                    <td style="text-align: right;">Rp {{ number_format($item->perkiraan_harga, 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td>{{ $item->keterangan ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center;">Tidak ada barang.</td>
                </tr>
                @endforelse
                @for ($i = $data->items->count(); $i < 5; $i++)
                <tr>
                    <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td>
                </tr>
                @endfor
            </tbody>
        </table>
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
                Kanit/Kabag
                <div class="signature-content">
                    @if($data->signature_path_unit)
                        <img src="{{ asset('storage/' . $data->signature_path_unit) }}" alt="TTD Kepala Unit">
                    @endif
                </div>
                <div class="signature-name">
                    ( {{ $data->approverUnit->name ?? '..................' }} )
                </div>
            </div>
            <div class="signature-box">
                Yogyakarta, {{ $data->created_at->isoFormat('D MMMM YYYY') }}<br>
                Pemohon,
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
    </div>

    <div class="print-button">
        <button onclick="window.print()">Cetak Halaman Ini</button>
    </div>

</body>
</html>