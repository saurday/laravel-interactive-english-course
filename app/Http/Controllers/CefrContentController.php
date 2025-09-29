<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CefrContentController extends Controller
{
    // List materi untuk satu level
    public function index(int $levelId)
    {
        $items = DB::table('placement_level_contents')
            ->where('level_id', $levelId)
            ->orderBy('order')
            ->orderBy('id')
            ->get()
            ->map(function ($r) {
                return [
                    'id'        => $r->id,
                    'type'      => 'composite',     // disesuaikan dengan FE
                    'title'     => $r->title,
                    'text'      => $r->text,
                    'video_url' => $r->video_url,
                    'file_url'  => $r->file_url,
                ];
            });

        // FE membaca "resources" ATAU array langsung — aman kita kirim "resources"
        return response()->json(['resources' => $items]);
    }

    // Tambah materi (dipanggil FE via POST /api/cefr-levels/{id}/resources)
    public function store(int $levelId, Request $req)
    {
        $data = $req->validate([
            'title'     => 'required|string|max:255',
            'text'      => 'nullable|string',
            'video_url' => 'nullable|string',
            'file'      => 'nullable|file',
            'file_url'  => 'nullable|string',
        ]);

        $fileUrl = $data['file_url'] ?? null;
        if ($req->hasFile('file')) {
            $path = $req->file('file')->store('cefr-files', 'public');
            $fileUrl = asset('storage/' . $path);
        }

        $order = (int) DB::table('placement_level_contents')->where('level_id', $levelId)->max('order') + 1;

        $id = DB::table('placement_level_contents')->insertGetId([
            'level_id'  => $levelId,
            'order'     => $order,
            'type'      => 'composite',
            'title'     => $data['title'],
            'text'      => $data['text']      ?? null,
            'video_url' => $data['video_url'] ?? null,
            'file_url'  => $fileUrl,
            'created_at'=> now(),
            'updated_at'=> now(),
        ]);

        $row = DB::table('placement_level_contents')->find($id);

        return response()->json([
            'id'        => $row->id,
            'type'      => 'composite',
            'title'     => $row->title,
            'text'      => $row->text,
            'video_url' => $row->video_url,
            'file_url'  => $row->file_url,
        ], 201);
    }

    // (opsional) Update materi
    public function update(int $id, Request $req)
    {
        $data = $req->validate([
            'title'     => 'required|string|max:255',
            'text'      => 'nullable|string',
            'video_url' => 'nullable|string',
            'file'      => 'nullable|file',
            'file_url'  => 'nullable|string',
        ]);

        $row = DB::table('placement_level_contents')->where('id',$id)->first();
        abort_if(!$row, 404);

        $fileUrl = $data['file_url'] ?? $row->file_url;
        if ($req->hasFile('file')) {
            $path = $req->file('file')->store('cefr-files', 'public');
            $fileUrl = asset('storage/' . $path);
        }

        DB::table('placement_level_contents')->where('id',$id)->update([
            'title'     => $data['title'],
            'text'      => $data['text']      ?? null,
            'video_url' => $data['video_url'] ?? null,
            'file_url'  => $fileUrl,
            'updated_at'=> now(),
        ]);

        $row = DB::table('placement_level_contents')->find($id);
        return response()->json([
            'id'        => $row->id,
            'type'      => 'composite',
            'title'     => $row->title,
            'text'      => $row->text,
            'video_url' => $row->video_url,
            'file_url'  => $row->file_url,
        ]);
    }

    // (opsional) Hapus materi
    public function destroy(int $id)
    {
        $deleted = DB::table('placement_level_contents')->where('id',$id)->delete();
        abort_if(!$deleted, 404);
        return response()->json(['ok' => true]);
    }
}
