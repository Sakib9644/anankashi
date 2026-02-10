<?php

namespace App\Http\Controllers\api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\Dislike;
use App\Models\Like;
use App\Models\News;
use Google\Cloud\Storage\Connection\Rest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Str;
use Throwable;

class NewsController extends Controller
{
    //
    public function news(Request $request)
    {
        try {

            $request->validate([
                'current_page' => 'sometimes|integer|min:1',
                'per_page'     => 'sometimes|integer|min:1|max:100',
                'title'        => 'sometimes|string',
                'type'         => 'sometimes|string',
            ]);

            $page    = $request->input('current_page', 1);
            $perPage = $request->input('per_page', 10);

            $query = News::where('status', 'publish')->withCount('likes')->withcount('dislikes')->withCount('comments')->latest('id');

            Cache::flush();

            if ($request->filled('title')) {
                $query->where('title', 'like', '%' . $request->title . '%');
            }

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            $newsPaginator = $query->paginate($perPage, ['*'], 'page', $page);

            $newsData = $newsPaginator->getCollection()->map(function ($news) {
                return [
                    'id'          => $news->id,
                    'type'        => $news->type,
                    'slug'        => $news->slug,
                    'title'       => $news->title,
                    'likes_count' => $news->likes_count,
                    'dislikes_count' => $news->dislikes_count,
                    'comments_count' => $news->comments_count,
                    'description' => Str::limit($news->short_description, 100),
                    'thumbnail'   => $news->thumbnail ? asset($news->thumbnail) : null,
                    'date'        => $news->created_at->format('l, F d Y'),
                ];
            });

            $newsPaginator->setCollection($newsData);

            return response()->json([
                'status'  => true,
                'code'    => 200,
                'message' => 'News fetched successfully',
                'data'    => [
                    'newslist' => $newsPaginator->items(),
                    'pagination' => [
                        'total_page'   => $newsPaginator->lastPage(),
                        'per_page'     => $newsPaginator->perPage(),
                        'total_item'   => $newsPaginator->total(),
                        'current_page' => $newsPaginator->currentPage(),
                    ],
                ],
            ]);
        } catch (ValidationException $e) {
            return Helper::jsonErrorResponse($e->errors(), 422, $e->getMessage());
        } catch (Throwable $e) {
            return Helper::jsonErrorResponse(
                config('app.debug') ? $e->getMessage() : 'Internal server error',
                500
            );
        }
    }


    public function news_details()
    {
        $slug = request('slug');

        try {
            $news = News::with('details.images')
                ->where('slug', $slug)
                ->first();

            if (!$news) {
                return Helper::jsonErrorResponse('News not found', 404);
            }
            $contentText = strip_tags($news->short_description ?? '');
            foreach ($news->details as $detail) {
                $contentText .= ' ' . strip_tags($detail->description ?? '');
            }
            $wordCount = str_word_count($contentText);
            $readingTime = max(1, ceil($wordCount / 200));
            $newsData = [
                'id' => $news->id,
                'is_subscribed' => auth()->user()->is_subscribed,
                'title' => $news->title,
                'type' => $news->type,
                'description' => $news->short_description,
                'date' => $news->created_at->format('F d Y'),
                'reading_time' => $readingTime . ' min read',

                'details' => $news->details->map(function ($detail) {
                    return [
                        'title' => $detail->title,
                        'description' => $detail->description,
                        'images' => $detail->images->map(function ($image) {
                            return [
                                'image' => $image->image ? asset($image->image) : null,
                            ];
                        }),
                    ];
                }),
                'total_comments' => $news->comments->count(),
                'total_likes' => $news->likes->count(),
                'total_dislike' => $news->dislikes->count(),
                'is_liked' => $news->likes()->where('user_id', auth()->user()->id)->exists(),
                'is_disliked' => $news->dislikes()->where('user_id', auth()->user()->id)->exists(),

            ];

            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => 'News fetched successfully',
                'data' => $newsData
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();

            return Helper::jsonErrorResponse($e->errors(), 422, $e->getMessage());
        } catch (Throwable $e) {
            DB::rollBack();

            return Helper::jsonErrorResponse(
                config('app.debug') ? $e->getMessage() : 'Internal server error',
                500
            );
        }
    }


    public function addComment(Request $request)
    {
        try {
            $request->validate([
                'slug'    => 'required|string|exists:news,slug', // validate slug instead of news_id
                'comment' => 'required|string|min:3',
            ]);

            // Find the news by slug
            $news = News::where('slug', $request->slug)->first();
            if (!$news) {
                return response()->json([
                    'status' => false,
                    'message' => 'News not found',
                ], 404);
            }

            // Create the comment
            $comment = Comment::create([
                'news_id'   => $news->id, // use news ID internally
                'user_id'   => auth('api')->id(),
                'parent_id' => $request->parent_id ?? null,
                'comment'   => $request->comment,
            ]);

            return response()->json([
                'status' => true,
                'code'   => 200,
                'message' => 'Comment added successfully',
            ]);
        } catch (ValidationException $e) {
            return Helper::jsonErrorResponse($e->errors(), 422, $e->getMessage());
        } catch (Throwable $e) {
            return Helper::jsonErrorResponse(
                config('app.debug') ? $e->getMessage() : 'Internal server error',
                500
            );
        }
    }

