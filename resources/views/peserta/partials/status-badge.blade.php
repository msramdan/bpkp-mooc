@php
    $map = [
        'Berlangsung' => 'primary',
        'Selesai' => 'success',
        'Belum dikumpulkan' => 'warning',
        'Sudah dikumpulkan' => 'info',
        'Terjadwal' => 'secondary',
        'Bisa dikerjakan' => 'primary',
        'Terbit' => 'success',
        'Menunggu kelulusan' => 'warning',
    ];
    $color = $map[$status] ?? 'secondary';
@endphp
<span class="badge bg-{{ $color }}-transparent">{{ $status }}</span>
