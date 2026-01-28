<?php

namespace App\Http\Controllers\Web\Backend;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\NewsDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;

class NewsController extends Controller
{
    /* ===============================
     * INDEX (DATATABLE)
     * =============================== */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = News::latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('thumbnail', fn($row) => '<img src="'.asset($row->thumbnail).'" width="60">')
                ->editColumn('status', fn($row) => $row->status === 'publish'
                    ? '<span class="badge bg-success">Publish</span>'
                    : '<span class="badge bg-danger">Unpublish</span>')
                ->addColumn('action', function ($row) {
                    return '
                        <a href="'.route('admin.news.edit', $row->id).'" class="btn btn-sm btn-primary">Edit</a>
                        <button data-id="'.$row->id.'" class="btn btn-sm btn-danger delete">Delete</button>
                    ';
                })
                ->rawColumns(['thumbnail', 'status', 'action'])
                ->make(true);
        }

        return view('backend.layouts.news.index');
    }

    /* ===============================
     * CREATE FORM
     * =============================== */
    public function create()
    {
        return view('backend.layouts.news.create');
    }

    /* ===============================
     * STORE NEWS + MULTIPLE DETAILS + IMAGES
     * =============================== */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'news_title' => 'required|string|max:255',
                'short_description' => 'required',
                'thumbnail' => 'required|image',
                'status' => 'in:publish,unpublish',
                'type' => 'required|string|max:255',
                'details' => 'required|array',
                'details.*.title' => 'required|string|max:255',
                'details.*.description' => 'required|string',
                'details.*.images.*' => 'nullable|image',
            ]);

            // 1️⃣ Save News
            $news = new News();
            $news->title = $request->news_title;
            $news->slug = Str::slug($request->news_title);
            $news->short_description = $request->short_description;
            $news->status = $request->status ? 'unpublish' : 'publish';
            $news->type = $request->type;

            if ($request->hasFile('thumbnail')) {
                $image = $request->file('thumbnail');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $news->thumbnail = Helper::fileUpload($image, 'news', $imageName);
            }

            $news->save();

            // 2️⃣ Save all NewsDetails and images
            foreach ($request->details as $detail) {
                $newsDetail = new NewsDetails();
                $newsDetail->news_id = $news->id;
                $newsDetail->title = $detail['title'];
                $newsDetail->description = $detail['description'];
                $newsDetail->save();

                if (isset($detail['images'])) {
                    foreach ($detail['images'] as $image) {
                        $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                        $path = Helper::fileUpload($image, 'news_details', $imageName);

                        $newsDetail->images()->create(['image' => $path]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.news.index')->with('t-success', 'News, details, and images created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('t-error', 'Something went wrong: '.$e->getMessage());
        }
    }

    /* ===============================
     * EDIT FORM
     * =============================== */
    public function edit($id)
    {
        $news = News::with('details.images')->findOrFail($id);
        return view('backend.layouts.news.edit', compact('news'));
    }

    /* ===============================
     * UPDATE NEWS + MULTIPLE DETAILS + IMAGES
     * =============================== */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'news_title' => 'required|string|max:255',
                'short_description' => 'required',
                'thumbnail' => 'nullable|image',
                'status' => 'in:publish,unpublish',
                'type' => 'required|string|max:255',
                'details' => 'required|array',
                'details.*.title' => 'required|string|max:255',
                'details.*.description' => 'required|string',
                'details.*.images.*' => 'nullable|image',
            ]);

            // 1️⃣ Update News
            $news = News::findOrFail($id);
            $news->title = $request->news_title;
            $news->slug = Str::slug($request->news_title);
            $news->short_description = $request->short_description;
            $news->status = $request->status;
            $news->type = $request->type;

            if ($request->hasFile('thumbnail')) {
                if ($news->thumbnail && file_exists(public_path($news->thumbnail))) {
                    Helper::fileDelete(public_path($news->thumbnail));
                }
                $image = $request->file('thumbnail');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $news->thumbnail = Helper::fileUpload($image, 'news', $imageName);
            }

            $news->save();

            // 2️⃣ Update or add NewsDetails
            foreach ($request->details as $detail) {
                $newsDetail = isset($detail['id'])
                    ? NewsDetails::find($detail['id']) ?? new NewsDetails()
                    : new NewsDetails();

                $newsDetail->news_id = $news->id;
                $newsDetail->title = $detail['title'];
                $newsDetail->description = $detail['description'];
                $newsDetail->save();

                // 3️⃣ Add new images for this detail
                if (isset($detail['images'])) {
                    foreach ($detail['images'] as $image) {
                        $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                        $path = Helper::fileUpload($image, 'news_details', $imageName);

                        $newsDetail->images()->create(['image' => $path]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.news.index')->with('t-success', 'News, details, and images updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('t-error', 'Something went wrong: '.$e->getMessage());
        }
    }

    /* ===============================
     * DELETE NEWS
     * =============================== */
    public function destroy($id)
    {
        try {
            $news = News::findOrFail($id);

            if ($news->thumbnail && file_exists(public_path($news->thumbnail))) {
                Helper::fileDelete(public_path($news->thumbnail));
            }

            $news->delete();

            return response()->json([
                'status' => true,
                'message' => 'News deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: '.$e->getMessage()
            ]);
        }
    }

    /* ===============================
     * TOGGLE STATUS
     * =============================== */
    public function status($id)
    {
        try {
            $news = News::findOrFail($id);
            $news->status = $news->status === 'publish' ? 'unpublish' : 'publish';
            $news->save();

            return response()->json([
                'status' => true,
                'message' => 'Status updated'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: '.$e->getMessage()
            ]);
        }
    }
}
