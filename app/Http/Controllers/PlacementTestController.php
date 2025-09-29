<?php

namespace App\Http\Controllers;

use App\Models\{
    PlacementTest,
    PlacementAttempt,
    PlacementAnswer,
    PlacementBand,
    UserPlacement,
    PlacementOption
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlacementTestController extends Controller
{
    // Dashboard status
  public function state(Request $req)
{
    $u = $req->user();

    $latest = UserPlacement::where('user_id', $u->id)->first();
    $latestAttempt = $latest ? PlacementAttempt::find($latest->attempt_id) : null;

    // hitung benar/total (kalau sudah pernah tes)
    $correct = 0; $total = 0;
    if ($latestAttempt) {
        $correct = DB::table('placement_answers')
            ->join('placement_options','placement_options.id','=','placement_answers.option_id')
            ->where('placement_answers.attempt_id', $latestAttempt->id)
            ->where('placement_options.is_correct', true)
            ->count();

        $total = DB::table('placement_questions')
            ->where('test_id', $latestAttempt->test_id)
            ->count();
    }

    $levelName = $latest?->level_id
        ? DB::table('placement_levels')->where('id', $latest->level_id)->value('name')
        : null;

    return response()->json([
        'latest' => $latest ? [
            'level'          => $latest->level,        // 'A1'..'C2'
            'level_name'     => $levelName,            // mis: "C2 PROFICIENCY"
            'score_percent'  => $latest->score,        // tetap simpan persen di DB
            'correct'        => $correct,
            'total'          => $total,
            'tested_at'      => $latest->tested_at,
            'level_id'       => $latest->level_id,
            'materials_url'  => $latest->level_id ? url("/student/placement/levels/{$latest->level_id}") : null,
        ] : null,
        'already_taken' => (bool) $latest, // untuk FE sembunyikan tombol
        'can_retake'    => false,
        'retake_available_at' => null,
    ]);
}


    // Mulai attempt (validasi kebijakan retake)
public function start(Request $req)
{
    $u = $req->user();
    $test = PlacementTest::where('is_active', true)->firstOrFail();

    // resume kalau ada attempt yg belum submit
    if ($started = PlacementAttempt::where('user_id', $u->id)->where('status','started')->latest()->first()) {
        return response()->json([
            'attempt' => $started,
            'test' => [
                'title'      => $test->title,
                'time_limit' => $test->time_limit,
                'questions'  => $test->questions()->with('options')->orderBy('number')->get(),
            ],
        ]);
    }

    // one-time: tolak jika SUDAH submit sebelumnya
    $already = PlacementAttempt::where('user_id', $u->id)->where('status','submitted')->exists();
    if ($already) {
        return response()->json(['message' => 'Placement test is one-time only.'], 403);
    }

    $attempt = PlacementAttempt::create([
        'user_id'  => $u->id,
        'test_id'  => $test->id,
        'status'   => 'started',
        'started_at' => now(),
        'retake_available_at' => null,
    ]);

    return response()->json([
        'attempt' => $attempt,
        'test' => [
            'title'      => $test->title,
            'time_limit' => $test->time_limit,
            'questions'  => $test->questions()->with('options')->orderBy('number')->get(),
        ],
    ]);
}


    public function show(Request $req, $id)
    {
        $a = PlacementAttempt::where('id', $id)->where('user_id', $req->user()->id)->firstOrFail();
        $q = $a->test->questions()->with('options')->orderBy('number')->get();
        return response()->json(['attempt' => $a, 'test' => $a->test, 'questions' => $q]);
    }

    public function answer(Request $req, $id)
    {
        $u = $req->user();
        $a = PlacementAttempt::where('id', $id)->where('user_id', $u->id)->firstOrFail();
        if ($a->status !== 'started') return response()->json(['message' => 'Attempt closed'], 409);

        $data = $req->validate([
            'question_id' => 'required|exists:placement_questions,id',
            'option_id'   => 'required|exists:placement_options,id',
        ]);

        PlacementAnswer::updateOrCreate(
            ['attempt_id' => $a->id, 'question_id' => $data['question_id']],
            ['option_id'  => $data['option_id']]
        );

        return response()->json(['ok' => true]);
    }

   public function submit(Request $req, $id)
{
    $u = $req->user();
    $a = PlacementAttempt::where('id', $id)->where('user_id', $u->id)->firstOrFail();
    if ($a->status !== 'started') return response()->json(['message' => 'Already submitted'], 409);

    $correct = DB::table('placement_answers')
        ->join('placement_options','placement_options.id','=','placement_answers.option_id')
        ->where('placement_answers.attempt_id', $a->id)
        ->where('placement_options.is_correct', true)
        ->count();

    $total = DB::table('placement_questions')->where('test_id', $a->test_id)->count();
    $den = max(1,$total);
    $scorePercent = (int) round(($correct / $den) * 100);

    $band = PlacementBand::where('min_score','<=',$scorePercent)
            ->where('max_score','>=',$scorePercent)->first();
    $levelCode = $band?->level;
    $levelId   = $band?->target_level_id;

    if ($levelId) {
        DB::table('placement_level_enrollments')->updateOrInsert(
            ['level_id'=>$levelId,'user_id'=>$u->id],
            ['joined_at'=>now(),'updated_at'=>now()]
        );
    }

    $a->update([
        'status'   => 'submitted',
        'score'    => $scorePercent,
        'level'    => $levelCode,
        'ended_at' => now(),
    ]);

    UserPlacement::updateOrCreate(
        ['user_id'=>$u->id],
        [
            'level'      => $levelCode,
            'score'      => $scorePercent,
            'attempt_id' => $a->id,
            'level_id'   => $levelId,
            'tested_at'  => now(),
        ]
    );

    $levelName = $levelId ? DB::table('placement_levels')->where('id',$levelId)->value('name') : null;

    return response()->json([
        'correct'      => $correct,
        'total'        => $total,
        'score_percent'=> $scorePercent,
        'level'        => $levelCode,
        'level_name'   => $levelName,
        'level_id'     => $levelId,
        'review_url'   => url("/student/placement-review?aid={$a->id}"),
        'materials_url'=> $levelId ? url("/student/placement/levels/{$levelId}") : null,
    ]);
}

public function review(Request $req, $id)
{
    $u = $req->user();
    $a = PlacementAttempt::where('id',$id)->where('user_id',$u->id)->firstOrFail();
    if ($a->status !== 'submitted') {
        return response()->json(['message'=>'Attempt not submitted'], 409);
    }

    $questions = DB::table('placement_questions')
        ->where('test_id', $a->test_id)->orderBy('number')->get();

    $answersByQ = DB::table('placement_answers')
        ->where('attempt_id',$a->id)
        ->pluck('option_id','question_id');

    $items = [];
    foreach ($questions as $q) {
        $opts = DB::table('placement_options')
            ->where('question_id', $q->id)
            ->orderBy('id')->get()
            ->map(function($op) use ($answersByQ, $q) {
                return [
                    'id'         => $op->id,
                    'text'       => $op->text,
                    'is_correct' => (bool) $op->is_correct,
                    'chosen'     => (string)($answersByQ[$q->id] ?? '') === (string)$op->id,
                ];
            })->values();

        $items[] = [
            'id'       => $q->id,
            'text'     => $q->text,
            'options'  => $opts,
        ];
    }

    $correct = DB::table('placement_answers')
        ->join('placement_options','placement_options.id','=','placement_answers.option_id')
        ->where('placement_answers.attempt_id',$a->id)
        ->where('placement_options.is_correct', true)->count();

    $total = count($questions);
    $scorePercent = $total > 0 ? (int) round($correct / $total * 100) : 0;

    $levelId   = UserPlacement::where('user_id',$u->id)->value('level_id');
    $levelName = $levelId ? DB::table('placement_levels')->where('id',$levelId)->value('name') : null;
    $levelCode = $a->level;

    return response()->json([
        'attempt_id'    => $a->id,
        'correct'       => $correct,
        'total'         => $total,
        'score_percent' => $scorePercent,
        'level'         => $levelCode,
        'level_name'    => $levelName,
        'questions'     => $items,
        'materials_url' => $levelId ? url("/student/placement/levels/{$levelId}") : null,
    ]);
}


    // === helper: cek progress level (100%) ===
    private function hasCompletedCurrentLevel(int $userId, ?int $levelId): bool
    {
        if (!$levelId) return false;
        $total = DB::table('placement_level_contents')->where('level_id', $levelId)->count();
        if ($total === 0) return false;

        $done = DB::table('placement_level_progress')
            ->join('placement_level_contents','placement_level_contents.id','=','placement_level_progress.content_id')
            ->where('placement_level_contents.level_id', $levelId)
            ->where('placement_level_progress.user_id', $userId)
            ->count();

        return $done >= $total;
    }
}
