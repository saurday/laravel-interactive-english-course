<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CefrLevelController extends Controller
{
    public function index()
    {
        $levels = DB::table('placement_levels')
            ->where('is_active', 1)
            ->orderBy('id')
            ->get(['id','code','name','description']);

        return response()->json(['levels' => $levels]);
    }

    public function show(int $id)
    {
        $level = DB::table('placement_levels')
            ->where('id', $id)
            ->first();

        if (!$level) return response()->json(['message'=>'Not found'], 404);

        return response()->json(['level' => $level]);
    }

    public function showByCode(string $code)
    {
        $level = DB::table('placement_levels')
            ->where('code', strtoupper($code))
            ->first();             // <<< jangan pakai firstOr / firstOrFail di Query Builder

        if (!$level) return response()->json(['message'=>'Not found'], 404);

        return response()->json(['level' => $level]);  // berisi id, code, name, description
    }
}
