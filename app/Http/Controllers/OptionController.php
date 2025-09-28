<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Option;

// app/Http/Controllers/OptionController.php
class OptionController extends Controller
{
    public function index(Request $request)
    {
        $q = Option::with('question')->orderBy('order');
        if ($request->filled('question_id')) $q->where('question_id', $request->question_id);
        return response()->json($q->get(), 200);
    }

    public function show($id)
    {
        return response()->json(Option::with('question')->findOrFail($id), 200);
    }

public function store(Request $request)
{
    $request->validate([
        'question_id' => 'required|exists:questions,id',
        'text'        => 'required|string',  // dari FE
        'is_correct'  => 'required|boolean',
        'order'       => 'nullable|integer',
    ]);

    $option = Option::create([
        'question_id' => $request->question_id,
        'option_text' => $request->text,     // MAP
        'is_correct'  => $request->boolean('is_correct'),
        'order'       => $request->input('order'),
    ]);

    return response()->json($option, 201);
}


    public function update(Request $request, $id)
    {
        $option = Option::findOrFail($id);

        $payload = $request->all();
        if (!isset($payload['option_text']) && isset($payload['text'])) {
            $payload['option_text'] = $payload['text'];
        }

        $data = validator($payload, [
            'option_text' => 'sometimes|string',
            'is_correct'  => 'sometimes|boolean',
            'order'       => 'nullable|integer',
        ])->validate();

        $option->update($data);
        return response()->json($option, 200);
    }

    public function destroy($id)
    {
        Option::findOrFail($id)->delete();
        return response()->json(['message'=>'Option deleted'], 200);
    }
}
