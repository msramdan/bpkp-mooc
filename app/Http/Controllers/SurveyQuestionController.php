<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SurveyQuestionController extends Controller
{
    public function builder(Survey $survey)
    {
        $survey->load(['questions.options']);
        return view('surveys.builder', compact('survey'));
    }

    public function store(Request $request, Survey $survey)
    {
        $data = $request->validate([
            'type' => 'required|in:radio,checkbox,text,rating',
            'question_text' => 'required|string',
            'is_required' => 'boolean',
            'options' => 'required_if:type,radio,checkbox|array|min:2',
            'options.*' => 'required_if:type,radio,checkbox|string|max:255',
            'options_score' => 'nullable|array',
            'options_score.*' => 'nullable|integer|min:0|max:100',
            'options_correct' => 'nullable|array',
            'options_correct.*' => 'nullable|boolean',
        ], [
            'options.required_if' => __('Tipe pertanyaan Pilihan Ganda dan Kotak Centang wajib memiliki minimal 2 opsi jawaban.'),
            'options.min' => __('Tipe pertanyaan Pilihan Ganda dan Kotak Centang wajib memiliki minimal 2 opsi jawaban.'),
            'options.*.required_if' => __('Teks pada opsi jawaban tidak boleh kosong.'),
        ]);

        if ($data['type'] === 'radio') {
            $correctCount = collect($request->input('options_correct', []))->filter(fn($val) => (bool)$val)->count();
            if ($correctCount !== 1) {
                return back()->withErrors(['options_correct' => __('Tipe Pilihan Ganda (Radio) mewajibkan tepat 1 Kunci Jawaban yang benar.')])->withInput();
            }
        } elseif ($data['type'] === 'checkbox') {
            $correctCount = collect($request->input('options_correct', []))->filter(fn($val) => (bool)$val)->count();
            if ($correctCount < 2) {
                return back()->withErrors(['options_correct' => __('Tipe Kotak Centang (Checkbox) mewajibkan minimal 2 Kunci Jawaban yang benar. Jika hanya memiliki 1 kunci benar, silakan gunakan tipe Pilihan Ganda (Radio).')])->withInput();
            }
        }

        DB::transaction(function () use ($data, $request, $survey) {
            $question = $survey->questions()->create([
                'type' => $data['type'],
                'question_text' => $data['question_text'],
                'is_required' => $request->has('is_required') ? 1 : 0,
                'urutan' => $survey->questions()->max('urutan') + 1,
            ]);

            if (in_array($data['type'], ['radio', 'checkbox']) && !empty($data['options'])) {
                foreach ($data['options'] as $idx => $optText) {
                    if (trim($optText) !== '') {
                        $isCorrect = (bool) ($request->input("options_correct.{$idx}") ?? false);
                        $question->options()->create([
                            'option_text' => $optText,
                            'urutan' => $idx + 1,
                            'score_value' => $isCorrect ? 100 : 0,
                            'is_correct' => $isCorrect,
                        ]);
                    }
                }
            }
        });

        return back()->with('success', __('Pertanyaan berhasil ditambahkan.'));
    }

    public function update(Request $request, Survey $survey, SurveyQuestion $question)
    {
        abort_if($question->survey_id !== $survey->id, 403);

        $data = $request->validate([
            'type' => 'required|in:radio,checkbox,text,rating',
            'question_text' => 'required|string',
            'is_required' => 'boolean',
            'options' => 'required_if:type,radio,checkbox|array|min:2',
            'options.*' => 'required_if:type,radio,checkbox|string|max:255',
            'options_score' => 'nullable|array',
            'options_score.*' => 'nullable|integer|min:0|max:100',
            'options_correct' => 'nullable|array',
            'options_correct.*' => 'nullable|boolean',
        ], [
            'options.required_if' => __('Tipe pertanyaan Pilihan Ganda dan Kotak Centang wajib memiliki minimal 2 opsi jawaban.'),
            'options.min' => __('Tipe pertanyaan Pilihan Ganda dan Kotak Centang wajib memiliki minimal 2 opsi jawaban.'),
            'options.*.required_if' => __('Teks pada opsi jawaban tidak boleh kosong.'),
        ]);

        if ($data['type'] === 'radio') {
            $correctCount = collect($request->input('options_correct', []))->filter(fn($val) => (bool)$val)->count();
            if ($correctCount !== 1) {
                return back()->withErrors(['options_correct' => __('Tipe Pilihan Ganda (Radio) mewajibkan tepat 1 Kunci Jawaban yang benar.')])->withInput();
            }
        } elseif ($data['type'] === 'checkbox') {
            $correctCount = collect($request->input('options_correct', []))->filter(fn($val) => (bool)$val)->count();
            if ($correctCount < 2) {
                return back()->withErrors(['options_correct' => __('Tipe Kotak Centang (Checkbox) mewajibkan minimal 2 Kunci Jawaban yang benar. Jika hanya memiliki 1 kunci benar, silakan gunakan tipe Pilihan Ganda (Radio).')])->withInput();
            }
        }

        DB::transaction(function () use ($data, $request, $question) {
            $question->update([
                'type' => $data['type'],
                'question_text' => $data['question_text'],
                'is_required' => $request->has('is_required') ? 1 : 0,
            ]);

            if (in_array($question->type, ['radio', 'checkbox'])) {
                $question->options()->delete();
                if (!empty($data['options'])) {
                    foreach ($data['options'] as $idx => $optText) {
                        if (trim($optText) !== '') {
                            $isCorrect = (bool) ($request->input("options_correct.{$idx}") ?? false);
                            $question->options()->create([
                                'option_text' => $optText,
                                'urutan' => $idx + 1,
                                'score_value' => $isCorrect ? 100 : 0,
                                'is_correct' => $isCorrect,
                            ]);
                        }
                    }
                }
            } else {
                $question->options()->delete();
            }
        });

        return back()->with('success', __('Pertanyaan berhasil diperbarui.'));
    }

    public function destroy(Survey $survey, SurveyQuestion $question)
    {
        abort_if($question->survey_id !== $survey->id, 403);
        $question->delete();
        return back()->with('success', __('Pertanyaan berhasil dihapus.'));
    }

    public function reorder(Request $request, Survey $survey)
    {
        $order = $request->input('order'); // array of question IDs in new order
        if (is_array($order)) {
            foreach ($order as $idx => $id) {
                $survey->questions()->where('id', $id)->update(['urutan' => $idx + 1]);
            }
        }
        return response()->json(['success' => true]);
    }
}
