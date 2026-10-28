<?php

namespace App\Services;

use App\Models\Survey;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Membaca file template import soal Pilihan Ganda (radio, 1 kunci jawaban)
 * dan menyimpannya ke bank soal/kuesioner.
 */
class PilihanGandaImporter
{
    public const SHEET_TITLE = 'Soal PG';

    public const EXAMPLE_MARKER = 'Contoh';

    public const HEADINGS = ['No', 'Pertanyaan', 'Pilihan A', 'Pilihan B', 'Pilihan C', 'Pilihan D', 'Pilihan E', 'Kunci Jawaban', 'Wajib Dijawab'];

    private const OPTION_LETTERS = ['A', 'B', 'C', 'D', 'E'];

    private const MAX_OPTION_LENGTH = 255;

    /**
     * Hasil baca file: setiap baris berisi data soal dan daftar error (kosong = valid).
     *
     * @return array{rows: array<int, array<string, mixed>>, skipped_examples: int}
     */
    public function parse(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getSheetByName(self::SHEET_TITLE) ?? $spreadsheet->getSheet(0);
        $cells = $sheet->toArray(null, true, true, false);

        $rows = [];
        $skippedExamples = 0;

        foreach ($cells as $index => $cell) {
            $values = array_map(fn ($value) => trim((string) $value), array_pad($cell, count(self::HEADINGS), ''));

            if ($index === 0 && Str::lower($values[1]) === 'pertanyaan') {
                continue;
            }

            if (implode('', array_slice($values, 1)) === '') {
                continue;
            }

            if (Str::lower($values[0]) === Str::lower(self::EXAMPLE_MARKER)) {
                $skippedExamples++;

                continue;
            }

            $rows[] = $this->parseRow($index + 1, $values);
        }

        return ['rows' => $rows, 'skipped_examples' => $skippedExamples];
    }

    /**
     * Simpan soal yang valid ke kuesioner, melanjutkan urutan soal yang sudah ada.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function import(Survey $survey, array $rows): int
    {
        $validRows = array_values(array_filter($rows, fn (array $row) => $row['errors'] === []));

        DB::transaction(function () use ($survey, $validRows): void {
            $urutan = (int) $survey->questions()->max('urutan');

            foreach ($validRows as $row) {
                $question = $survey->questions()->create([
                    'type' => 'radio',
                    'question_text' => $row['question'],
                    'is_required' => $row['is_required'],
                    'urutan' => ++$urutan,
                ]);

                foreach ($row['options'] as $position => $option) {
                    $isCorrect = $option['letter'] === $row['answer'];

                    $question->options()->create([
                        'option_text' => $option['text'],
                        'urutan' => $position + 1,
                        'score_value' => $isCorrect ? 100 : 0,
                        'is_correct' => $isCorrect,
                    ]);
                }
            }
        });

        return count($validRows);
    }

    /**
     * @param  array<int, string>  $values
     * @return array<string, mixed>
     */
    private function parseRow(int $line, array $values): array
    {
        [, $question, $a, $b, $c, $d, $e, $answer, $required] = $values;

        $options = collect([$a, $b, $c, $d, $e])
            ->map(fn (string $text, int $i) => ['letter' => self::OPTION_LETTERS[$i], 'text' => $text])
            ->filter(fn (array $option) => $option['text'] !== '')
            ->values()
            ->all();
        $letters = array_column($options, 'letter');
        $answer = Str::upper($answer);

        $errors = [];

        if ($question === '') {
            $errors[] = __('Pertanyaan belum diisi.');
        }

        if (count($options) < 2) {
            $errors[] = __('Isi minimal 2 pilihan jawaban (Pilihan A dan Pilihan B).');
        }

        foreach ($options as $option) {
            if (mb_strlen($option['text']) > self::MAX_OPTION_LENGTH) {
                $errors[] = __('Pilihan :letter terlalu panjang (maksimal :max karakter).', ['letter' => $option['letter'], 'max' => self::MAX_OPTION_LENGTH]);
            }
        }

        if ($answer === '') {
            $errors[] = __('Kunci jawaban belum diisi.');
        } elseif (! in_array($answer, self::OPTION_LETTERS, true)) {
            $errors[] = __('Kunci jawaban ":answer" tidak dikenal. Isi dengan huruf A, B, C, D, atau E.', ['answer' => $answer]);
        } elseif (! in_array($answer, $letters, true)) {
            $errors[] = __('Kunci jawaban :answer, tetapi Pilihan :answer masih kosong.', ['answer' => $answer]);
        }

        $required = Str::lower($required);
        if (! in_array($required, ['', 'ya', 'tidak'], true)) {
            $errors[] = __('Kolom Wajib Dijawab diisi Ya atau Tidak.');
        }

        return [
            'line' => $line,
            'question' => $question,
            'options' => $options,
            'answer' => $answer,
            'is_required' => $required !== 'tidak',
            'errors' => $errors,
        ];
    }
}
