@extends('backend.app', ['title' => 'Create News'])

@section('content')

<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">

            <div class="page-header">
                <div><h1 class="page-title">Create News</h1></div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.news.index') }}">News</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create News</h3>
                    <div class="card-options">
                        <a href="javascript:window.history.back()" class="btn btn-sm btn-primary">Back</a>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('admin.news.store') }}" enctype="multipart/form-data">
                        @csrf

                        {{-- News Section --}}
                        <h5 class="text-center">News Info</h5>
                        <div class="mb-3">
                            <label for="news_title">News Title</label>
                            <input type="text" class="form-control" name="news_title" id="news_title">
                        </div>

                        <div class="mb-3">
                            <label for="type">Type</label>
                            <input type="text" class="form-control" name="type" id="type">
                        </div>

                        <div class="mb-3">
                            <label for="short_description">Short Description</label>
                            <textarea class="form-control" name="short_description"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="thumbnail">Thumbnail</label>
                            <input type="file" class="form-control" name="thumbnail">
                        </div>



                        {{-- News Details --}}
                        <h5 class="mt-4 text-center">News Details</h5>
                        <div id="news-details-wrapper">

                            <div class="news-detail-row border p-3 mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <h6>Detail</h6>
                                    <button type="button" class="btn btn-danger btn-sm remove-detail">Remove</button>
                                </div>

                                <div class="mb-3">
                                    <label>Detail Title</label>
                                    <input type="text" name="details[0][title]" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label>Description</label>
                                    <textarea name="details[0][description]" class="form-control"></textarea>
                                </div>

                                <div class="images-wrapper">
                                    <div class="mb-3 image-row">
                                        <label>Image</label>
                                        <div class="input-group mb-2">
                                            <input type="file" name="details[0][images][]" class="form-control">
                                            <button type="button" class="btn btn-success add-image">+</button>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>

                        <button type="button" class="btn btn-secondary mb-3" id="add-detail">Add News Detail</button>

                        <div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let detailIndex = 1;

// Add new detail row
$('#add-detail').click(function() {
    let html = `
    <div class="news-detail-row border p-3 mb-3">
        <div class="d-flex justify-content-between mb-2">
            <h6>Detail</h6>
            <button type="button" class="btn btn-danger btn-sm remove-detail">Remove</button>
        </div>

        <div class="mb-3">
            <label>Detail Title</label>
            <input type="text" name="details[${detailIndex}][title]" class="form-control">
        </div>

        <div class="mb-3">
            <label>Description</label>
            <textarea name="details[${detailIndex}][description]" class="form-control"></textarea>
        </div>

        <div class="images-wrapper">
            <div class="mb-3 image-row">
                <label>Image</label>
                <div class="input-group mb-2">
                    <input type="file" name="details[${detailIndex}][images][]" class="form-control">
                    <button type="button" class="btn btn-success add-image">+</button>
                </div>
            </div>
        </div>
    </div>
    `;
    $('#news-details-wrapper').append(html);
    detailIndex++;
});

// Remove detail row
$(document).on('click', '.remove-detail', function() {
    $(this).closest('.news-detail-row').remove();
});

// Add new image input inside a detail
$(document).on('click', '.add-image', function() {
    let inputGroup = `
        <div class="input-group mb-2">
            <input type="file" name="${$(this).prev('input').attr('name')}" class="form-control">
            <button type="button" class="btn btn-danger remove-image">-</button>
        </div>
    `;
    $(this).closest('.images-wrapper').append(inputGroup);
});

// Remove image input
$(document).on('click', '.remove-image', function() {
    $(this).closest('.input-group').remove();
});
</script>
@endpush
