<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QuizSubmission;

class QuizSubmissionController extends Controller
{
    // Ambil semua submission untuk quiz tertentu atau mahasiswa tertentu
    public function index(Request $request)
    {
        try {
            $query = QuizSubmission::with(['quiz', 'mahasiswa', 'answers']);

            if ($request->has('quiz_id')) {
                $query->where('quiz_id', $request->quiz_id);
            }
            if ($request->has('mahasiswa_id')) {
                $query->where('mahasiswa_id', $request->mahasiswa_id);
            }

            $submissions = $query->orderBy('created_at', 'desc')->get();
            return response()->json([
                'success' => true,
                'message' => 'Quiz submissions retrieved successfully',
                'data' => $submissions
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve quiz submissions',
                'errors' => $e->getMessage()
            ], 400);
        }
    }

    // Ambil detail submission
    public function show($id)
    {
        try {
            $submission = QuizSubmission::with(['quiz', 'mahasiswa', 'answers'])->findOrFail($id);
            return response()->json([
                'success' => true,
                'message' => 'Quiz submission detail retrieved successfully',
                'data' => $submission
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve quiz submission detail',
                'errors' => $e->getMessage()
            ], 404);
        }
    }

    // Simpan submission baru (mahasiswa submit quiz)
    public function store(Request $request)
    {
        try {
            $request->validate([
                'quiz_id' => 'required|exists:quizzes,id',
                'answers' => 'required|array',
                // Validasi lain sesuai kebutuhan
            ]);

            $submission = QuizSubmission::create([
                'quiz_id' => $request->quiz_id,
                'mahasiswa_id' => $request->user()->id,
            ]);

            // Simpan jawaban (answers)
            foreach ($request->answers as $answerData) {
                $submission->answers()->create($answerData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Quiz submitted successfully',
                'data' => $submission->load('answers')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit quiz',
                'errors' => $e->getMessage()
            ], 400);
        }
    }

    // Update skor submission (dosen/admin)
    public function update(Request $request, $id)
    {
        try {
            $submission = QuizSubmission::findOrFail($id);

            $request->validate([
                'score' => 'required|integer|min:0',
            ]);

            $submission->score = $request->score;
            $submission->save();

            return response()->json([
                'success' => true,
                'message' => 'Quiz submission score updated successfully',
                'data' => $submission
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update quiz submission score',
                'errors' => $e->getMessage()
            ], 400);
        }
    }

    // Hapus submission
    public function destroy($id)
    {
        try {
            $submission = QuizSubmission::findOrFail($id);
            $submission->delete();

            return response()->json([
                'success' => true,
                'message' => 'Quiz submission deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete quiz submission',
                'errors' => $e->getMessage()
            ], 404);
        }
    }
}