    public function reaction(Request $request)
    {
        try {
            $request->validate([
                'slug'   => 'required|string|exists:news,slug', // validate slug
                'action' => 'required|in:like,dislike',
            ]);

            $userId = auth('api')->id();
            $slug = $request->slug;

            // Find the news by slug
            $news = News::where('slug', $slug)->firstOrFail();
            $newsId = $news->id;

            if ($request->action === 'like') {
                // Remove existing dislike if any
                Dislike::where('user_id', $userId)
                    ->where('news_id', $newsId)
                    ->delete();

                // Check if user already liked
                $like = Like::where('user_id', $userId)
                    ->where('news_id', $newsId)
                    ->first();

                if ($like) {
                    $like->delete();
                    $status = 'unliked';
                } else {
                    Like::create([
                        'news_id' => $newsId,
                        'user_id' => $userId,
                    ]);
                    $status = 'liked';
                }
            }

            if ($request->action === 'dislike') {
                // Remove existing like if any
                Like::where('user_id', $userId)
                    ->where('news_id', $newsId)
                    ->delete();

                // Check if user already disliked
                $dislike = Dislike::where('user_id', $userId)
                    ->where('news_id', $newsId)
                    ->first();

                if ($dislike) {
                    $dislike->delete();
                    $status = 'undisliked';
                } else {
                    Dislike::create([
                        'news_id' => $newsId,
                        'user_id' => $userId,
                    ]);
                    $status = 'disliked';
                }
            }

            return response()->json([
                'status' => true,
                'code'   => 200,
                'reaction_status' => $status,
            ]);
        } catch (ValidationException $e) {
            return Helper::jsonErrorResponse($e->errors(), 422, $e->getMessage());
        } catch (Throwable $e) {
            return Helper::jsonErrorResponse(
                config('app.debug') ? $e->getMessage() : 'Internal server error',
                500
            );
        }
    }


