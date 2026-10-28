<?php

namespace App\Exports\PilihanGanda;

use App\Services\PilihanGandaImporter;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SoalSheet implements FromArray, WithColumnWidths, WithEvents, WithHeadings, WithStyles, WithTitle
{
    /** Jumlah baris yang diberi dropdown kunci jawaban & wajib dijawab. */
    private const INPUT_ROWS = 300;

    public function title(): string
    {
        return PilihanGandaImporter::SHEET_TITLE;
    }

    public function headings(): array
    {
        return PilihanGandaImporter::HEADINGS;
    }

    public function array(): array
    {
        return [
            [
                PilihanGandaImporter::EXAMPLE_MARKER,
                'Apa kepanjangan dari SPIP?',
                'Sistem Pengendalian Intern Pemerintah',
                'Sistem Pengawasan Internal Pusat',
                'Standar Pelaporan Instansi Pemerintah',
                'Sistem Perencanaan Investasi Publik',
                '',
                'A',
                'Ya',
            ],
            [
                PilihanGandaImporter::EXAMPLE_MARKER,
                'Lembaga yang bertugas melakukan pengawasan intern atas akuntabilitas keuangan negara adalah ...',
                'BPK',
                'BPKP',
                'KPK',
                '',
                '',
                'B',
                'Ya',
            ],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 10, 'B' => 60, 'C' => 30, 'D' => 30, 'E' => 30, 'F' => 30, 'G' => 30, 'H' => 16, 'I' => 16];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '214B9A']],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            ],
            '2:3' => [
                'font' => ['italic' => true, 'color' => ['rgb' => '6B7280']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F2FD']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $lastRow = self::INPUT_ROWS + 1;

                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->freezePane('A2');
                $sheet->getStyle("B2:G{$lastRow}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

                $this->addListValidation($sheet, "H2:H{$lastRow}", '"A,B,C,D,E"', 'Kunci jawaban', 'Pilih huruf A, B, C, D, atau E sesuai pilihan yang benar.');
                $this->addListValidation($sheet, "I2:I{$lastRow}", '"Ya,Tidak"', 'Wajib dijawab', 'Pilih Ya atau Tidak. Kosong = Ya.');
            },
        ];
    }

    private function addListValidation(Worksheet $sheet, string $range, string $formula, string $title, string $prompt): void
    {
        $validation = $sheet->getCell(explode(':', $range)[0])->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST)
            ->setErrorStyle(DataValidation::STYLE_STOP)
            ->setAllowBlank(true)
            ->setShowDropDown(true)
            ->setShowInputMessage(true)
            ->setShowErrorMessage(true)
            ->setPromptTitle($title)
            ->setPrompt($prompt)
            ->setErrorTitle($title)
            ->setError($prompt)
            ->setFormula1($formula);

        $sheet->setDataValidation($range, $validation);
    }
}
