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
            $comments = Comment::with([
                'user',
                'news',
                'replies.user',
                'replies.replies.user'
            ])
                ->whereNull('parent_id')
                ->latest()
                ->get();

            return DataTables::of($comments)
                ->addIndexColumn()
                ->addColumn('commenter', fn($comment) => $comment->user?->name ?? 'Guest')
                ->addColumn('post', fn($comment) => $comment->news?->title ?? '—')
                ->addColumn('comment', fn($comment) => $this->buildCommentThreadHtml($comment))
                ->addColumn('action', fn($comment) => $this->buildActionButtons($comment))
                ->rawColumns(['comment', 'action'])
                ->make(true);
        }

        return view('backend.layouts.comment.index');
    }

    /**
     * Build threaded comment HTML (up to 3 levels)
     */
    private function buildCommentThreadHtml(Comment $comment): string
    {
        $html = '<div class="comment-main">';

        // Level 0 (main comment)
        $html .= '
        <div class="comment-item level-0">
            <div class="comment-text">' . e($comment->comment) . '</div>

        </div>
    ';

        // Level 1 replies
        if ($comment->replies->isNotEmpty()) {
            $html .= '<ul class="comment-replies level-1">';

            foreach ($comment->replies as $reply) {
                $html .= '
                <li class="comment-item">
                    <div class="comment-text">
                        <strong>' . e($reply->user?->name ?? 'Guest') . ':</strong>
                        ' . e($reply->comment) . '
                    </div>

                    <div class="comment-actions">
                        <button class="icon-btn edit-reply" data-id="' . $reply->id . '" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="icon-btn delete-reply" data-id="' . $reply->id . '" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                        <button class="icon-btn reply-comment"
                    data-id="' . $comment->id . '"
                    data-news-id="' . $comment->news_id . '"
                    title="Reply">
                <i class="fas fa-reply"></i>
            </button>
                    </div>
            ';

                // Level 2 replies
                if ($reply->replies->isNotEmpty()) {
                    $html .= '<ul class="comment-replies level-2">';

                    foreach ($reply->replies as $subReply) {
                        $html .= '
                        <li class="comment-item">
                            <div class="comment-text">
                                <strong>' . e($subReply->user?->name ?? 'Guest') . ':</strong>
                                ' . e($subReply->comment) . '
                            </div>

                            <div class="comment-actions">
                                <button class="icon-btn edit-reply" data-id="' . $subReply->id . '" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="icon-btn delete-reply" data-id="' . $subReply->id . '" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </li>
                    ';
                    }

                    $html .= '</ul>';
                }

                $html .= '</li>';
            }

            $html .= '</ul>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Action buttons for top-level comment
     */
    private function buildActionButtons(Comment $comment): string
    {
        return '
        <div class="comment-actions">
            <button class="icon-btn reply-comment"
                    data-id="' . $comment->id . '"
                    data-news-id="' . $comment->news_id . '"
                    title="Reply">
                <i class="fas fa-reply"></i>
            </button>
            <button class="icon-btn edit-comment"
                    data-id="' . $comment->id . '"
                    title="Edit">
                <i class="fas fa-edit"></i>
            </button>
            <button class="icon-btn delete-comment"
                    data-id="' . $comment->id . '"
                    title="Delete">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    ';
    }


    public function store(Request $request)
    {
        $request->validate([
            'news_id' => 'required|exists:news,id',
            'comment' => 'required|string',
            'parent_id' => 'nullable|exists:comments,id',
        ]);


               $comment = Comment::where('id', $request->parent_id)
                 ->where('news_id', $request->news_id)
                 ->get();


        if ($comment->count() < 1) {
            dd(123);
            $comment = Comment::create([
                'user_id' => auth()->id(),
                'news_id' => $request->news_id,
                'parent_id' => $comment->first()->id,
                'comment' => $request->comment,
            ]);
        } else {
            $comment = Comment::create([
                'user_id' => auth()->id(),
                'news_id' => $request->news_id,
                'parent_id' => $request->parent_id,
                'comment' => $request->comment,
            ]);
        }
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
