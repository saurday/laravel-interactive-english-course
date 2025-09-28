<?php

namespace App\Http\Controllers;

use App\Models\CourseResource;
use App\Models\Progress;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function toggleResourceComplete(Request $request, CourseResource $resource)
    {
        $data = $request->validate([
            'completed' => ['required','boolean'],
        ]);

        $userId = $request->user()->id;

        // Simpan progress per resource (100 atau 0)
        $progress = Progress::updateOrCreate(
            ['mahasiswa_id' => $userId, 'resource_id' => $resource->id],
            [
                'percentage' => $data['completed'] ? 100 : 0,
                'completed'  => $data['completed'],
            ]
        );

        return response()->json([
            'ok' => true,
            'progress' => $progress,
        ]);
    }
}
