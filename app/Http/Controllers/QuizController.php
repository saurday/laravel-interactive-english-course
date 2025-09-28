<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quiz;
use Illuminate\Support\Facades\DB;
use App\Models\Question;
use App\Models\Option;

// app/Http/Controllers/QuizController.php
class QuizController extends Controller
{
    public function show($id)
    {
        // ambil quiz beserta questions & options
        $quiz = \App\Models\Quiz::with(['questions.options'])->findOrFail($id);

        // bentuk respons yang FE kamu sudah kenal
        return response()->json(['quiz' => $quiz], 200);
    }
    public function index(Request $request)
    {
        $q = Quiz::query()->withCount('questions')->latest();

        // (opsional) kalau butuh filter lain, pakai relasi:
        // if ($request->filled('week_id')) {
        //     $q->whereHas('resource', fn($r) => $r->where('week_id', $request->week_id));
        // }
        // if ($request->filled('class_id')) {
        //     $q->whereHas('resource.week', fn($w) => $w->where('class_id', $request->class_id));
        // }

        return response()->json($q->get(), 200);
    }



    // app/Http/Controllers/QuizController.php

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'           => 'required|string|max:255',
            'instructions'     => 'nullable|string',
            'time_limit'      => 'nullable|integer|min:0',
            'shuffle'         => 'nullable|boolean',

            // FE kirim items
            'items'           => 'required|array|min:1',
            'items.*.type'    => 'required|string', // kita normalisasi sendiri
            'items.*.prompt'  => 'required|string',
            'items.*.options' => 'sometimes|array',     // untuk MCQ
            'items.*.answer'  => 'sometimes|integer',   // index jawaban benar
            'items.*.answers' => 'sometimes|array',     // untuk short (optional)
        ]);

        return DB::transaction(function () use ($data) {
            $quiz = Quiz::create([
                'title'       => $data['title'],
                'instructions' => $data['instructions'] ?? null,
                'time_limit'  => $data['time_limit'] ?? 0,
                'shuffle'     => (bool)($data['shuffle'] ?? true),
            ]);

            foreach ($data['items'] as $it) {

                // --- NORMALISASI TYPE → ENUM UPPERCASE ---
                $t = strtolower($it['type']); // bisa 'mcq' | 'short' | 'multiple_choice' | 'short_answer'
                $enumType = match ($t) {
                    'mcq', 'multiple_choice'  => 'MULTIPLE_CHOICE',
                    'short', 'short_answer'   => 'SHORT_ANSWER',
                    default => throw \Illuminate\Validation\ValidationException::withMessages([
                        'items.*.type' => ["Unsupported type: {$it['type']}"],
                    ]),
                };

                $q = Question::create([
                    'quiz_id'       => $quiz->id,
                    'question_text' => $it['prompt'],
                    'type'          => $enumType, // ENUM uppercase sesuai DB
                ]);

                if ($enumType === 'MULTIPLE_CHOICE') {
                    $ops = $it['options'] ?? [];
                    $correctIdx = (int)($it['answer'] ?? -1);
                    foreach ($ops as $j => $text) {
                        Option::create([
                            'question_id' => $q->id,
                            'option_text' => (string)$text,
                            'order'       => $j,
                            'is_correct'  => $j === $correctIdx,
                        ]);
                    }
                }


                if ($enumType === 'SHORT_ANSWER') {
                    $answers = array_values($it['answers'] ?? []);
                    $q->answers = $answers;
                    $q->save();
                }
            }

            $quiz->load(['questions.options']);
            return response()->json($quiz, 201);
        });
    }


  public function update(Request $request, $id)
{
    $quiz = Quiz::with('questions.options')->findOrFail($id);

    $payload = $request->validate([
        'title'        => 'sometimes|string|max:255',
        'instructions' => 'sometimes|nullable|string',
        'time_limit'   => 'sometimes|integer|min:0',
        'shuffle'      => 'sometimes|boolean',
        'questions'    => 'sometimes|array',
        'questions.*.id'            => 'sometimes|integer|exists:questions,id',
        'questions.*.type'          => 'required_with:questions|string',
        'questions.*.question_text' => 'required_with:questions|string',
        'questions.*.options'       => 'sometimes|array',
        'questions.*.options.*.id'         => 'sometimes|integer|exists:options,id',
        'questions.*.options.*.option_text'=> 'required_with:questions|string',
        'questions.*.options.*.is_correct' => 'sometimes|boolean',
        'questions.*.answers'       => 'sometimes|array',
    ]);

    DB::transaction(function () use ($quiz, $payload) {
        $quiz->update($payload);

        if (!empty($payload['questions'])) {
            // hapus soal yang tidak dikirim lagi (opsional)
            $keepIds = collect($payload['questions'])->pluck('id')->filter()->all();
            $quiz->questions()->whereNotIn('id',$keepIds ?: [-1])->delete();

            foreach ($payload['questions'] as $i => $qIn) {
                $t = strtolower($qIn['type']);
                $typeEnum = in_array($t, ['mcq','multiple_choice']) ? 'MULTIPLE_CHOICE' : 'SHORT_ANSWER';

                $q = isset($qIn['id'])
                    ? \App\Models\Question::findOrFail($qIn['id'])
                    : new \App\Models\Question(['quiz_id' => $quiz->id]);

                $q->question_text = $qIn['question_text'];
                $q->type = $typeEnum;

                if ($typeEnum === 'SHORT_ANSWER') {
                    $q->answers = array_values($qIn['answers'] ?? []);
                    $q->save();
                    // pastikan tak ada option tersisa
                    $q->options()->delete();
                } else {
                    $q->answers = null; // bersihkan jika sebelumnya short
                    $q->save();

                    // sederhananya: reset & buat ulang opsi
                    $q->options()->delete();
                    foreach ($qIn['options'] ?? [] as $j => $op) {
                        \App\Models\Option::create([
                            'question_id' => $q->id,
                            'option_text' => $op['option_text'],
                            'is_correct'  => (bool)($op['is_correct'] ?? false),
                            'order'       => $j,
                        ]);
                    }
                }
            }
        }
    });

    return response()->json($quiz->fresh(['questions.options']), 200);
}


    public function destroy($id)
    {
        Quiz::findOrFail($id)->delete();
        return response()->json(['message' => 'Quiz deleted'], 200);
    }
}