    public function comments(Request $request)
    {
        $page    = $request->input('current_page', 1);
        $perPage = $request->input('per_page', 10);
        $slug    = $request->input('slug');

        if (!$slug) {
            return response()->json([
                'status' => false,
                'message' => 'News slug is required',
            ], 400);
        }

        // Find news by slug
        $news = News::where('slug', $slug)->first();
        if (!$news) {
            return response()->json([
                'status' => false,
                'message' => 'News not found',
            ], 404);
        }

        $authUserId = auth('api')->id();

        $comments = Comment::with([
            'user',
            'replies.user',
            'replies.replies.user'
        ])
            ->where('news_id', $news->id)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $transformedData = $comments->getCollection()->map(function ($comment) use ($authUserId) {
            return [
                'id' => $comment->id,
                'user_id' => $comment->user_id,
                'is_mine' => $comment->user_id == $authUserId,
                'is_liked' => $comment->likes()->where('user_id', $authUserId)->exists(),
                'avatar' => $comment->user?->avatar ? url($comment->user->avatar) : null,
                'name' => $comment->user?->name,
                'comment' => $comment->comment,
                'commented_at' => $comment->created_at->diffForHumans(),
                'replies' => $comment->replies->map(function ($reply) use ($authUserId) {
                    return [
                        'id' => $reply->id,
                        'user_id' => $reply->user_id,
                        'is_mine' => $reply->user_id == $authUserId,
                        'is_liked' => $reply->likes()->where('user_id', $authUserId)->exists(),
                        'avatar' => $reply->user?->avatar ? url($reply->user->avatar) : null,
                        'name' => $reply->user?->name,
                        'comment' => $reply->comment,
                        'commented_at' => $reply->created_at->diffForHumans(),
                        'replies' => $reply->replies->map(function ($subReply) use ($authUserId) {
                            return [
                                'id' => $subReply->id,
                                'user_id' => $subReply->user_id,
                                'is_mine' => $subReply->user_id == $authUserId,
                                'is_liked' => $subReply->likes()->where('user_id', $authUserId)->exists(),
                                'avatar' => $subReply->user?->avatar ? url($subReply->user->avatar) : null,
                                'name' => $subReply->user?->name,
                                'comment' => $subReply->comment,
                                'commented_at' => $subReply->created_at->diffForHumans(),
                            ];
                        })->values(),
                    ];
                })->values(),
            ];
        })->values();

        // Replace paginator collection
        $comments->setCollection($transformedData);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Comments fetched successfully',
            'data' => $comments->items(), // or $transformedData
            'pagination' => [
                'total_page'   => $comments->lastPage(),
                'per_page'     => $comments->perPage(),
                'total_item'   => $comments->total(),
                'current_page' => $comments->currentPage(),
            ],
        ]);
    }


    public function news_type(Request $request)
    {
        $page    = $request->input('current_page', 1);
        $perPage = $request->input('per_page', 10);

        $newsType = News::select('type')->where('status', 'publish')->distinct()->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'News Type fetched successfully',
            'data' => $newsType->map(function ($newsType) {
                return [
                    'type' => $newsType->type,
                ];
            }),
            'pagination' => [
                'total_page'   => $newsType->lastPage(),
                'per_page'     => $newsType->perPage(),
                'total_item'   => $newsType->total(),
                'current_page' => $newsType->currentPage(),
            ], // actual comment data
        ]);
    }


    public function subscribe(Request $request)
    {
        $user = auth('api')->user();
        $user->is_subscribed = ! $user->is_subscribed;

        $user->save();
        // dd($user->is_subscribed);
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => $user->is_subscribed ? 'Subscribed' : 'Unsubscribed',
        ]);
    }


    public function most_popular(Request $request)
    {
        $page    = $request->input('current_page', 1);
        $perPage = $request->input('per_page', 10);

        $newsall = News::withCount(['likes', 'dislikes', 'comments'])
            ->where('status', 'publish')
            ->orderBy('likes_count', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'status'  => true,
            'code'    => 200,
            'message' => 'Most popular news fetched successfully',
            'data'    => [
                'newslist' => $newsall->map(function ($news) {
                    return [
                        'id'               => $news->id,
                        'likes_count'      => $news->likes_count,
                        'dislikes_count'   => $news->dislikes_count,
                        'comments_count'   => $news->comments_count,
                        'type'             => $news->type,
                        'slug'             => $news->slug,
                        'title'            => $news->title,
                        'description'      => Str::limit($news->short_description, 100),
                        'thumbnail'        => $news->thumbnail ? asset($news->thumbnail) : null,
                        'date'             => $news->created_at->format('l F d Y'),
                    ];
                }),
                'pagination' => [
                    'total_page'   => $newsall->lastPage(),
                    'per_page'     => $newsall->perPage(),
                    'total_item'   => $newsall->total(),
                    'current_page' => $newsall->currentPage(),
                ],
            ]
        ]);
    }

    public function edit_comment(Request $request)
    {
        try {

            $request->validate([
                'comment_id' => 'required|exists:comments,id',
                'comment' => 'required|string|min:3'
            ]);


            // Find the comment
            $comment = Comment::find($request->input('comment_id'));
            if (!$comment) {
                return response()->json([
                    'status' => false,
                    'message' => 'Comment not found',
                ], 404);
            }

            // Update the comment
            $comment->comment = $request->input('comment');
            $comment->save();

            return response()->json([
                'status' => true,
                'message' => 'Comment updated successfully',
            ]);
        } catch (ValidationException $e) {
            return Helper::jsonErrorResponse($e->errors(), 422, $e->getMessage());
        } catch (Throwable $e) {
            return Helper::jsonErrorResponse(
                config('app.debug') ? $e->getMessage() : 'Internal server error',
                500
            );
        }
    }


    public function delete_comment(Request $request)
    {
        try {
            $request->validate([
                'comment_id' => 'required|exists:comments,id',
            ]);

            $comment = Comment::find($request->input('comment_id'));
            if (!$comment) {
                return response()->json([
                    'status' => false,
                    'message' => 'Comment not found',
                ], 404);
            }

            $comment->delete();

            return response()->json([
                'status' => true,
                'message' => 'Comment deleted successfully',
            ]);
        } catch (ValidationException $e) {
            return Helper::jsonErrorResponse($e->errors(), 422, $e->getMessage());
        } catch (Throwable $e) {
            return Helper::jsonErrorResponse(
                config('app.debug') ? $e->getMessage() : 'Internal server error',
                500
            );
        }
    }

    public function like_comment(Request $request)
    {
        try {
            $request->validate([
                'comment_id' => 'required|exists:comments,id',
            ]);

            $commentId = $request->input('comment_id');
            $userId = auth('api')->id();

            // Check if the user has already liked the comment
            $existingLike = CommentLike::where('comment_id', $commentId)
                ->where('user_id', $userId)
                ->first();

            if ($existingLike) {
                // If the like already exists, remove it (unlike)
                $existingLike->delete();
                $status = 'unliked';
            } else {
                // Otherwise, create a new like
                CommentLike::create([
                    'comment_id' => $commentId,
                    'user_id' => $userId,
                ]);
                $status = 'liked';
            }

            return response()->json([
                'status' => true,
                'message' => "Comment {$status} successfully",
            ]);
        } catch (ValidationException $e) {
            return Helper::jsonErrorResponse($e->errors(), 422, $e->getMessage());
        } catch (Throwable $e) {
            return Helper::jsonErrorResponse(
                config('app.debug') ? $e->getMessage() : 'Internal server error',
                500
            );
        }
    }
}
