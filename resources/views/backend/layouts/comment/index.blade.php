@extends('backend.app', ['title' => 'Comments'])

@push('styles')
<link href="{{ asset('default/datatable.css') }}" rel="stylesheet" />
@endpush

@section('content')
<style>
    /* Main wrapper */
.comment-main {
    font-size: 14px;
    max-width: 100%;
}

/* Each comment */
.comment-item {
    margin-bottom: 6px;
}

/* Comment text */
.comment-text {
    padding: 6px 8px;
    background: #f9fafb;
    border-radius: 6px;
}

/* Replies container */
.comment-replies {
    list-style: none;
    padding-left: 16px;
    margin-top: 6px;
    border-left: 2px solid #e5e7eb;
}

/* Level spacing */
.comment-replies.level-1 {
    margin-left: 12px;
}

.comment-replies.level-2 {
    margin-left: 16px;
    border-left-color: #d1d5db;
}

/* Actions (icons) */
.comment-actions {
    margin-top: 4px;
    display: flex;
    gap: 6px;
}

/* Icon buttons */
.icon-btn {
    background: transparent;
    border: none;
    padding: 2px 4px;
    cursor: pointer;
    font-size: 13px;
    color: #6b7280;
}

.icon-btn:hover {
    color: #111827;
}

/* Danger hover */
.icon-btn.delete-comment:hover,
.icon-btn.delete-reply:hover {
    color: #dc2626;
}

</style>
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">
            <!-- PAGE HEADER -->
            <div class="page-header">
                <h1 class="page-title">Comments</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Comments</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Comments List</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="datatable" class="table table-bordered text-nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Post</th>
                                            <th>Commenter</th>
                                            <th>Comment & Replies</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Reply / Add Comment Modal -->
<div class="modal fade" id="commentModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="commentForm">
        <div class="modal-header">
          <h5 class="modal-title">Add / Reply Comment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="parent_id" id="parent_id">
          <input type="hidden" name="news_id" id="news_id">
          <textarea name="comment" id="comment_text" class="form-control" rows="4" placeholder="Write comment"></textarea>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Submit</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Comment Modal -->
<div class="modal fade" id="editCommentModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editCommentForm">
        <div class="modal-header">
          <h5 class="modal-title">Edit Comment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="comment_id" id="edit_comment_id">
          <textarea name="comment" id="edit_comment_text" class="form-control" rows="4"></textarea>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function(){

    // Setup AJAX CSRF
    $.ajaxSetup({
        headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") }
    });

    // Initialize DataTable
    let table = $('#datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.comment.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'post', name: 'post' },
            { data: 'commenter', name: 'commenter' },
            { data: 'comment', name: 'comment', orderable: false, searchable: true },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    // Open reply modal
    $(document).on('click', '.reply-comment', function(){
        $('#parent_id').val($(this).data('id'));
        $('#news_id').val($(this).data('news-id'));
        $('#commentModal').modal('show');
    });

    // Submit reply / new comment
    $('#commentForm').submit(function(e){
        e.preventDefault();
        $.post("{{ route('admin.comment.store') }}", $(this).serialize(), function(resp){
            $('#commentModal').modal('hide');
            table.ajax.reload();
            toastr.success(resp.message);
            $('#commentForm')[0].reset();
        });
    });

    // Edit comment / reply
    $(document).on('click', '.edit-comment, .edit-reply', function(){
        let id = $(this).data('id');
        $.get("/admin/comment/" + id + "/edit", function(resp){
            $('#edit_comment_id').val(resp.id);
            $('#edit_comment_text').val(resp.comment);
            $('#editCommentModal').modal('show');
        });
    });

    // Submit edit
    $('#editCommentForm').submit(function(e){
        e.preventDefault();
        let id = $('#edit_comment_id').val();
        $.ajax({
            url: "/admin/comment/" + id,
            type: "PUT",
            data: $(this).serialize(),
            success: function(resp){
                $('#editCommentModal').modal('hide');
                table.ajax.reload();
                toastr.success(resp.message);
            }
        });
    });

    // Delete comment / reply
    $(document).on('click', '.delete-comment, .delete-reply', function(){
        let id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete!'
        }).then((result)=>{
            if(result.isConfirmed){
                $.ajax({
                    url: "/admin/comment/" + id,
                    type: "DELETE",
                    success: function(resp){
                        table.ajax.reload();
                        toastr.success(resp.message);
                    }
                });
            }
        });
    });

});
</script>
@endpush
