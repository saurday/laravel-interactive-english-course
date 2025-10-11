<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class AdminStatsController extends Controller
{
    /**
     * GET /api/admin/stats
     * Return:
     * {
     *   total, students, lecturers, admins,
     *   weekly: [{label, value}],  // 7 hari terakhir
     *   latest: [{id,name,email,role,created_at}]
     * }
     */
    public function index(Request $request)
    {
        $auth = $request->user();
        if (!$auth || $auth->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // ---- COUNTS ----
        $total     = (int) User::count();
        $students  = (int) User::where('role', 'mahasiswa')->count();
        $lecturers = (int) User::where('role', 'dosen')->count();
        $admins    = (int) User::where('role', 'admin')->count();

        // ---- WEEKLY SIGNUPS (7 hari terakhir) ----
        // gunakan timezone dari query ?tz=Asia/Jakarta jika mau, default ke app.timezone
        $tz = $request->query('tz', config('app.timezone'));
        $nowTz   = Carbon::now($tz)->startOfDay();
        $startTz = $nowTz->copy()->subDays(6);            // 6 hari ke belakang (total 7)

        // Query grup per tanggal (UTC di DB), lalu dipetakan ke bucket harian
        $startUtc = $startTz->copy()->timezone('UTC');    // created_at tersimpan UTC
        $rows = DB::table('users')
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->where('created_at', '>=', $startUtc)
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('c', 'd')                              // ["2025-10-02" => 5, ...]
            ->toArray();

        $weekly = [];
        for ($i = 0; $i < 7; $i++) {
            $dayTz  = $startTz->copy()->addDays($i);      // tanggal di TZ setempat
            $keyUtc = $dayTz->copy()->timezone('UTC')->toDateString();
            $weekly[] = [
                'label' => $dayTz->isoFormat('dd'),       // Mo, Tu, We (singkat)
                'value' => (int) ($rows[$keyUtc] ?? 0),
            ];
        }

        // ---- LATEST USERS (6 terbaru) ----
        $latest = User::select('id', 'name', 'email', 'role', 'created_at')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        return response()->json([
            'total'     => $total,
            'students'  => $students,
            'lecturers' => $lecturers,
            'admins'    => $admins,
            'weekly'    => $weekly,
            'latest'    => $latest,
        ], 200);
    }
}
