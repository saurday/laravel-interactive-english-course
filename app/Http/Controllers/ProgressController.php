<?php

// app/Http/Controllers/ProgressController.php
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

        $progress = Progress::updateOrCreate(
            ['user_id' => $userId, 'course_resource_id' => $resource->id],
            [
                'completed'    => $data['completed'],
                'completed_at' => $data['completed'] ? now() : null,
            ]
        );

        return response()->json(['ok' => true, 'progress' => $progress]);
    }
}
