<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */


public function index(Request $request)
{
    if ($request->ajax()) {
        $data = Comment::with(['user', 'news', 'replies.user'])
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('commenter', fn($c) => $c->user?->name ?? 'N/A')
            ->addColumn('comment', function($c){
                $html = "<strong>{$c->comment}</strong>";
                if($c->replies->count()){
                    $html .= "<ul style='margin-left:15px;'>";
                    foreach($c->replies as $r){
                        $html .= "<li><strong>{$r->user?->name}:</strong> {$r->comment}
                                    <button class='btn btn-sm btn-warning edit-reply' data-id='{$r->id}'>Edit</button>
                                    <button class='btn btn-sm btn-danger delete-reply' data-id='{$r->id}'>Delete</button>
                                  </li>";
                    }
                    $html .= "</ul>";
                }
                return $html;
            })
            ->addColumn('post', fn($c) => $c->news?->title ?? 'N/A')
            ->addColumn('action', function($c){
                return "
                    <button class='btn btn-primary reply-comment' data-id='{$c->id}'>Reply</button>
                    <button class='btn btn-warning edit-comment' data-id='{$c->id}'>Edit</button>
                    <button class='btn btn-danger delete-comment' data-id='{$c->id}'>Delete</button>
                ";
            })
            ->rawColumns(['comment', 'action'])
            ->make();
    }

    return view('backend.layouts.comment.index');
}

public function store(Request $request)
{
    $request->validate([
        'news_id' => 'required|exists:news,id',
        'comment' => 'required|string',
        'parent_id' => 'nullable|exists:comments,id',
    ]);

    $comment = Comment::create([
        'user_id' => auth()->id(),
        'news_id' => $request->news_id,
        'parent_id' => $request->parent_id,
        'comment' => $request->comment,
    ]);

    return response()->json(['message' => 'Comment added successfully', 'comment' => $comment]);
}

public function edit($id)
{
    $comment = Comment::findOrFail($id);
    return response()->json($comment);
}

public function update(Request $request, $id)
{
    $request->validate([
        'comment' => 'required|string',
    ]);

    $comment = Comment::findOrFail($id);
    $comment->update(['comment' => $request->comment]);

    return response()->json(['message' => 'Comment updated successfully']);
}

public function destroy($id)
{
    $comment = Comment::findOrFail($id);
    $comment->delete(); // cascade deletes replies automatically
    return response()->json(['message' => 'Comment deleted successfully']);
}

}
