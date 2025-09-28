<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuizSubmissionController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\ChatMessageController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AssignmentSubmissionController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\OptionController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\WeekController;
use App\Http\Controllers\CourseResourceController;
use App\Http\Controllers\QuizAttemptController;

// Auth
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);

    // Question
    Route::get('questions', [QuestionController::class, 'index']);
    Route::get('questions/{id}', [QuestionController::class, 'show']);
    Route::post('questions', [QuestionController::class, 'store']);
    Route::put('questions/{id}', [QuestionController::class, 'update']);
    Route::delete('questions/{id}', [QuestionController::class, 'destroy']);

    // Answer
    Route::get('answers', [AnswerController::class, 'index']);
    Route::get('answers/{id}', [AnswerController::class, 'show']);
    Route::post('answers', [AnswerController::class, 'store']);
    Route::put('answers/{id}', [AnswerController::class, 'update']);
    Route::delete('answers/{id}', [AnswerController::class, 'destroy']);

    // Option
    Route::get('options', [OptionController::class, 'index']);
    Route::get('options/{id}', [OptionController::class, 'show']);
    Route::post('options', [OptionController::class, 'store']);
    Route::put('options/{id}', [OptionController::class, 'update']);
    Route::delete('options/{id}', [OptionController::class, 'destroy']);


    // Kelas
    Route::get('kelas', [KelasController::class, 'index']);          // daftar kelas
    Route::get('kelas/{kelas}', [KelasController::class, 'show']);   // ⬅️ detail kelas (untuk /lecture/classes/:id)
    Route::post('kelas', [KelasController::class, 'store']);         // dosen buat kelas
    Route::post('kelas/join', [KelasController::class, 'join']);     // mahasiswa join kelas
    Route::put('kelas/{kelas}', [KelasController::class, 'update']); // update kelas (PUT/PATCH)
    Route::patch('kelas/{kelas}', [KelasController::class, 'update']); // optional PATCH alias
    Route::delete('kelas/{kelas}', [KelasController::class, 'destroy']); // hapus kelas


    // WEEK & RESOURCES
    Route::get('/kelas/{kelas}/weeks', [WeekController::class, 'index']);
    Route::post('/kelas/{kelas}/weeks', [WeekController::class, 'store']);
    Route::get('/weeks/{week}',        [WeekController::class, 'show']);
    Route::delete('/weeks/{week}',     [WeekController::class, 'destroy']);
    Route::put('/weeks/{week}',    [WeekController::class, 'update']); // ⬅️ NEW
    Route::patch('/weeks/{week}',  [WeekController::class, 'update']); // optional


    // CourseResource endpoints:
    Route::post('/weeks/{week}/resources',            [CourseResourceController::class, 'store']);
    Route::put('/course-resources/{resource}',        [CourseResourceController::class, 'update']);
    Route::delete('/course-resources/{resource}',     [CourseResourceController::class, 'destroy']);

    Route::get('/course-resources/{resource}/comments', [CommentController::class, 'index']);
    Route::post('/course-resources/{resource}/comments', [CommentController::class, 'store']);
    Route::put('/comments/{comment}',                  [CommentController::class, 'update']);
    Route::delete('/comments/{comment}',                  [CommentController::class, 'destroy']);
    Route::put('/comments/{comment}/score',            [CommentController::class, 'score']); // dosen only

    // Tandai resource selesai / batal selesai (mahasiswa)
    Route::put('/course-resources/{resource}/complete', [CourseResourceController::class, 'complete']);
    // (opsional, kalau mau izinkan POST _method=PUT)
    Route::post('/course-resources/{resource}/complete', [CourseResourceController::class, 'complete']);
    Route::put(
        '/course-resources/{resource}/complete',
        [\App\Http\Controllers\ProgressController::class, 'toggleResourceComplete']
    );

    // Quiz & Question & Option
    Route::apiResource('quizzes', QuizController::class);
    Route::apiResource('questions', QuestionController::class);
    Route::apiResource('options', OptionController::class);

    Route::post('quizzes/{quiz}/attempts/start', [QuizAttemptController::class, 'start']);
    Route::get('attempts/{attempt}',             [QuizAttemptController::class, 'show']);
    Route::post('attempts/{attempt}/answers',    [QuizAttemptController::class, 'saveAnswer']); // autosave
    Route::post('attempts/{attempt}/submit',     [QuizAttemptController::class, 'submit']);     // submit final

    Route::get('quizzes/{quiz}/attempts/me-latest', [QuizAttemptController::class, 'meLatest']);
    Route::post('/attempts/{attempt}/abort', [QuizAttemptController::class, 'abort']);

    Route::put('/course-resources/{resource}/complete', [CourseResourceController::class, 'complete']);

    Route::post('/assignments', [AssignmentController::class, 'store']);
    Route::get('/assignments/{assignment}', [AssignmentController::class, 'show']);
    Route::put('/assignments/{assignment}', [AssignmentController::class, 'update']);

    // mahasiswa
    Route::get('/assignments/{assignment}/submissions/me', [AssignmentSubmissionController::class, 'me']);
    Route::post('/assignments/{assignment}/submissions',    [AssignmentSubmissionController::class, 'storeOrUpdate']);

    // dosen (beri middleware role/policy sesuai aplikasi Anda)
    Route::get('/assignments/{assignment}/submissions',     [AssignmentSubmissionController::class, 'index']); // ->middleware('role:dosen');
    Route::patch('/assignment-submissions/{submission}/score', [AssignmentSubmissionController::class, 'updateScore']); // ->middleware('role:dosen');

    Route::get('/kelas/{id}/mahasiswa', [KelasController::class, 'students']);

 Route::get('/kelas/{id}/students', [KelasController::class, 'students']);                 // daftar mahasiswa
Route::get('/kelas/{id}/students/{sid}/report', [KelasController::class, 'studentReport']); // ringkas per mahasiswa (path param)
Route::get('/kelas/{id}/reports', [KelasController::class, 'studentReport']);
});
