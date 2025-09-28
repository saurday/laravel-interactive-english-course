<?php
// app/Http/Controllers/CommentController.php
namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CourseResource;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // GET /api/course-resources/{resource}/comments
    public function index(CourseResource $resource)
    {
        // root comments + replies
        $comments = Comment::where('course_resource_id', $resource->id)
            ->whereNull('parent_id')
            ->with(['replies.user'])
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'resource_id' => $resource->id,
            'comments'    => $comments->map(function ($c) {
                return $this->mapComment($c);
            }),
        ]);
    }

    // POST /api/course-resources/{resource}/comments
    public function store(Request $req, CourseResource $resource)
    {
        $data = $req->validate([
            'text'      => 'required|string',
            'parent_id' => 'nullable|exists:comments,id'
        ]);

        $comment = Comment::create([
            'course_resource_id' => $resource->id,
            'user_id'            => $req->user()->id,
            'parent_id'          => $data['parent_id'] ?? null,
            'text'               => $data['text'],
        ]);

        return response()->json(['comment' => $this->mapComment($comment->fresh(['user','replies.user']))], 201);
    }

    // PUT /api/comments/{comment}
    public function update(Request $req, Comment $comment)
    {
        $this->authorizeComment($req->user()->id, $comment); // pemilik atau dosen

        $data = $req->validate(['text' => 'required|string']);
        $comment->update(['text' => $data['text']]);

        return response()->json(['comment' => $this->mapComment($comment->fresh(['user','replies.user']))]);
    }

    // DELETE /api/comments/{comment}
    public function destroy(Request $req, Comment $comment)
    {
        $this->authorizeComment($req->user()->id, $comment);
        $comment->delete();

        return response()->json(['ok' => true]);
    }

    // PUT /api/comments/{comment}/score  (khusus dosen)
    public function score(Request $req, Comment $comment)
    {
        $user = $req->user();
        if (!in_array($user->role ?? null, ['dosen','lecturer'])) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $data = $req->validate(['score' => 'nullable|numeric|min:0|max:100']);
        $comment->update(['score' => $data['score']]);
        return response()->json(['comment' => $this->mapComment($comment->fresh(['user','replies.user']))]);
    }

    /** helpers */
    private function authorizeComment(int $userId, Comment $comment): void
    {
        $user = request()->user();
        $isLecturer = in_array($user->role ?? null, ['dosen','lecturer']);
        abort_unless($isLecturer || $comment->user_id === $userId, 403, 'Forbidden');
    }

    private function mapComment(Comment $c): array
    {
        return [
            'id'        => $c->id,
            'author'    => $c->user?->name ?? 'User',
            'author_id' => $c->user_id,
            'role'      => $c->user->role ?? 'user',
            'text'      => $c->text,
            'score'     => $c->score,
            'createdAt' => $c->created_at?->toIso8601String(),
            'replies'   => $c->replies->map(fn($r) => [
                'id'        => $r->id,
                'author'    => $r->user?->name ?? 'User',
                'author_id' => $r->user_id,
                'role'      => $r->user->role ?? 'user',
                'text'      => $r->text,
                'score'     => $r->score,
                'createdAt' => $r->created_at?->toIso8601String(),
            ])->values(),
        ];
    }
}
