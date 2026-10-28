<?php

namespace App\Exports\PilihanGanda;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Template import soal Pilihan Ganda: sheet "Soal PG" untuk diisi dan sheet
 * "Petunjuk" berisi cara pengisian.
 */
class TemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new SoalSheet,
            new PetunjukSheet,
        ];
    }
}
