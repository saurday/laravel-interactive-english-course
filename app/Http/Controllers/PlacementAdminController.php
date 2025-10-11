<?php

// app/Http/Controllers/PlacementAdminController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlacementAdminController extends Controller
{
    // app/Http/Controllers/PlacementAdminController.php
public function index(Request $req)
{
    $rows = DB::table('placement_level_contents as c')
        ->join('placement_levels as l','l.id','=','c.level_id')
        ->select(
            'c.id','c.level_id',
            'l.code as level_code','l.name as level_name',
            'c.sort_order',                // <— kolom baru
            'c.type','c.title','c.text','c.video_url','c.file_url','c.quiz_id',
            'c.created_at'
        )
        ->orderBy('l.code')
        ->orderBy('c.sort_order')          // <— pakai sort_order
        ->get();

    return response()->json(['contents' => $rows]);
}

}

