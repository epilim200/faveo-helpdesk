<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: sans-serif; font-size: 11px; color: #000; margin: 20px; }
    h2 { text-align: center; font-size: 14px; margin: 0; padding: 2px 0; }
    h3 { text-align: center; font-size: 12px; margin: 0; padding: 2px 0; font-weight: normal; }
    h4 { font-size: 11px; margin: 5px 0; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    td, th { border: 1px solid #000; padding: 5px 8px; vertical-align: top; font-size: 11px; }
    .section-header { background-color: #d9e2f3; font-weight: bold; text-align: left; }
    .label { width: 30%; font-weight: bold; background-color: #f2f2f2; }
    .no-border { border: none; }
    .checkbox { font-family: DejaVu Sans, sans-serif; }
    .text-center { text-align: center; }
    .thread-body { padding: 5px 8px; }
    .page-break { page-break-before: always; }
</style>
</head>
<body>

<h2>LAPORAN PERUBAHAN/PEMBAIKAN</h2>
<h3>BORANG PERMOHONAN PERUBAHAN/PEMBAIKAN</h3>
<h3>SISTEM/PERISIAN/PERKAKASAN ICT</h3>
<h4 style="text-align:center;">NO. RUJUKAN: {{ $ticket->ticket_number }}</h4>

<br>

<!-- A. MAKLUMAT PERMOHONAN -->
<table>
    <tr>
        <td colspan="4" class="section-header">A. MAKLUMAT PERMOHONAN</td>
    </tr>
    <tr>
        <td class="label">NAMA</td>
        <td colspan="3">{{ $user ? $user->first_name.' '.$user->last_name : '-' }}</td>
    </tr>
    <tr>
        <td class="label">JAWATAN</td>
        <td colspan="3">{{ $user && $user->company ? $user->company : '-' }}</td>
    </tr>
    <tr>
        <td class="label">BAHAGIAN/CAWANGAN</td>
        <td colspan="3">{{ $department ? $department->name : '-' }}</td>
    </tr>
    <tr>
        <td class="label">TARIKH</td>
        <td colspan="3">{{ $ticket->created_at ? date('d/m/Y', strtotime($ticket->created_at)) : '-' }}</td>
    </tr>
</table>

<!-- B. MAKLUMAT PERUBAHAN DIPOHON -->
<table>
    <tr>
        <td colspan="4" class="section-header">B. MAKLUMAT PERUBAHAN DIPOHON</td>
    </tr>
    <tr>
        <td class="label">TAHAP PERUBAHAN</td>
        <td colspan="3">
            @php
                $priority_name = $priority ? strtolower($priority->priority_desc) : '';
            @endphp
            <span class="checkbox">{{ $priority_name == 'high' || $priority_name == 'tinggi' ? '&#9745;' : '&#9744;' }}</span> Tinggi &nbsp;&nbsp;&nbsp;&nbsp;
            <span class="checkbox">{{ $priority_name == 'normal' || $priority_name == 'sederhana' || $priority_name == 'medium' ? '&#9745;' : '&#9744;' }}</span> Sederhana &nbsp;&nbsp;&nbsp;&nbsp;
            <span class="checkbox">{{ $priority_name == 'low' || $priority_name == 'rendah' ? '&#9745;' : '&#9744;' }}</span> Rendah
        </td>
    </tr>
    <tr>
        <td class="label">SPESIFIKASI PERUBAHAN</td>
        <td colspan="3">
            @php
                $ips = $ticket->ips_type ?? '';
            @endphp
            <span class="checkbox">{{ $ips == 'Sekolah' ? '&#9745;' : '&#9744;' }}</span> Sistem/Aplikasi &nbsp;&nbsp;&nbsp;&nbsp;
            <span class="checkbox">&#9744;</span> Sistem Pengoperasian &nbsp;&nbsp;&nbsp;&nbsp;
            <span class="checkbox">&#9744;</span> Antivirus<br>
            <span class="checkbox">{{ $ips == 'Pusat' ? '&#9745;' : '&#9744;' }}</span> Perkakasan &nbsp;&nbsp;&nbsp;&nbsp;
            <span class="checkbox">&#9744;</span> Pangkalan Data<br>
            <span class="checkbox">{{ $ips == 'Tadika' ? '&#9745;' : '&#9744;' }}</span> Lain-lain, Nyatakan: {{ $ips ?: '-' }}
        </td>
    </tr>
    <tr>
        <td class="label">JENIS PERUBAHAN</td>
        <td colspan="3">
            <span class="checkbox">&#9744;</span> Peningkatan &nbsp;&nbsp;&nbsp;&nbsp;
            <span class="checkbox">&#9744;</span> Pembaikan &nbsp;&nbsp;&nbsp;&nbsp;
            <span class="checkbox">&#9744;</span> Kemaskini<br>
            <span class="checkbox">&#9744;</span> Pembatalan Transaksi &nbsp;&nbsp;&nbsp;&nbsp;
            <span class="checkbox">&#9744;</span> Lain-lain, Nyatakan:
        </td>
    </tr>
    <tr>
        <td class="label">PERSETUJUAN TAHAP PERKHIDMATAN (SLA)</td>
        <td colspan="3">
            @php
                $sla_name = $sla ? strtolower($sla->name) : '';
            @endphp
            <span class="checkbox">{{ str_contains($sla_name, '1') ? '&#9745;' : '&#9744;' }}</span> Tahap 1 &nbsp;&nbsp;&nbsp;&nbsp;
            <span class="checkbox">{{ str_contains($sla_name, '2') ? '&#9745;' : '&#9744;' }}</span> Tahap 2 &nbsp;&nbsp;&nbsp;&nbsp;
            <span class="checkbox">{{ str_contains($sla_name, '3') ? '&#9745;' : '&#9744;' }}</span> Tahap 3
        </td>
    </tr>
    <tr>
        <td colspan="4">
            <strong>KETERANGAN PERUBAHAN DIPOHON:</strong><br><br>
            <strong>Tajuk:</strong> {{ $thread ? $thread->title : '-' }}<br>
            <strong>Help Topic:</strong> {{ $helptopic ? $helptopic->topic : '-' }}<br>
            <strong>Negeri:</strong> {{ $ticket->state ?: '-' }} &nbsp;&nbsp; <strong>Daerah:</strong> {{ $ticket->district ?: '-' }} &nbsp;&nbsp; <strong>Jenis IPS:</strong> {{ $ticket->ips_type ?: '-' }}<br><br>
            @php
                $first_thread = $threads->first();
            @endphp
            @if($first_thread)
                {!! strip_tags($first_thread->body) !!}
            @endif
            <br>
        </td>
    </tr>
</table>

<!-- C. TINDAKAN PEMBEKAL -->
<table>
    <tr>
        <td colspan="4" class="section-header">C. TINDAKAN PEMBEKAL</td>
    </tr>
    <tr>
        <td colspan="4"><strong>PEGAWAI BERTANGGUNGJAWAB:</strong> {{ $assigned ? $assigned->first_name.' '.$assigned->last_name : '-' }}</td>
    </tr>
    <tr>
        <td colspan="4"><strong>SYARIKAT/BAHAGIAN/UNIT:</strong> Opensoft Technologies Sdn Bhd</td>
    </tr>
    <tr>
        <td colspan="4">
            <strong>KETERANGAN TINDAKAN DIAMBIL/PENYELESAIAN/ULASAN:</strong><br>
            <em>(Lampirkan Source code yang terlibat/Struktur Pangkalan Data/rajah PrintScreen sekiranya ada)</em><br><br>
            @foreach($threads->skip(1) as $t)
                @if($t->is_internal != 1)
                    <strong>{{ $t->poster }} ({{ date('d/m/Y H:i', strtotime($t->created_at)) }}):</strong><br>
                    {!! strip_tags($t->body) !!}<br><br>
                @endif
            @endforeach
        </td>
    </tr>
    <tr>
        <td class="label">PUNCA PENANGGUHAN</td>
        <td colspan="3">&nbsp;</td>
    </tr>
    <tr>
        <td class="label">TARIKH SELESAI</td>
        <td colspan="3">{{ $ticket->closed_at ? date('d/m/Y', strtotime($ticket->closed_at)) : '-' }}</td>
    </tr>
</table>

<!-- D. PENGESAHAN PENERIMAAN -->
<table>
    <tr>
        <td colspan="4" class="section-header">D. PENGESAHAN PENERIMAAN</td>
    </tr>
    <tr>
        <td colspan="2" style="height:100px;">
            <strong>1. DISAHKAN OLEH:</strong><br>
            (PEMOHON)<br><br>
            JAWATAN: {{ $user && $user->company ? $user->company : '.....................' }}<br>
            BAHAGIAN: {{ $department ? $department->name : '.....................' }}
        </td>
        <td colspan="2" style="height:100px;">
            <br><br><br><br><br>
            TANDATANGAN<br>
            TARIKH:
        </td>
    </tr>
</table>

</body>
</html>
