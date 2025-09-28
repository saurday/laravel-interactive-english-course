<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Answer;

class AnswerController extends Controller
{
    // Ambil semua jawaban untuk quiz submission tertentu
    public function index(Request $request)
    {
        $query = Answer::with(['question', 'option', 'quizSubmission']);

        if ($request->has('quiz_submission_id')) {
            $query->where('quiz_submission_id', $request->quiz_submission_id);
        }
        if ($request->has('question_id')) {
            $query->where('question_id', $request->question_id);
        }

        $answers = $query->orderBy('id')->get();
        return response()->json([
            'success' => true,
            'message' => 'Answers retrieved successfully',
            'data' => $answers
        ], 200);
    }

    // Ambil detail jawaban
    public function show($id)
    {
        $answer = Answer::with(['question', 'option', 'quizSubmission'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Answer detail retrieved successfully',
            'data' => $answer
        ], 200);
    }

    // Buat jawaban baru
    public function store(Request $request)
    {
        $request->validate([
            'quiz_submission_id' => 'required|exists:quiz_submissions,id',
            'question_id' => 'required|exists:questions,id',
            'answer_text' => 'nullable|string',
            'option_id' => 'nullable|exists:options,id',
        ]);

        $answer = Answer::create($request->only(['quiz_submission_id', 'question_id', 'answer_text', 'option_id']));

        return response()->json([
            'success' => true,
            'message' => 'Answer created successfully',
            'data' => $answer
        ], 201);
    }

    // Update jawaban
    public function update(Request $request, $id)
    {
        $answer = Answer::findOrFail($id);

        $request->validate([
            'answer_text' => 'nullable|string',
            'option_id' => 'nullable|exists:options,id',
        ]);

        $answer->update($request->only(['answer_text', 'option_id']));

        return response()->json([
            'success' => true,
            'message' => 'Answer updated successfully',
            'data' => $answer
        ], 200);
    }

    // Hapus jawaban
    public function destroy($id)
    {
        $answer = Answer::findOrFail($id);
        $answer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Answer deleted successfully'
        ], 200);
    }
}
