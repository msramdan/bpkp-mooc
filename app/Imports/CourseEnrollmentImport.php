<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class CourseEnrollmentImport implements ToCollection
{
    public Collection $nips;

    public function __construct()
    {
        $this->nips = collect();
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $nip = trim((string) ($row[0] ?? ''));
            
            // Skip header or empty
            if ($nip === '' || str_contains(strtolower($nip), 'nip')) {
                continue;
            }

            $this->nips->push($nip);
        }
    }
}
