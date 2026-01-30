<?php

namespace App\Http\Controllers\api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Dislike;
use App\Models\Like;
use App\Models\News;
use Google\Cloud\Storage\Connection\Rest;
use Illuminate\Http\Request;
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
            // 1️⃣ Validate inputs
            $request->validate([
                'current_page' => 'sometimes|integer|min:1',
                'per_page'     => 'sometimes|integer|min:1|max:100',
                'title'        => 'sometimes|string',
                'type'         => 'sometimes|string',
            ]);

            $page    = $request->input('current_page', 1);
            $perPage = $request->input('per_page', 10);

            $query = News::query()->latest('id');

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
                    'description' => Str::limit($news->short_description, 100),
                    'thumbnail'   => $news->thumbnail ? asset($news->thumbnail) : null,
                    'date'        => $news->created_at->format('l F d Y'),
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
                'is_subscribed' => $news->is_subscribed,
                'title' => $news->title,
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
                // 'comments' => $news->comments->whereNull('parent_id')->values()->map(function ($comment) {

                //     return [

                //         'id' => $comment->id,
                //         'comment' => $comment->comment,
                //         'user' => $comment->user ? [
                //             'name' => $comment->user->name,
                //             'avatar' => $comment->user->avatar ? asset($comment->user->avatar) : null,
                //             'commented_at' => $comment->created_at->diffForHumans(),
                //         ] : null,
                //         'replies' => $comment->replies->map(function ($reply) {
                //             return [
                //                 'id' => $reply->id,
                //                 'comment' => $reply->comment,
                //                 'user' => $reply->user ? [
                //                     'name' => $reply->user->name,
                //                     'avatar' => $reply->user->avatar ? asset($reply->user->avatar) : null,
                //                     'commented_at' => $reply->created_at->diffForHumans(),
                //                 ] : null,
                //             ];
                //         })




                //     ];
                // }),
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
        // dd($request->all());
        try {

            $request->validate([
                'news_id' => 'required|exists:news,id',
                'comment' => 'required|string|min:3'
            ]);

            $comment = Comment::create([
                'news_id' => $request->news_id,
                'user_id' => auth('api')->id(),
                'parent_id' => $request->parent_id ?? null,
                'comment' => $request->comment,
            ]);

            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => 'Comment added successfully',
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

    public function reaction(Request $request)
    {
        try {
            $request->validate([
                'news_id' => 'required|exists:news,id',
                'action'  => 'required|in:like,dislike',
            ]);

            $userId = auth('api')->id();
            $newsId = $request->news_id;

            if ($request->action === 'like') {

                // Remove dislike first
                Dislike::where('user_id', $userId)
                    ->where('news_id', $newsId)
                    ->delete();

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

                // Remove like first
                Like::where('user_id', $userId)
                    ->where('news_id', $newsId)
                    ->delete();

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
        $newsId  = $request->input('news_id');

        if (!$newsId) {
            return response()->json([
                'status' => false,
                'message' => 'News ID is required',
            ], 400);
        }

        // Fetch paginated comments with user and replies
        $comments = Comment::with(['user', 'replies.user'])
            ->where('news_id', $newsId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        // Transform comments
        $data = $comments->map(function ($comment) {
            return [
                'id' => $comment->id,
                'user_id' => $comment->user_id,
                'avatar' => $comment->user?->avatar ? url($comment->user->avatar) : null,
                'name' => $comment->user?->name,
                'comment' => $comment->comment,
                'commented_at' => $comment->created_at->diffForHumans(),

                'replies' => $comment->replies->map(function ($reply) {
                    return [
                        'id' => $reply->id,
                        'user_id' => $reply->user_id,
                        'avatar' => $reply->user?->avatar ? url($reply->user->avatar) : null,
                        'name' => $reply->user?->name,
                        'reply' => $reply->comment,
                        'commented_at' => $reply->created_at->diffForHumans(),
                    ];
                })->values(),
            ];
        });
        // Replace collection with transformed data


        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Comments fetched successfully',
            'data' => $data, // actual comment data
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

        $newsType = News::select('type')->distinct()->paginate($perPage, ['*'], 'page', $page);

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
}
