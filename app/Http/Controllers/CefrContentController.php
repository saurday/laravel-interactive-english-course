<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CefrContentController extends Controller
{
    // List materi untuk satu level
    public function index(int $levelId)
    {
        // quote kolom `order` agar aman
        $items = DB::table('placement_level_contents')
            ->where('level_id', $levelId)
            ->when(Schema::hasColumn('placement_level_contents', 'order'), function ($q) {
                $q->orderBy(DB::raw('`order`'));
            })
            ->orderBy('id')
            ->get()
            ->map(function ($r) {
                return [
                    'id'        => $r->id,
                    'type'      => $r->type ?? 'composite',
                    'title'     => $r->title,
                    'text'      => $r->text,
                    'video_url' => $r->video_url,
                    'file_url'  => $r->file_url,
                ];
            });

        return response()->json(['resources' => $items]);
    }

    // Tambah materi
    public function store(int $levelId, Request $req)
    {
        $data = $req->validate([
            'title'     => 'required|string|max:255',
            'text'      => 'nullable|string',
            'video_url' => 'nullable|url',
            'file'      => 'nullable|file|max:20480',
            'file_url'  => 'nullable|url',
        ]);

        $fileUrl = $data['file_url'] ?? null;
        if ($req->hasFile('file')) {
            $path = $req->file('file')->store('cefr-files', 'public');
            $fileUrl = Storage::url($path);
        }

        $insert = [
            'level_id'  => $levelId,
            'title'     => $data['title'],
            'text'      => $data['text']      ?? null,
            'video_url' => $data['video_url'] ?? null,
            'file_url'  => $fileUrl,
        ];

        // kolom opsional: type, order, timestamps
        if (Schema::hasColumn('placement_level_contents', 'type')) {
            $insert['type'] = 'composite';
        }

        if (Schema::hasColumn('placement_level_contents', 'order')) {
            $nextOrder = (int) DB::table('placement_level_contents')
                ->where('level_id', $levelId)
                ->max('order') + 1;
            $insert['order'] = $nextOrder;
        }

        if (Schema::hasColumns('placement_level_contents', ['created_at', 'updated_at'])) {
            $insert['created_at'] = now();
            $insert['updated_at'] = now();
        }

        $id  = DB::table('placement_level_contents')->insertGetId($insert);
        $row = DB::table('placement_level_contents')->find($id);

        return response()->json([
            'id'        => $row->id,
            'type'      => $row->type ?? 'composite',
            'title'     => $row->title,
            'text'      => $row->text,
            'video_url' => $row->video_url,
            'file_url'  => $row->file_url,
        ], 201);
    }

    // Update materi
    public function update(int $id, Request $req)
    {
        $data = $req->validate([
            'title'     => 'required|string|max:255',
            'text'      => 'nullable|string',
            'video_url' => 'nullable|url',
            'file'      => 'nullable|file|max:20480',
            'file_url'  => 'nullable|url',
        ]);

        $row = DB::table('placement_level_contents')->where('id', $id)->first();
        abort_if(!$row, 404);

        $fileUrl = $data['file_url'] ?? $row->file_url;
        if ($req->hasFile('file')) {
            $path = $req->file('file')->store('cefr-files', 'public');
            $fileUrl = Storage::url($path);
        }

        $update = [
            'title'     => $data['title'],
            'text'      => $data['text']      ?? null,
            'video_url' => $data['video_url'] ?? null,
            'file_url'  => $fileUrl,
        ];
        if (Schema::hasColumn('placement_level_contents', 'updated_at')) {
            $update['updated_at'] = now();
        }

        DB::table('placement_level_contents')->where('id', $id)->update($update);

        $row = DB::table('placement_level_contents')->find($id);
        return response()->json([
            'id'        => $row->id,
            'type'      => $row->type ?? 'composite',
            'title'     => $row->title,
            'text'      => $row->text,
            'video_url' => $row->video_url,
            'file_url'  => $row->file_url,
        ]);
    }

    // Hapus materi
    public function destroy(int $id)
    {
        $deleted = DB::table('placement_level_contents')->where('id', $id)->delete();
        abort_if(!$deleted, 404);
        return response()->json(['ok' => true]);
    }
}
