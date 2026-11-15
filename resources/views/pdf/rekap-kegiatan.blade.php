<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Kegiatan Biro Humas</title>
    <style>
        @page {
            margin: 20mm 15mm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #1E2616;
            font-size: 10pt;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        .kop {
            text-align: center;
            border-bottom: 2px solid #2F3A1A;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .kop-instansi {
            font-size: 15pt;
            font-weight: bold;
            color: #2F3A1A;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .kop-judul {
            font-size: 11pt;
            font-weight: bold;
            color: #4B5D2A;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .kop-meta {
            font-size: 8.5pt;
            color: #6B7360;
            line-height: 1.5;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th {
            background-color: #F3F6EC;
            color: #2F3A1A;
            font-weight: bold;
            text-align: left;
            padding: 8px 10px;
            border: 1px solid #DDE2D0;
            font-size: 8.5pt;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        td {
            padding: 7px 10px;
            border: 1px solid #DDE2D0;
            font-size: 8.5pt;
            vertical-align: top;
        }

        tr:nth-child(even) td {
            background-color: #FAFCF7;
        }

        .text-center {
            text-align: center;
        }

        .text-bold {
            font-weight: bold;
        }

        .footer {
            margin-top: 24px;
            text-align: right;
            font-size: 8pt;
            color: #6B7360;
        }
    </style>
</head>
<body>
    {{-- KOP SURAT / DOKUMEN --}}
    <div class="kop">
        <div class="kop-instansi">Biro Humas</div>
        <div class="kop-judul">Rekap Kegiatan Biro Humas</div>
        <div class="kop-meta">
            <strong>Periode:</strong> {{ $periodeText }} &nbsp;|&nbsp; 
            <strong>Tanggal Cetak:</strong> {{ $tanggalCetak }}
        </div>
    </div>

    {{-- TABEL DATA KEGIATAN --}}
    <table>
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 32%;">Judul Kegiatan</th>
                <th style="width: 20%;">Tanggal & Waktu</th>
                <th style="width: 23%;">Tempat</th>
                <th style="width: 20%;">PIC</th>
            </tr>
        </thead>
        <tbody>
            @foreach($kegiatans as $index => $kegiatan)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-bold">{{ $kegiatan->judul }}</td>
                    <td>
                        {{ \Carbon\Carbon::parse($kegiatan->tanggal, 'Asia/Jakarta')->translatedFormat('d M Y') }}
                        <br>
                        <span style="color: #6B7360; font-size: 8pt;">{{ $kegiatan->waktu_formatted }}</span>
                    </td>
                    <td>{{ $kegiatan->tempat }}</td>
                    <td>
                        @if($kegiatan->pics->isNotEmpty())
                            {{ $kegiatan->pics->pluck('name')->join(', ') }}
                        @else
                            <span style="color: #6B7360; font-style: italic;">-</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak secara otomatis dari Sistem Biro Humas
    </div>
</body>
</html>
