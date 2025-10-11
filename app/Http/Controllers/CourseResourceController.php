<?php

namespace App\Http\Controllers;

use App\Models\CourseResource;
use App\Models\Week;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\Progress;

class CourseResourceController extends Controller
{
    public function index(Request $request)
{
    $query = CourseResource::with(['week:id,week_number', 'quiz:id,title', 'assignment:id,title'])
        ->orderByDesc('created_at');

    if ($type = $request->query('type')) {
        $query->where('type', $type);
    }
    if ($weekId = $request->query('week_id')) {
        $query->where('week_id', $weekId);
    }

    $list = $query->get();

    $out = [];
    foreach ($list as $r) {
        $out[] = $this->resourcePayload($r);
    }

    return response()->json([
        'resources' => $out,
        'total'     => count($out),
    ]);
}

    // POST /api/weeks/{week}/resources
    public function store(Request $request, $weekId)
    {
        $rules = [
            'type'          => ['required', Rule::in(['text','video','file','quiz','composite','assignment'])],
            'title'         => 'nullable|string|max:255',
            'text'          => 'nullable|string',
            'video_url'     => 'nullable|url',
            'file_url'      => 'nullable|url',
            'file'          => 'nullable|file|max:20480',
            'quiz_id'       => 'nullable|exists:quizzes,id',
            'assignment_id' => 'nullable|exists:assignments,id',
        ];

        if ($request->input('type') === 'quiz') {
            $rules['quiz_id'] = 'required|exists:quizzes,id';
        }
        if ($request->input('type') === 'assignment') {
            $rules['assignment_id'] = 'required|exists:assignments,id';
        }

        $data = $request->validate($rules);

        $week = Week::findOrFail($weekId);

        $payload = [
            'week_id'       => $week->id,
            'type'          => $data['type'],
            'title'         => $data['title'] ?? null,
            'text'          => null,
            'video_url'     => null,
            'file_path'     => null,   // bisa berisi path lokal ATAU URL eksternal
            'quiz_id'       => null,
            'assignment_id' => null,
            'created_by'    => optional($request->user())->id,
        ];

        switch ($data['type']) {
            case 'text':
                $payload['text'] = $data['text'] ?? null;
                break;

            case 'video':
                $payload['video_url'] = $data['video_url'] ?? null;
                break;

            case 'file':
                if ($request->hasFile('file')) {
                    $payload['file_path'] = $request->file('file')->store('materials', 'public');
                } elseif (!empty($data['file_url'])) {
                    // simpan URL eksternal apa adanya di file_path
                    $payload['file_path'] = $data['file_url'];
                }
                break;

            case 'quiz':
                $payload['quiz_id'] = $data['quiz_id'];
                break;

            case 'assignment':
                $payload['assignment_id'] = $data['assignment_id'];
                break;

            case 'composite':
                $payload['text']      = $data['text'] ?? null;
                $payload['video_url'] = $data['video_url'] ?? null;
                if ($request->hasFile('file')) {
                    $payload['file_path'] = $request->file('file')->store('materials', 'public');
                } elseif (!empty($data['file_url'])) {
                    $payload['file_path'] = $data['file_url'];
                }
                break;
        }

        $res = CourseResource::create($payload)->loadMissing(['quiz','assignment','week']);

        return response()->json([
            'resource' => $this->resourcePayload($res),
        ], 201);
    }

