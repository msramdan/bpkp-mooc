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
            'options' => 'nullable|array',
            'options.*' => 'string|max:255',
        ]);

        DB::transaction(function () use ($data, $survey) {
            $question = $survey->questions()->create([
                'type' => $data['type'],
                'question_text' => $data['question_text'],
                'is_required' => $request->has('is_required') ? 1 : 0,
                'urutan' => $survey->questions()->max('urutan') + 1,
            ]);

            if (in_array($data['type'], ['radio', 'checkbox']) && !empty($data['options'])) {
                foreach ($data['options'] as $idx => $optText) {
                    if (trim($optText) !== '') {
                        $question->options()->create([
                            'option_text' => $optText,
                            'urutan' => $idx + 1,
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
            'question_text' => 'required|string',
            'is_required' => 'boolean',
            'options' => 'nullable|array',
            'options.*' => 'string|max:255',
        ]);

        DB::transaction(function () use ($data, $request, $question) {
            $question->update([
                'question_text' => $data['question_text'],
                'is_required' => $request->has('is_required') ? 1 : 0,
            ]);

            if (in_array($question->type, ['radio', 'checkbox'])) {
                // Sederhananya hapus opsi lama dan buat yang baru
                $question->options()->delete();
                if (!empty($data['options'])) {
                    foreach ($data['options'] as $idx => $optText) {
                        if (trim($optText) !== '') {
                            $question->options()->create([
                                'option_text' => $optText,
                                'urutan' => $idx + 1,
                            ]);
                        }
                    }
                }
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
