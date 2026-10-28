<?php

namespace App\Exports\PilihanGanda;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PetunjukSheet implements FromArray, WithColumnWidths, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Petunjuk';
    }

    public function array(): array
    {
        return [
            ['Petunjuk Pengisian Soal Pilihan Ganda'],
            [''],
            ['1.', 'Isi soal pada sheet "Soal PG", satu baris untuk satu soal. Mulai dari baris ke-4 (di bawah 2 baris contoh).'],
            ['2.', 'Kolom "Pertanyaan" wajib diisi.'],
            ['3.', 'Isi minimal 2 pilihan jawaban (Pilihan A dan Pilihan B). Pilihan C sampai E boleh dikosongkan.'],
            ['4.', 'Kolom "Kunci Jawaban" diisi satu huruf (A, B, C, D, atau E) sesuai pilihan yang benar. Gunakan dropdown agar tidak salah ketik.'],
            ['5.', 'Kolom "Wajib Dijawab" diisi Ya atau Tidak. Jika dikosongkan dianggap Ya.'],
            ['6.', 'Baris yang kolom "No"-nya bertuliskan "Contoh" tidak akan diimpor. Boleh dihapus atau dibiarkan.'],
            ['7.', 'Simpan file (format .xlsx), lalu unggah di menu Bank Soal › Builder › Import Soal PG.'],
            ['8.', 'Sebelum disimpan, sistem akan menampilkan pratinjau dan memberi tahu baris yang perlu diperbaiki.'],
            [''],
            ['Catatan', 'Setiap soal Pilihan Ganda hanya memiliki 1 jawaban benar dan bernilai 100 poin.'],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 10, 'B' => 110];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('A1:B1');
        $sheet->getStyle('B3:B12')->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '214B9A']],
            ],
            'A3:A12' => ['font' => ['bold' => true], 'alignment' => ['vertical' => Alignment::VERTICAL_TOP]],
            12 => ['font' => ['bold' => true, 'color' => ['rgb' => 'E24A55']]],
        ];
    }
}