    // PUT /api/course-resources/{resource}
    public function update(Request $request, $id)
    {
        $res = CourseResource::findOrFail($id);

        $rules = [
            'type'          => ['sometimes', Rule::in(['text','video','file','quiz','composite','assignment'])],
            'title'         => 'sometimes|nullable|string|max:255',
            'text'          => 'sometimes|nullable|string',
            'video_url'     => 'sometimes|nullable|url',
            'file_url'      => 'sometimes|nullable|url',
            'file'          => 'sometimes|nullable|file|max:20480',
            'quiz_id'       => 'sometimes|nullable|exists:quizzes,id',
            'assignment_id' => 'sometimes|nullable|exists:assignments,id',
        ];

        if ($request->input('type') === 'quiz') {
            $rules['quiz_id'] = 'required|exists:quizzes,id';
        }
        if ($request->input('type') === 'assignment') {
            $rules['assignment_id'] = 'required|exists:assignments,id';
        }

        $data = $request->validate($rules);

        $patch = [];
        if ($request->has('title'))         $patch['title']         = $data['title'] ?? null;
        if ($request->has('type'))          $patch['type']          = $data['type'];
        if ($request->has('text'))          $patch['text']          = $data['text'] ?? null;
        if ($request->has('video_url'))     $patch['video_url']     = $data['video_url'] ?? null;
        if ($request->has('quiz_id'))       $patch['quiz_id']       = $data['quiz_id'] ?? null;
        if ($request->has('assignment_id')) $patch['assignment_id'] = $data['assignment_id'] ?? null;

        // ganti file: bisa upload baru atau set URL (atau kosongkan)
        $replacingWithUpload = $request->hasFile('file');
        $replacingWithUrl    = $request->has('file_url');

        if ($replacingWithUpload) {
            $newPath = $request->file('file')->store('materials', 'public');
            $patch['file_path'] = $newPath;
        } elseif ($replacingWithUrl) {
            $patch['file_path'] = $data['file_url'] ?? null; // boleh kosongkan
        }

        // normalisasi by type (hapus field yang tidak relevan)
        if (isset($patch['type'])) {
            switch ($patch['type']) {
                case 'text':
                    $patch['video_url']     = null;
                    $patch['file_path']     = null;
                    $patch['quiz_id']       = null;
                    $patch['assignment_id'] = null;
                    break;
                case 'video':
                    $patch['text']          = null;
                    $patch['file_path']     = null;
                    $patch['quiz_id']       = null;
                    $patch['assignment_id'] = null;
                    break;
                case 'file':
                    $patch['text']          = null;
                    $patch['video_url']     = null;
                    $patch['quiz_id']       = null;
                    $patch['assignment_id'] = null;
                    break;
                case 'quiz':
                    $patch['text']          = null;
                    $patch['video_url']     = null;
                    $patch['file_path']     = null;
                    $patch['assignment_id'] = null;
                    break;
                case 'assignment':
                    $patch['text']          = null;
                    $patch['video_url']     = null;
                    $patch['file_path']     = null;
                    $patch['quiz_id']       = null;
                    break;
                case 'composite':
                    // biarkan semua field (text/video/file) apa adanya
                    $patch['quiz_id']       = null;
                    $patch['assignment_id'] = null;
                    break;
            }
        }

        // Hapus file lama di storage jika diganti atau jika tipe baru tidak memakai file
        $hadLocalFile = $res->file_path && !preg_match('#^https?://#i', $res->file_path);
        $typeChanged  = isset($patch['type']);

        if ($hadLocalFile && ($replacingWithUpload || $replacingWithUrl ||
            ($typeChanged && in_array(($patch['type'] ?? ''), ['text','video','quiz','assignment'], true)) ||
            ($replacingWithUrl && empty($patch['file_path'])))) {
            Storage::disk('public')->delete($res->file_path);
        }

        $res->update($patch);

        $res->loadMissing(['quiz','assignment','week']);

        return response()->json([
            'resource' => $this->resourcePayload($res),
        ], 200);
    }

    // DELETE /api/course-resources/{resource}
    public function destroy(CourseResource $resource)
    {
        $fp = $resource->file_path;
        if ($fp && !preg_match('#^https?://#i', $fp)) {
            Storage::disk('public')->delete($fp);
        }
        $resource->delete();

        return response()->json(['status' => 'ok']);
    }

    // POST /api/course-resources/{resource}/complete
    public function complete(Request $request, CourseResource $resource)
    {
        $data = $request->validate([
            'completed' => ['required','boolean'],
        ]);

        $user = $request->user();

        $progress = Progress::updateOrCreate(
            [
                'user_id'            => $user->id,
                'course_resource_id' => $resource->id,
            ],
            [
                'completed'    => $data['completed'],
                'completed_at' => $data['completed'] ? now() : null,
            ]
        );

        return response()->json([
            'ok'       => true,
            'progress' => $progress,
        ]);
    }

    /* ---------- Helper untuk bentuk respons yang konsisten ---------- */
    private function resourcePayload(CourseResource $r): array
    {
        $r->loadMissing(['quiz','assignment','week']);

        return [
            'id'            => $r->id,
            'week_id'       => $r->week_id,
            'week_number'   => optional($r->week)->week_number ?? $r->week_id,
            'type'          => $r->type,
            'title'         => $r->title,
            'text'          => $r->text,
            'video_url'     => $r->video_url,
            'file_url'      => $r->file_url,      // via accessor di model
            'quiz_id'       => $r->quiz_id,
            'quiz'          => $r->relationLoaded('quiz') ? $r->quiz : null,
            'assignment_id' => $r->assignment_id,
            'assignment'    => $r->relationLoaded('assignment') ? $r->assignment : null,
        ];
    }
}
