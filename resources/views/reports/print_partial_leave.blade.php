<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Izin Meninggalkan Tempat Kerja - {{ $data->user->name }}</title>
    <style>
        /* ... (Semua CSS Anda dari sebelumnya biarkan apa adanya) ... */
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
        }

        .container {
            width: 90%;
            max-width: 800px;
            margin: 20px auto;
            background-color: #fff;
            padding: 1.5cm;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            display: flex;
            align-items: center;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header .logo {
            flex-shrink: 0;
            margin-right: 15px;
        }

        .header .logo img {
            height: 70px;
            width: auto;
        }

        .header .text-content {
            flex-grow: 1;
            text-align: center;
        }

        .header h1 {
            font-size: 14pt;
            font-weight: bold;
            margin: 0;
        }

        .header p {
            font-size: 11pt;
            margin: 2px 0;
        }

        .title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 20px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table tr td {
            padding: 5px 0;
            vertical-align: top;
        }

        table tr td:first-child {
            width: 150px;
        }

        table tr td:nth-child(2) {
            width: 15px;
            text-align: center;
        }

        .checkbox-label {
            display: block;
            margin-bottom: 5px;
        }

        .checkbox {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 1px solid #000;
            margin-right: 10px;
            vertical-align: middle;
            text-align: center;
            line-height: 16px;
        }

        .signature-area {
            margin-top: 30px;
            width: 100%;
            display: table;
        }

        .signature-box {
            display: table-cell;
            width: 33.3%;
            text-align: center;
            vertical-align: top;
            position: relative;
            height: 120px;
        }

        .signature-content {
            height: 60px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .signature-content img {
            max-height: 60px;
            max-width: 150px;
            object-fit: contain;
        }

        .signature-name {
            border-top: 1px solid #000;
            width: 80%;
            max-width: 200px;
            margin-left: auto;
            margin-right: auto;
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
        }

        .print-button {
            width: 100%;
            text-align: center;
            padding: 15px 0;
            background-color: #f0f0f0;
            border-top: 1px solid #ccc;
            margin-top: 20px;
        }

        .print-button button {
            padding: 8px 16px;
            font-size: 14px;
            background-color: #4a4a4a;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .print-button button:hover {
            background-color: #6a6a6a;
        }

        @media print {
            .print-button {
                display: none;
            }

            body {
                background-color: #fff;
            }

            .container {
                margin: 0;
                padding: 0;
                width: 100%;
                max-width: 100%;
                box-shadow: none;
                border: none;
            }
        }
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
        <h2 class="title">SURAT IZIN MENINGGALKAN TEMPAT KERJA</h2>
        <p>Saya yang bertanda tangan di bawah ini:</p>
        <table>
            <tbody>
                <tr>
                    <td>Nama</td>
                    <td>:</td>
                    <td>{{ $data->user->name }}</td>
                </tr>
                <tr>
                    <td>Unit kerja</td>
                    <td>:</td>
                    <td>{{ $data->user->unit ? ucwords(str_replace('_', ' ', $data->user->unit->value)) : 'N/A' }}</td>
                </tr>
            </tbody>
        </table>
        <p>bermaksud mengajukan izin untuk meninggalkan tempat kerja pada:</p>
        <table>
            <tbody>
                <tr>
                    <td>Hari, tanggal</td>
                    <td>:</td>
                    <td>{{ $data->leave_date->isoFormat('dddd, D MMMM YYYY') }}</td>
                </tr>
                <tr>
                    <td>Jam</td>
                    <td>:</td>
                    <td>{{ $data->start_time->format('H:i') }} s.d. {{ $data->end_time->format('H:i') }}</td>
                </tr>
            </tbody>
        </table>
        <p class="mt-4">Untuk keperluan: <span style="font-size: 10pt; font-style: italic;">*pilih salah satu</span></p>
        @php
        $keperluan = [
        'melayat' => 'Melayat',
        'mengurus_orang_tua' => 'Mengurus orang tua',
        'keperluan_sekolah_anak' => 'Mengurus keperluan sekolah anak',
        'berobat' => 'Berobat',
        'lain' => 'Keperluan pribadi lain',
        ];
        @endphp
        @foreach ($keperluan as $value => $label)
        <div class="checkbox-label">
            <span class="checkbox">
                @if($data->purpose_category == $value)
                &#10003; @endif
            </span>
            <span>{{ $label }}</span>
            @if($value == 'lain' && $data->purpose_category == 'lain')
            : <strong>{{ $data->purpose_other }}</strong>
            @endif
        </div>
        @endforeach
        <p style="margin-top: 20px;">
            Demikian izin ini diajukan sesuai dengan keadaan sesungguhnya untuk dapat diadministrasikan
            dalam catatan presensi.
        </p>

        <!-- === BLOK TANDA TANGAN === -->
        <div class="signature-area">
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
                Kepala Unit
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
                Yg mengajukan,
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