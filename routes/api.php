<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\AdminStatsController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AssignmentSubmissionController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\OptionController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\WeekController;
use App\Http\Controllers\CourseResourceController;
use App\Http\Controllers\QuizAttemptController;
use App\Http\Controllers\PlacementTestController;
use App\Http\Controllers\CefrLevelController;
use App\Http\Controllers\CefrContentController;
use App\Http\Controllers\AdminProgressController;
use App\Http\Controllers\PlacementAdminController;

Route::get('/healthz', fn () => response('OK', 200));

// Auth
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // ========================= Users =========================
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);
    Route::post('users', [UserController::class, 'store']);

    // ========================= Q/A/Options =========================
    Route::get('questions', [QuestionController::class, 'index']);
    Route::get('questions/{id}', [QuestionController::class, 'show']);
    Route::post('questions', [QuestionController::class, 'store']);
    Route::put('questions/{id}', [QuestionController::class, 'update']);
    Route::delete('questions/{id}', [QuestionController::class, 'destroy']);

    Route::get('answers', [AnswerController::class, 'index']);
    Route::get('answers/{id}', [AnswerController::class, 'show']);
    Route::post('answers', [AnswerController::class, 'store']);
    Route::put('answers/{id}', [AnswerController::class, 'update']);
    Route::delete('answers/{id}', [AnswerController::class, 'destroy']);

    Route::get('options', [OptionController::class, 'index']);
    Route::get('options/{id}', [OptionController::class, 'show']);
    Route::post('options', [OptionController::class, 'store']);
    Route::put('options/{id}', [OptionController::class, 'update']);
    Route::delete('options/{id}', [OptionController::class, 'destroy']);

    // ========================= Kelas =========================
    Route::get('kelas', [KelasController::class, 'index']);               // daftar kelas
    Route::get('kelas/{kelas}', [KelasController::class, 'show']);        // detail kelas
    Route::post('kelas', [KelasController::class, 'store']);              // dosen buat kelas
    Route::post('kelas/join', [KelasController::class, 'join']);          // mahasiswa join kelas
    Route::put('kelas/{kelas}', [KelasController::class, 'update']);      // update kelas
    Route::patch('kelas/{kelas}', [KelasController::class, 'update']);    // alias PATCH
    Route::delete('kelas/{kelas}', [KelasController::class, 'destroy']);  // hapus kelas

    // ========================= Weeks & Course Resources =========================
    Route::get('/kelas/{kelas}/weeks', [WeekController::class, 'index']);
    Route::post('/kelas/{kelas}/weeks', [WeekController::class, 'store']);
    Route::get('/weeks/{week}', [WeekController::class, 'show']);
    Route::delete('/weeks/{week}', [WeekController::class, 'destroy']);
    Route::put('/weeks/{week}', [WeekController::class, 'update']);
    Route::patch('/weeks/{week}', [WeekController::class, 'update']);

    // CourseResource endpoints
    Route::post('/weeks/{week}/resources', [CourseResourceController::class, 'store']);
    Route::put('/course-resources/{resource}', [CourseResourceController::class, 'update']);
    Route::delete('/course-resources/{resource}', [CourseResourceController::class, 'destroy']);

    Route::get('/course-resources/{resource}/comments', [CommentController::class, 'index']);
    Route::post('/course-resources/{resource}/comments', [CommentController::class, 'store']);
    Route::put('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
    Route::put('/comments/{comment}/score', [CommentController::class, 'score']); // dosen only

    // Tandai resource selesai / batal selesai (mahasiswa)
    Route::put('/course-resources/{resource}/complete', [CourseResourceController::class, 'complete']);
    // izinkan POST + _method=PUT
    Route::post('/course-resources/{resource}/complete', [CourseResourceController::class, 'complete']);
    // (baris berikut jika kamu memang punya ProgressController versi lain)
    Route::put(
        '/course-resources/{resource}/complete',
        [\App\Http\Controllers\ProgressController::class, 'toggleResourceComplete']
    );

    // ========================= Quiz =========================
    Route::apiResource('quizzes', QuizController::class);
    Route::apiResource('questions', QuestionController::class);
    Route::apiResource('options', OptionController::class);

    Route::post('quizzes/{quiz}/attempts/start', [QuizAttemptController::class, 'start']);
    Route::get('attempts/{attempt}', [QuizAttemptController::class, 'show']);
    Route::post('attempts/{attempt}/answers', [QuizAttemptController::class, 'saveAnswer']);  // autosave
    Route::post('attempts/{attempt}/submit', [QuizAttemptController::class, 'submit']);       // submit final

    Route::get('quizzes/{quiz}/attempts/me-latest', [QuizAttemptController::class, 'meLatest']);
    Route::post('/attempts/{attempt}/abort', [QuizAttemptController::class, 'abort']);

    Route::put('/course-resources/{resource}/complete', [CourseResourceController::class, 'complete']);

    // ========================= Assignments =========================
    Route::post('/assignments', [AssignmentController::class, 'store']);
    Route::get('/assignments/{assignment}', [AssignmentController::class, 'show']);
    Route::put('/assignments/{assignment}', [AssignmentController::class, 'update']);

    // mahasiswa
    Route::get('/assignments/{assignment}/submissions/me', [AssignmentSubmissionController::class, 'me']);
    Route::post('/assignments/{assignment}/submissions', [AssignmentSubmissionController::class, 'storeOrUpdate']);

    // dosen
    Route::get('/assignments/{assignment}/submissions', [AssignmentSubmissionController::class, 'index']); // ->middleware('role:dosen');
    Route::patch('/assignment-submissions/{submission}/score', [AssignmentSubmissionController::class, 'updateScore']); // ->middleware('role:dosen');

    Route::get('/kelas/{id}/mahasiswa', [KelasController::class, 'students']);
    Route::get('/kelas/{id}/students', [KelasController::class, 'students']);
    Route::get('/kelas/{id}/students/{sid}/report', [KelasController::class, 'studentReport']);
    Route::get('/kelas/{id}/reports', [KelasController::class, 'studentReport']);

    // ========================= Placement Test =========================
    Route::get('/placement/state', [PlacementTestController::class, 'state']);
    Route::post('/placement/start', [PlacementTestController::class, 'start']);
    Route::get('/placement/attempts/{id}', [PlacementTestController::class, 'show']);
    Route::post('/placement/attempts/{id}/answer', [PlacementTestController::class, 'answer']);
    Route::post('/placement/attempts/{id}/submit', [PlacementTestController::class, 'submit']);
    Route::get('/placement/attempts/{id}/review', [PlacementTestController::class, 'review']);

    // ========================= CEFR levels + contents =========================
    Route::prefix('cefr-levels')->group(function () {
        // GET /api/cefr-levels
        Route::get('/', [CefrLevelController::class, 'index']);

        // GET /api/cefr-levels/by-code/A1
        Route::get('/by-code/{code}', [CefrLevelController::class, 'showByCode']);

        // GET /api/cefr-levels/{level}  (numerik id level)
        Route::get('{level}', [CefrLevelController::class, 'show'])->whereNumber('level');

        // LIST & CREATE content untuk suatu level
        // GET /api/cefr-levels/{level}/resources
        Route::get('{level}/resources', [CefrContentController::class, 'index'])->whereNumber('level');

        // POST /api/cefr-levels/{level}/resources
        Route::post('{level}/resources', [CefrContentController::class, 'store'])->whereNumber('level');
    });

    // UPDATE / DELETE per resource id
    // PUT /api/cefr-resources/{id}
    Route::put('cefr-resources/{id}', [CefrContentController::class, 'update'])->whereNumber('id');

    // Alias untuk FE yang mengirim POST + _method=PUT
    // POST /api/cefr-resources/{id}
    Route::post('cefr-resources/{id}', [CefrContentController::class, 'update'])->whereNumber('id');

    // DELETE /api/cefr-resources/{id}
    Route::delete('cefr-resources/{id}', [CefrContentController::class, 'destroy'])->whereNumber('id');

    // ========================= Admin =========================
    Route::get('admin/students/progress', [AdminProgressController::class, 'index']);
    Route::get('course-resources', [CourseResourceController::class, 'index']);
    Route::get('/placement/contents', [PlacementAdminController::class, 'index']);

    Route::prefix('admin')->middleware('admin.only')->group(function () {
        Route::get('/stats', [AdminStatsController::class, 'index']);
    });
});
