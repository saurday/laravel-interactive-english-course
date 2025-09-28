<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AssignmentController extends Controller
{
    // POST /api/assignments
    public function store(\Illuminate\Http\Request $req)
    {
        $data = $req->validate([
            'kelas_id'     => ['required', 'exists:kelas,id'],  // sesuaikan nama tabel 'kelas'
            'title'        => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'due_date'     => ['nullable', 'string'],           // akan di-parse
            'max_score'    => ['nullable', 'integer', 'min:1'],
            'allow_file'   => ['nullable', 'boolean'],
        ]);

        $assignment = \App\Models\Assignment::create([
            'kelas_id'     => $data['kelas_id'],
            'title'        => $data['title'],
            'instructions' => $data['instructions'] ?? null,
            'due_date'     => !empty($data['due_date']) ? \Carbon\Carbon::parse($data['due_date']) : null,
            'max_score'    => $data['max_score'] ?? 100,
            'allow_file'   => $data['allow_file'] ?? true,
            'created_by'   => optional($req->user())->id,
        ]);

        return response()->json(['assignment' => $assignment], 201);
    }

    public function show(Assignment $assignment)
    {
        return response()->json(['assignment' => $assignment]);
    }

    // PUT /api/assignments/{assignment}
    public function update(Request $req, Assignment $assignment)
    {
        $data = $req->validate([
            'title'        => ['sometimes', 'required', 'string', 'max:255'],
            'instructions' => ['sometimes', 'nullable', 'string'],
            'due_date'     => ['sometimes', 'nullable', 'string'],
            'max_score'    => ['sometimes', 'nullable', 'integer', 'min:1'],
            'allow_file'   => ['sometimes', 'nullable', 'boolean'],
        ]);

        $patch = [];
        if ($req->has('title'))        $patch['title']        = $data['title'];
        if ($req->has('instructions')) $patch['instructions'] = $data['instructions'] ?? null;
        if ($req->has('due_date'))     $patch['due_date']     = !empty($data['due_date']) ? Carbon::parse($data['due_date']) : null;
        if ($req->has('max_score'))    $patch['max_score']    = $data['max_score'] ?? 100;
        if ($req->has('allow_file'))   $patch['allow_file']   = (bool)($data['allow_file'] ?? true);

        $assignment->update($patch);

        return response()->json(['assignment' => $assignment->fresh()]);
    }
}
