<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Pengajuan Cuti atau Ijin - {{ $data->user->name }}</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: "Times New Roman", serif;
            font-size: 10pt;
            line-height: 1.25;
            color: #000;
            background: #eee;
            margin: 0;
        }

        .container {
            width: 190mm;
            min-height: 277mm;
            margin: 15px auto;
            padding: 5mm;
            background: #fff;
        }

        .header {
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 3px double #000;
            padding-bottom: 7px;
            margin-bottom: 10px;
        }

        .logo img {
            width: 65px;
            height: auto;
            object-fit: contain;
        }

        .header-text {
            flex: 1;
            text-align: center;
        }

        .header-text h1 {
            font-size: 12pt;
            margin: 0 0 2px;
        }

        .header-text p {
            font-size: 8pt;
            margin: 3px 0 0;
        }

        .form-number {
            display: flex;
            justify-content: space-between;
            font-size: 9pt;
            margin: 5px 0;
        }

        .form-title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            margin: 10px 0 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
            padding: 3px 5px;
            vertical-align: top;
        }

        .identity td {
            padding: 3px 2px;
        }

        .identity td:first-child {
            width: 175px;
        }

        .identity td:nth-child(2) {
            width: 12px;
            text-align: center;
        }

        .section-title {
            font-weight: bold;
            margin: 10px 0 4px;
        }

        .checkbox {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid #000;
            text-align: center;
            line-height: 10px;
            font-size: 10pt;
            vertical-align: -1px;
            margin-right: 4px;
        }

        .choice-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 22px;
            margin: 5px 0;
        }

        .small-note {
            font-size: 8pt;
            font-style: italic;
        }

        .purpose-box {
            border: 1px solid #000;
            min-height: 35px;
            padding: 6px;
            white-space: pre-line;
        }

        .absensi {
            margin-top: 9px;
        }

        .absensi th,
        .absensi td {
            padding: 4px 1px;
            
        }

        .absensi .ket {
            width: 150px;
        }

        .absensi th {
            text-align: left;
            font-weight: bold;
        }

        .absensi .number {
            
            text-align: left;
        }

        .approval-title {
            font-weight: bold;
            text-align: center;
            margin: 10px 0 5px;
        }

        .approval-table {
            table-layout: fixed;
        }

        .approval-table th,
        .approval-table td {
            border: 1px solid #000;
            text-align: center;
            padding: 4px;
        }

        .approval-table th {
            font-weight: bold;
        }

        .decision {
            display: flex;
            justify-content: center;
            gap: 7px;
            font-size: 8pt;
            margin-top: 5px;
        }

        .signature {
            height: 42px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .signature img {
            max-width: 110px;
            max-height: 40px;
            object-fit: contain;
        }

        .signer-name {
            border-top: 1px solid #000;
            padding-top: 3px;
            min-height: 18px;
            overflow-wrap: anywhere;
        }

        .applicant {
            width: 40%;
            margin: 9px 0 0 auto;
            text-align: center;
        }

        .applicant .signature {
            height: 42px;
        }

        .notes {
            border: 1px solid #000;
            padding: 6px 8px;
            margin-top: 8px;
            font-size: 8pt;
        }

        .notes p {
            margin: 2px 0;
        }

        .print-button {
            text-align: center;
            padding: 12px;
        }

        .print-button button {
            padding: 8px 18px;
            background: #333;
            color: #fff;
            border: 0;
            border-radius: 4px;
            cursor: pointer;
        }

        @media print {
            body {
                background: #fff;
                font-size: 9pt;
            }

            .container {
                width: 100%;
                min-height: 0;
                margin: 0;
                padding: 0;
            }

            .print-button {
                display: none;
            }

            .absensi,
            .approval-table,
            .notes,
            .purpose-box {
                break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    @php
    $status = $data->status instanceof \App\Enums\LeaveStatus
    ? $data->status->value
    : $data->status;

    // Keputusan ditentukan berdasarkan tanda tangan dan approver
    // agar penolakan di setiap tahap dapat dibedakan.
    $unitApproved = !empty($data->signature_path_unit);
    $sdmApproved = !empty($data->signature_path_sdm);
    $pengurusApproved = !empty($data->signature_path_pengurus);

    $unitRejected = $status === 'rejected'
    && !empty($data->approved_by_head_id)
    && !$unitApproved;

    $sdmRejected = $status === 'rejected'
    && !empty($data->approved_by_hr_id)
    && !$sdmApproved;

    $pengurusRejected = $status === 'rejected'
    && !empty($data->approved_by_pengurus_id)
    && !$pengurusApproved;

    $unitName = $data->user->unit
    ? ucwords(str_replace('_', ' ', $data->user->unit->value))
    : '-';

    $tahunPengajuan = $data->created_at->format('Y');

    $formatTanggal = fn ($tanggal) => $tanggal
    ? \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('D MMMM YYYY')
    : '-';

    $nilaiAbsensi = fn ($field) => $absensi && $absensi->$field !== null
    ? $absensi->$field
    : '-';
    @endphp

    <div class="container">
        <div class="header">
            <div class="logo">
                <img src="{{ asset('images/kosudgama_logo.png') }}" alt="Logo Kosudgama">
            </div>

            <div class="header-text">
                <h1>KOPERASI KONSUMEN</h1>
                <h1>KOSUDGAMA DAYA GEMILANG</h1>
                <h1>YOGYAKARTA</h1>
                <p>
                    Jl. Monjali No.153, Karangjati, Sinduadi, Kec. Mlati,
                    Kabupaten Sleman, Daerah Istimewa Yogyakarta 55284
                </p>
            </div>
        </div>

        <div class="form-number">
            <span>No: 001/UmumSDM-KSG/{{ $tahunPengajuan }}</span>
        </div>

        <div class="form-title">
            FORMULIR APLIKASI PENGAJUAN CUTI ATAU IJIN KARYAWAN
        </div>

        <table class="identity">
            <tbody>
                <tr>
                    <td>Nama Karyawan</td>
                    <td>:</td>
                    <td>{{ $data->user->name }}</td>
                </tr>
                <tr>
                    <td>Unit Kerja</td>
                    <td>:</td>
                    <td>{{ $unitName }}</td>
                </tr>
                <tr>
                    <td>Tgl Pengajuan Cuti atau Ijin</td>
                    <td>:</td>
                    <td>{{ $formatTanggal($data->created_at) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="section-title">
            Mengajukan:
            <span class="small-note">*centang salah satu</span>
        </div>

        <div class="choice-row">
            <span>
                <span class="checkbox">
                    @if($data->leave_type === 'cuti') ✓ @endif
                </span>
                Cuti
            </span>

            <span>
                <span class="checkbox">
                    @if($data->leave_type === 'ijin') ✓ @endif
                </span>
                Ijin tidak masuk bekerja
            </span>
        </div>

        <table class="identity">
            <tbody>
                <tr>
                    <td>Lama Pengajuan</td>
                    <td>:</td>
                    <td>
                        @foreach([1, 2, 3] as $hari)
                        <span style="margin-right: 12px; white-space: nowrap;">
                            <span class="checkbox">
                                @if((int) $data->total_days === $hari) ✓ @endif
                            </span>
                            {{ $hari }} hari
                        </span>
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <td>Hari &amp; Tanggal</td>
                    <td>:</td>
                    <td>
                        {{ $formatTanggal($data->start_date) }}
                        s.d. {{ $formatTanggal($data->end_date) }}
                    </td>
                </tr>
                <tr>
                    <td>Untuk Kepentingan</td>
                    <td>:</td>
                    <td>
                        <div class="purpose-box">{{ $data->reason ?: '-' }}</div>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="applicant">
            Yogyakarta, {{ $formatTanggal($data->created_at) }}<br>
            Yang mengajukan cuti/ijin,

            <div class="signature">
                @if($data->signature_path)
                <img
                    src="{{ asset('storage/' . $data->signature_path) }}"
                    alt="Tanda tangan pemohon">
                @endif
            </div>

            <div class="signer-name">
                {{ $data->user->name }}
            </div>
        </div>

        <div class="section-title">DATA ABSENSI</div>
        <div class="small-note">
            Diisi oleh Umum &amp; SDM. Rekap terhitung tanggal 21 bulan sebelumnya
            s.d. tanggal pengajuan cuti.
        </div>

        <table class="absensi">
            <tbody>
                <tr>
                    <td colspan="3"><b>Tidak masuk bekerja dengan alasan</b></td>
                    <td colspan="3" style="text-align: left;"> <b>Masuk bekerja tidak penuh dengan alasan</b></td>
                </tr>
                <tr>
                    <td class="ket">• Sakit</td>
                    <td>:</td>
                    <td class="number">{{ $nilaiAbsensi('sakit') }} hari</td>
                    <td class="ket">• Terlambat</td>
                    <td>:</td>
                    <td class="number">{{ $nilaiAbsensi('terlambat') }} hari</td>
                </tr>
                <tr>
                    <td class="ket">• Tanpa kabar</td>
                    <td>:</td>
                    <td class="number">{{ $nilaiAbsensi('tanpa_kabar') }} hari</td>
                    <td class="ket">• ½ hari kerja</td>
                    <td>:</td>
                    <td class="number">{{ $nilaiAbsensi('setengah_hari') }} hari</td>
                </tr>
                <tr>
                    <td colspan="3"><b>Ijin meninggalkan tempat</b></td>
                    <td colspan="3" style="text-align: left;"><b>Sisa cuti atau ijin yang belum diambil</b></td>
                </tr>
                <tr>
                    <td class="ket">• Kurang dari 1 jam</td>
                    <td>:</td>
                    <td class="number">{{ $nilaiAbsensi('ijin_kurang_1_jam') }} hari</td>
                    <td class="ket">• Cuti</td>
                    <td>:</td>
                    <td class="number">{{ $sisaCuti ?? '-' }} hari</td>
                </tr>
                <tr>
                    <td class="ket">• Lebih dari 1 jam</td>
                    <td>:</td>
                    <td class="number">{{ $nilaiAbsensi('ijin_lebih_1_jam') }} hari</td>
                    <td class="ket">• Ijin</td>
                    <td>:</td>
                    <td class="number">{{ $sisaIzin ?? '-' }} hari</td>
                </tr>
                <tr>
                    <td class="ket">• Pulang awal</td>
                    <td>:</td>
                    <td class="number">{{ $nilaiAbsensi('pulang_awal') }} hari</td>
                </tr>
            </tbody>
        </table>

        <div class="approval-title">PERSETUJUAN</div>

        <table class="approval-table">
            <thead>
                <tr>
                    <th>
                        Kepala/Wakil Kepala<br>Unit Kerja
                    </th>
                    <th>
                        Kabag./Wakabag<br>Umum &amp; SDM
                    </th>
                    <th>
                        Penanggung Jawab<br>SDM/Pengurus
                    </th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>
                        <div class="decision">
                            <span>
                                <span class="checkbox">
                                    @if($unitApproved) ✓ @endif
                                </span>
                                Disetujui
                            </span>
                            <span>
                                <span class="checkbox">
                                    @if($unitRejected) ✓ @endif
                                </span>
                                Tidak disetujui
                            </span>
                        </div>

                        <div class="signature">
                            @if($data->signature_path_unit)
                            <img
                                src="{{ asset('storage/' . $data->signature_path_unit) }}"
                                alt="Tanda tangan Kepala Unit">
                            @endif
                        </div>

                        <div class="signer-name">
                            {{ $data->approverUnit->name ?? '........................' }}
                        </div>
                    </td>

                    <td>
                        <div class="decision">
                            <span>
                                <span class="checkbox">
                                    @if($sdmApproved) ✓ @endif
                                </span>
                                Disetujui
                            </span>
                            <span>
                                <span class="checkbox">
                                    @if($sdmRejected) ✓ @endif
                                </span>
                                Tidak disetujui
                            </span>
                        </div>

                        <div class="signature">
                            @if($data->signature_path_sdm)
                            <img
                                src="{{ asset('storage/' . $data->signature_path_sdm) }}"
                                alt="Tanda tangan SDM">
                            @endif
                        </div>

                        <div class="signer-name">
                            {{ $data->approverSdm->name ?? '........................' }}
                        </div>
                    </td>

                    <td>
                        <div class="decision">
                            <span>
                                <span class="checkbox">
                                    @if($pengurusApproved) ✓ @endif
                                </span>
                                Disetujui
                            </span>
                            <span>
                                <span class="checkbox">
                                    @if($pengurusRejected) ✓ @endif
                                </span>
                                Tidak disetujui
                            </span>
                        </div>

                        <div class="signature">
                            @if($data->signature_path_pengurus)
                            <img
                                src="{{ asset('storage/' . $data->signature_path_pengurus) }}"
                                alt="Tanda tangan Pengurus">
                            @endif
                        </div>

                        <div class="signer-name">
                            {{ $data->approverPengurus->name ?? '........................' }}
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        

        <div class="notes">
            <strong>Catatan penting:</strong>
            <p>1. Masih memiliki hak; cuti tahunan 12 (dua belas) hari dan dapat diambil maksimal 3 (tiga) hari dalam 1 bulan, izin tidak masuk bekerja 3 (tiga) hari.</p>
            <p>2. Izin tidak masuk bekerja dapat diajukan setelah hak cuti habis.</p>
            <p>3. Maksimal pengajuan seminggu sebelumnya pelaksanaan, kecuali untuk kepentingan tertentu yang sifatnya tidak dapat direncanakan.</p>
            <p>4. Mendapatkan persetujuan masing-masing dari kepala/Wakil Kepala Unit Kerja, Kepala Bagian/Wakil Kepala Bag. Umum & SDM dan penanggung jawab SDM/Pengurus.</p>
        </div>
    </div>

    <div class="print-button">
        <button type="button" onclick="window.print()">Cetak Formulir</button>
    </div>
</body>

</html>