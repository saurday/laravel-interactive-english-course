<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Option;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class QuizAttemptController extends Controller
{
    // POST /api/quizzes/{quiz}/attempts/start
    public function start(Request $req, Quiz $quiz)
    {
        $userId = $req->user()->id;

        // jika sudah ada attempt "started", gunakan itu
        $existing = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $userId)->where('status', 'started')->first();
        if ($existing) return response()->json($existing, 200);

        $timeLeft = $quiz->time_limit ? $quiz->time_limit * 60 : null;

        $attempt = QuizAttempt::create([
            'quiz_id'    => $quiz->id,
            'user_id'    => $userId,
            'started_at' => now(),
            'time_left'  => $timeLeft,
            'status'     => 'started',
        ]);

        return response()->json($attempt, 201);
    }

    // GET /api/attempts/{attempt}
    public function show(QuizAttempt $attempt)
    {
        $this->authorizeAttempt($attempt);

        $attempt->load(['quiz.questions.options', 'answers']);
        return response()->json([
            'attempt' => $attempt,
            'quiz'    => $attempt->quiz, // berisi questions.options
            'answers' => $attempt->answers,
        ]);
    }

    // POST /api/attempts/{attempt}/answers
    public function saveAnswer(Request $req, QuizAttempt $attempt)
    {
        $this->authorizeAttempt($attempt);
        abort_if($attempt->status !== 'started', 409, 'Attempt already submitted');

        $data = $req->validate([
            'question_id' => 'required|exists:questions,id',
            'option_id'   => 'nullable|exists:options,id',
            'text_answer' => 'nullable|string',
        ]);

        // nilai benar (MCQ) dihitung saat submit; boleh autoset di sini juga:
        $isCorrect = null;
        if (!empty($data['option_id'])) {
            $isCorrect = Option::where('id', $data['option_id'])->value('is_correct') ? true : false;
        }

        $row = QuizAttemptAnswer::updateOrCreate(
            ['attempt_id' => $attempt->id, 'question_id' => $data['question_id']],
            ['option_id' => $data['option_id'] ?? null, 'text_answer' => $data['text_answer'] ?? null, 'is_correct' => $isCorrect]
        );

        return response()->json($row, 200);
    }

    // POST /api/attempts/{attempt}/submit
    public function submit(Request $req, QuizAttempt $attempt)
    {
        $this->authorizeAttempt($attempt);

        // sudah bukan "started" -> jangan double submit
        if ($attempt->status !== 'started') {
            return response()->json(['message' => 'Attempt already submitted', 'attempt' => $attempt], 409);
        }

        // ⬇⬇ CEGAH CONFLICT DENGAN UNIQUE INDEX (quiz_id, user_id, status)
        $already = QuizAttempt::where('quiz_id', $attempt->quiz_id)
            ->where('user_id', $attempt->user_id)
            ->where('status', 'submitted')
            ->first();

        if ($already) {
            // optional: tandai attempt aktif sebagai "aborted" supaya tidak menggantung
            $attempt->update(['status' => 'aborted', 'ended_at' => now()]);

            // balikan attempt yang sudah submitted
            return response()->json([
                'message' => 'You already submitted this quiz.',
                'score'   => $already->score,
                'attempt' => $already,
            ], 409);
        }

        return DB::transaction(function () use ($attempt) {
            $attempt->load(['quiz.questions.options', 'answers']);

            $total   = max(1, $attempt->quiz->questions->count());
            $correct = QuizAttemptAnswer::where('attempt_id', $attempt->id)
                ->where('is_correct', 1)->count();

            $score = round(($correct / $total) * 100, 2);

            $attempt->update([
                'status'   => 'submitted',
                'ended_at' => now(),
                'score'    => $score,
            ]);

            return response()->json(['score' => $score, 'attempt' => $attempt], 200);
        });
    }


    private function authorizeAttempt(QuizAttempt $attempt)
    {
        // Ambil ID user dari guard yang aktif
        $userId = Auth::id();

        // Kalau belum login, atau bukan pemilik attempt → 403
        abort_if(!$userId || (int)$userId !== (int)$attempt->user_id, 403, 'Forbidden');
    }

    public function meLatest(Request $req, Quiz $quiz)
    {
        $userId = $req->user()->id;
        $attempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->first();

        return response()->json(['attempt' => $attempt]);
    }

    public function abort(QuizAttempt $attempt)
    {
        $this->authorizeAttempt($attempt);

        // Hanya boleh abort kalau masih started
        if ($attempt->status === 'started') {
            $attempt->update([
                'status'   => QuizAttempt::STATUS_ABORTED, // ← di sinilah baris itu dipakai
                'ended_at' => now(),
            ]);
        }

        return response()->json(['attempt' => $attempt->fresh()], 200);
    }
}
