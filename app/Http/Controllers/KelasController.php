<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class KelasController extends Controller
{
    // GET daftar kelas (tergantung role)
    public function index(Request $request)
    {
        $user = $request->user();

        $q = Kelas::with('dosen:id,name');

        if ($user->role === 'dosen') {
            // hanya kelas milik dosen tsb
            $q->where('dosen_id', $user->id);
        } elseif ($user->role === 'mahasiswa') {
            // hanya kelas yang diikuti mahasiswa tsb
            // untuk mahasiswa
            $q->whereHas('mahasiswa', function ($qq) use ($user) {
                $qq->whereKey($user->id); // lebih aman daripada where('users.id', ...)
            });
        } else {
            // admin boleh lihat semua (opsional: batasi sesuai kebutuhan)
        }

        return response()->json(
            $q->orderByDesc('created_at')->get(),
            200
        );
    }

    public function show(Request $request, \App\Models\Kelas $kelas)
    {
        // (opsional) batasi akses detail:
        // - dosen: hanya kelas miliknya
        // - mahasiswa: hanya kelas yang diikuti
        $user = $request->user();
        if ($user->role === 'dosen' && $kelas->dosen_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        if ($user->role === 'mahasiswa' && !$kelas->mahasiswa()->where('users.id', $user->id)->exists()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $kelas->load('dosen:id,name');

        return response()->json($kelas, 200);
    }

    // POST buat kelas baru (dosen)
    public function store(Request $request)
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:150',
        ]);

        do {
            $kode = Str::upper(Str::random(6));
        } while (Kelas::where('kode_kelas', $kode)->exists());

        $kelas = Kelas::create([
            'nama_kelas' => $request->nama_kelas,
            'kode_kelas' => $kode,
            'dosen_id'   => Auth::id(),
        ]);

        // tampilkan juga nama dosen pada respons
        $kelas->load('dosen:id,name');

        return response()->json($kelas, 201);
    }

    // PUT update kelas (hanya pemilik)
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:150',
        ]);

        $kelas = Kelas::findOrFail($id);

        if ($kelas->dosen_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $kelas->update([
            'nama_kelas' => $request->nama_kelas,
        ]);

        $kelas->load('dosen:id,name');

        return response()->json($kelas);
    }

    // DELETE kelas (hanya pemilik)
    public function destroy($id)
    {
        $kelas = Kelas::findOrFail($id);

        if ($kelas->dosen_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $kelas->delete();

        return response()->json(['message' => 'Kelas berhasil dihapus']);
    }

    // POST mahasiswa join kelas pakai kode
    public function join(Request $request)
    {
        $request->validate([
            'kode_kelas' => 'required|string',
        ]);

        $user = $request->user();
        if ($user->role !== 'mahasiswa') {
            return response()->json(['message' => 'Only students can join'], 403);
        }

        $kelas = Kelas::where('kode_kelas', $request->kode_kelas)->firstOrFail();

        // sudah bergabung?
        $already = $kelas->mahasiswa()->where('users.id', $user->id)->exists();
        if ($already) {
            return response()->json(['message' => 'Sudah tergabung pada kelas ini'], 409);
        }

        $kelas->mahasiswa()->attach($user->id);

        $kelas->load('dosen:id,name');

        return response()->json([
            'message' => 'Berhasil bergabung kelas',
            'kelas'   => $kelas
        ], 200);
    }

public function students($id)
{
    $rows = DB::table('kelas_mahasiswa as km')
        ->join('users as u', 'u.id', '=', 'km.mahasiswa_id')
        ->where('km.kelas_id', $id)
        ->orderBy('u.name')
        ->get([
            'km.id as pivot_id',
            'u.id as user_id',
            'u.name',
            'u.email',
            'km.joined_at',
        ]);

    return response()->json($rows);
}

   public function studentReport($id, Request $request, $sid = null)
    {
        $studentId = $sid ?? (int)$request->query('student_id');
        if (!$studentId) {
            return response()->json(['message' => 'student_id is required'], 422);
        }

        // pastikan student memang terdaftar di kelas ini
        $exists = DB::table('kelas_mahasiswa')
            ->where('kelas_id', $id)
            ->where('mahasiswa_id', $studentId)
            ->exists();

        if (!$exists) {
            return response()->json(['message' => 'Student not in this class'], 404);
        }

        // Ambil semua resource id untuk kelas ini
        $resourceIds = DB::table('course_resources as cr')
            ->join('weeks as w', 'w.id', '=', 'cr.week_id')
            ->where('w.kelas_id', $id)
            ->pluck('cr.id');

        $totalResources = $resourceIds->count();

        // Progress student pada resource tsb
        $doneRows = DB::table('progress')
            ->whereIn('course_resource_id', $resourceIds)
            ->where('user_id', $studentId)
            ->where('completed', 1)
            ->get(['course_resource_id', 'completed_at']);

        $completedCount = $doneRows->count();
        $percent = $totalResources > 0 ? round($completedCount * 100 / $totalResources, 2) : 0.0;

        // Breakdown per minggu
        $weeks = DB::table('weeks')
            ->where('kelas_id', $id)
            ->orderBy('week_number')
            ->get(['id', 'week_number']);

        $byResource = DB::table('course_resources')
            ->select('id', 'week_id', 'type', 'title', 'sort')
            ->whereIn('id', $resourceIds)
            ->get()
            ->keyBy('id');

        $doneIndex = $doneRows->keyBy('course_resource_id');

        $weeksBreakdown = $weeks->map(function ($w) use ($byResource, $doneIndex) {
            $resThisWeek = $byResource->filter(fn ($r) => (int)$r->week_id === (int)$w->id);
            $tot = $resThisWeek->count();
            $done = $resThisWeek->filter(fn ($r) => $doneIndex->has($r->id))->count();
            return [
                'week_number' => (int)$w->week_number,
                'total'       => $tot,
                'completed'   => $done,
            ];
        })->values();

        // Daftar resource yang sudah selesai (lengkap dengan week number)
        $completedResources = $byResource->filter(function ($r) use ($doneIndex) {
            return $doneIndex->has($r->id);
        })->map(function ($r) use ($weeks, $doneIndex) {
            $wNo = optional($weeks->firstWhere('id', $r->week_id))->week_number;
            return [
                'id'           => (int)$r->id,
                'week_number'  => (int)$wNo,
                'type'         => $r->type,
                'title'        => $r->title,
                'completed_at' => optional($doneIndex->get($r->id))->completed_at,
            ];
        })->sortBy(['week_number', 'sort'])->values();

        // Info student (opsional untuk header)
        $student = DB::table('users')->where('id', $studentId)->first(['id','name','email']);

        return response()->json([
            'class_id' => (int)$id,
            'student'  => [
                'id'    => $student->id ?? $studentId,
                'name'  => $student->name ?? null,
                'email' => $student->email ?? null,
            ],
            'totals'   => [
                'totalResources' => $totalResources,
                'completed'      => $completedCount,
                'percent'        => $percent,
            ],
            'weeks'    => $weeksBreakdown,
            'completed_resources' => $completedResources,
            'source'   => 'kelas-controller',
        ]);
    }
}
