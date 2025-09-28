<?php

namespace App\Http\Controllers;

use App\Models\Week;
use App\Models\Kelas;
use App\Models\CourseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class WeekController extends Controller
{
    /** Buat URL publik untuk file di disk 'public' atau kembalikan apa adanya jika sudah absolut */
    private function fileUrl(?string $path): ?string
    {
        if (!$path) return null;
        // kalau sudah http/https/ protocol-relative
        if (preg_match('#^(?:https?:)?//#i', $path)) return $path;

        // Alternatif stabil (tidak bikin Intelephense error)
        return asset('storage/' . ltrim($path, '/'));

        // Kalau mau tetap pakai Storage, sebenarnya ini juga valid runtime:
        // return Storage::disk('public')->url($path);
    }

    // GET /api/kelas/{kelas}/weeks
    public function index(Request $request, Kelas $kelas)
    {
        $userId = $request->user()->id;



        // app/Http/Controllers/WeekController.php

        // di index():
        $weeks = Week::with(['resources' => function ($q) use ($userId) {
            $q->with(['quiz', 'assignment'])   // <— TAMBAH assignment
                ->withCount([
                    'progresses as completed_count' => function ($qq) use ($userId) {
                        $qq->where('user_id', $userId)->where('completed', 1);
                    }
                ])
                ->orderBy('sort')->orderBy('id');
        }])
            ->where('kelas_id', $kelas->id)
            ->orderBy('week_number')
            ->get();

        // dekorasi:
        foreach ($weeks as $week) {
            foreach ($week->resources as $r) {
                $r->file_url    = $r->file_url ?? $this->fileUrl($r->file_path);
                $r->week_number = $week->week_number;
                $r->completed   = ($r->completed_count ?? 0) > 0;
                unset($r->completed_count);

                // >>> expose field assignment agar FE bisa langsung pakai
                if ($r->type === 'assignment' && $r->assignment) {
                    $r->assignment_id = $r->assignment->id;
                    // kirim meta yang dibutuhkan FE
                    $r->instructions  = $r->assignment->instructions;
                    $r->due_date      = optional($r->assignment->due_date)->toISOString();
                    $r->max_score     = $r->assignment->max_score;
                    $r->allow_file    = (bool)$r->assignment->allow_file;
                }
            }
        }


        return response()->json($weeks);
    }

    // POST /api/kelas/{kelas}/weeks
    public function store(Request $req, Kelas $kelas)
    {
        $data = $req->validate([
            'week_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('weeks', 'week_number')
                    ->where(fn($q) => $q->where('kelas_id', $kelas->id)),
            ],
            'resources'                 => ['nullable', 'array'],
            'resources.*.type'          => ['required', 'in:file,video,text'],
            'resources.*.title'         => ['nullable', 'string', 'max:255'],
            'resources.*.text'          => ['nullable', 'string'],
            'resources.*.video_url'     => ['nullable', 'url'],
            'resources.*.file'          => ['nullable', 'file', 'mimes:pdf,ppt,pptx,doc,docx', 'max:20480'],
        ]);

        $week = Week::create([
            'kelas_id'    => $kelas->id,
            'week_number' => $data['week_number'],
        ]);

        $created = [];
        foreach (($data['resources'] ?? []) as $i => $r) {
            $payload = [
                'week_id'    => $week->id,
                'type'       => $r['type'],
                'title'      => $r['title'] ?? null,
                'text'       => $r['type'] === 'text'  ? ($r['text'] ?? null) : null,
                'video_url'  => $r['type'] === 'video' ? ($r['video_url'] ?? null) : null,
                'sort'       => $i,
                'created_by' => $req->user()?->id,
            ];

            if ($r['type'] === 'file' && $req->hasFile("resources.$i.file")) {
                $path = $req->file("resources.$i.file")
                    ->store("course-files/{$kelas->id}/week-{$week->week_number}", 'public');
                $payload['file_path'] = $path;
            }

            $created[] = CourseResource::create($payload);
        }

        $created = CourseResource::whereIn('id', collect($created)->pluck('id'))->get();

        // tambahkan file_url & week_number untuk respons
        foreach ($created as $r) {
            $r->file_url    = $this->fileUrl($r->file_path);
            $r->week_number = $week->week_number;
        }

        return response()->json([
            'week'      => $week,
            'resources' => $created,
        ], 201);
    }

    // GET /api/weeks/{week}
    public function show(Request $request, Week $week)
    {
        // di show():
        $week->load(['resources' => function ($q) {
            $q->with(['quiz', 'assignment'])->orderBy('sort')->orderBy('id');  // <— assignment
        }]);

        foreach ($week->resources as $r) {
            $r->file_url    = $r->file_url ?? $this->fileUrl($r->file_path);
            $r->week_number = $r->week_number ?? $week->week_number;

            if ($r->type === 'assignment' && $r->assignment) {
                $r->assignment_id = $r->assignment->id;
                $r->instructions  = $r->assignment->instructions;
                $r->due_date      = optional($r->assignment->due_date)->toISOString();
                $r->max_score     = $r->assignment->max_score;
                $r->allow_file    = (bool)$r->assignment->allow_file;
            }
        }


        return $week;
    }

    // DELETE /api/weeks/{week}
    public function destroy(Week $week)
    {
        foreach ($week->resources as $r) {
            if ($r->file_path) Storage::disk('public')->delete($r->file_path);
        }
        $week->delete();
        return response()->noContent();
    }

    // PUT/PATCH /api/weeks/{week}
    public function update(Request $request, Week $week)
    {
        $data = $request->validate([
            'week_number' => ['required', 'integer', 'min:1'],
        ]);

        $exists = Week::where('kelas_id', $week->kelas_id)
            ->where('id', '<>', $week->id)
            ->where('week_number', $data['week_number'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Week number already exists in this class'], 422);
        }

        $week->update(['week_number' => $data['week_number']]);

        return response()->json(['week' => $week->fresh()]);
    }
}
