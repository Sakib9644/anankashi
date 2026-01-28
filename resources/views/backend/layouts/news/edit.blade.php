<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.news.update', $news->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <h5 class="mb-4">Edit News</h5>

            <!-- News Title -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="news_title" class="form-label fw-semibold text-start d-block">News Title</label>
                    <input type="text" id="news_title" class="form-control" name="news_title"
                        value="{{ old('news_title', $news->title) }}">
                </div>

                <!-- Type -->
                <div class="col-md-6 mb-3">
                    <label for="type" class="form-label fw-semibold text-start d-block">Type</label>
                    <input type="text" id="type" class="form-control" name="type"
                        value="{{ old('type', $news->type) }}">
                </div>
            </div>

            <!-- Short Description -->
            <div class="mb-3">
                <label for="short_description" class="form-label fw-semibold text-start d-block">Short Description</label>
                <textarea id="short_description" class="form-control summernote" name="short_description">{{ old('short_description', $news->short_description) }}</textarea>
            </div>

            <!-- Thumbnail -->
            <div class="mb-3">
                <label for="thumbnail" class="form-label fw-semibold text-start d-block">Thumbnail</label>
                <input type="file" id="thumbnail" class="form-control" name="thumbnail">
                @if ($news->thumbnail)
                    <img src="{{ asset($news->thumbnail) }}" width="120" class="mt-2">
                @endif
            </div>

            <h5 class="mt-4 mb-3 text-start">News Details</h5>

            <div id="news-details-wrapper">
                @foreach ($news->details as $index => $detail)
                    <div class="news-detail-row border p-3 mb-3 position-relative">

                        <input type="hidden" name="details[{{ $index }}][id]" value="{{ $detail->id }}">

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Detail</h6>
                            <button type="button" class="btn btn-danger btn-sm remove-detail">Remove</button>
                        </div>

                        <!-- Detail Title -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-start d-block">Detail Title</label>
                            <input type="text" name="details[{{ $index }}][title]" class="form-control"
                                value="{{ $detail->title }}">
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-start d-block">Description</label>
                            <textarea name="details[{{ $index }}][description]" class="form-control summernote">{{ $detail->description }}</textarea>
                        </div>

                        <!-- Existing Images -->
                        <div class="mb-3">
                            @foreach ($detail->images as $img)
                                <img src="{{ asset($img->image) }}" width="100" class="me-2 mb-2">
                            @endforeach
                        </div>

                        <!-- Add New Images -->
                        <div class="images-wrapper">
                            <div class="input-group mb-2">
                                <input type="file" name="details[{{ $index }}][images][]"
                                    class="form-control">
                                <button type="button" class="btn btn-success add-image">+</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="button" class="btn btn-secondary mb-3" id="add-detail">Add News Detail</button>

            <div>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>

        </form>
    </div>
</div>

<style>
    .note-editable {
        text-align: left !important;
    }
    .note-editable p {
        text-align: left !important;
    }
</style>

<script>
    let detailIndex = {{ $news->details->count() }};

    $('#add-detail').click(function() {
        let html = `
    <div class="news-detail-row border p-3 mb-3 position-relative">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">Detail</h6>
            <button type="button" class="btn btn-danger btn-sm remove-detail">Remove</button>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold text-start d-block">Detail Title</label>
            <input type="text" name="details[${detailIndex}][title]" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold text-start d-block">Description</label>
            <textarea name="details[${detailIndex}][description]" class="form-control summernote"></textarea>
        </div>

        <div class="images-wrapper">
            <div class="input-group mb-2">
                <input type="file" name="details[${detailIndex}][images][]" class="form-control">
                <button type="button" class="btn btn-success add-image">+</button>
            </div>
        </div>
    </div>`;
        $('#news-details-wrapper').append(html);
        detailIndex++;
    });

    // Remove detail row
    $(document).on('click', '.remove-detail', function() {
        $(this).closest('.news-detail-row').remove();
    });

    // Add new image input
    $(document).on('click', '.add-image', function() {
        let name = $(this).prev('input').attr('name');
        let html = `
        <div class="input-group mb-2">
            <input type="file" name="${name}" class="form-control">
            <button type="button" class="btn btn-danger remove-image">-</button>
        </div>`;
        $(this).closest('.images-wrapper').append(html);
    });

    // Remove image input
    $(document).on('click', '.remove-image', function() {
        $(this).closest('.input-group').remove();
    });
</script>
