<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;

// app/Http/Controllers/QuestionController.php
class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $q = Question::with('options')->orderBy('id');
        if ($request->filled('quiz_id')) $q->where('quiz_id', $request->quiz_id);
        return response()->json($q->get(), 200);
    }

    public function show($id)
    {
        return response()->json(
            Question::with('options')->findOrFail($id),
            200
        );
    }

public function store(Request $request)
{
    $request->validate([
        'quiz_id' => 'required|exists:quizzes,id',
        'text'    => 'required|string', // datang dari FE
        'type'    => 'required|in:multiple_choice,drag_drop,matching,short_answer,ordering,categorizing',
    ]);

    $question = Question::create([
        'quiz_id'       => $request->quiz_id,
        'question_text' => $request->text,   // MAP
        'type'          => $request->type,
    ]);

    return response()->json($question, 201);
}


    public function update(Request $request, $id)
    {
        $question = Question::findOrFail($id);

        $payload = $request->all();
        if (!isset($payload['question_text']) && isset($payload['text'])) {
            $payload['question_text'] = $payload['text'];
        }

        $data = validator($payload, [
            'question_text' => 'sometimes|string',
            'type'          => 'sometimes|in:multiple_choice,short_answer,drag_drop,matching,ordering,categorizing',
        ])->validate();

        $question->update($data);
        return response()->json($question, 200);
    }

    public function destroy($id)
    {
        Question::findOrFail($id)->delete();
        return response()->json(['message'=>'Question deleted'], 200);
    }
}
