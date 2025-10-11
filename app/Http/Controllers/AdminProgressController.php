<?php

// app/Http/Controllers/AdminProgressController.php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CourseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminProgressController extends Controller
{
    public function index(Request $request)
    {
        $auth = $request->user();
        if (!$auth || $auth->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $totalResources = CourseResource::count(); // denominator global
        // Jika ingin “per kelas”, nanti denominator diganti join ke resource kelas yang diikuti mahasiswa.

        $rows = User::where('users.role', 'mahasiswa')
            ->leftJoin('progress', 'users.id', '=', 'progress.user_id')
            ->groupBy('users.id', 'users.name', 'users.email', 'users.created_at')
            ->orderBy('users.name')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.created_at',
                DB::raw('SUM(CASE WHEN progress.completed = 1 THEN 1 ELSE 0 END) as done_cnt')
            )
            ->get()
            ->map(function ($u) use ($totalResources) {
                $done = (int)($u->done_cnt ?? 0);
                $pct  = $totalResources > 0 ? round($done / $totalResources * 100) : null;

                return [
                    'id'         => $u->id,
                    'name'       => $u->name,
                    'email'      => $u->email,
                    'created_at' => $u->created_at,
                    'progress'   => [
                        'done'    => $done,
                        'total'   => $totalResources,
                        'percent' => $pct, // null = belum ada data/denominator 0
                    ],
                ];
            });

        return response()->json([
            'total_resources' => $totalResources,
            'students'        => $rows,
        ]);
    }
}
