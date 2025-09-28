<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AssignmentSubmissionController extends Controller
{
    // Mahasiswa: lihat submission miliknya
    public function me(Assignment $assignment, Request $request)
    {
        $userId = Auth::id();

        $sub = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('mahasiswa_id', $userId)
            ->first();

        return response()->json(['submission' => $sub]);
    }

    // Mahasiswa: kirim / re-submit (teks dan/atau file)
    public function storeOrUpdate(Assignment $assignment, Request $request)
    {
        $userId = Auth::id();

        // Validasi dasar
        $request->validate([
            'text' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,zip,rar,png,jpg,jpeg'],
        ]);

        // Minimal harus ada salah satu
        if (!$request->filled('text') && !$request->hasFile('file')) {
            return response()->json([
                'message' => 'Provide either text or file.'
            ], 422);
        }

        // Jika assignment tidak mengizinkan file, tolak file upload
        if (!$assignment->allow_file && $request->hasFile('file')) {
            return response()->json([
                'message' => 'This assignment does not allow file uploads.'
            ], 422);
        }

        // Ambil/siapkan submission existing (unik per assignment & mahasiswa)
        $submission = AssignmentSubmission::firstOrNew([
            'assignment_id' => $assignment->id,
            'mahasiswa_id'  => $userId,
        ]);

        // Text
        if ($request->filled('text')) {
            $submission->answer_text = $request->string('text');
        }

        // File
        if ($request->hasFile('file')) {
            // hapus file lama jika ada
            if ($submission->file_path && Storage::disk('public')->exists($submission->file_path)) {
                Storage::disk('public')->delete($submission->file_path);
            }
            $path = $request->file('file')->store("assignments/{$assignment->id}/{$userId}", 'public');
            $submission->file_path = $path;
        }

        $submission->submitted_at = now();
        $submission->save();

        // refresh agar accessor file_url ikut
        $submission->refresh();

        return response()->json(['submission' => $submission], 201);
    }

    // Dosen: lihat semua submission 1 assignment
    public function index(Assignment $assignment, Request $request)
    {
        // Tambahkan middleware/policy sesuai sistem auth/role Anda
        $subs = $assignment->submissions()
            ->with(['student:id,name,email'])
            ->latest('submitted_at')
            ->get();

        return response()->json(['submissions' => $subs]);
    }

    // Dosen: beri/ubah nilai & feedback
    public function updateScore(AssignmentSubmission $submission, Request $request)
    {
        $data = $request->validate([
            'score'    => ['nullable', 'integer', 'min:0'],
            'feedback' => ['nullable', 'string'],
        ]);

        if (array_key_exists('score', $data)) {
            $submission->score = $data['score'];
            $submission->graded_at = now();
        }
        if (array_key_exists('feedback', $data)) {
            $submission->feedback = $data['feedback'];
        }

        $submission->save();

        return response()->json(['submission' => $submission]);
    }
}
