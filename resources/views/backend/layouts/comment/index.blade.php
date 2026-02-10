@extends('backend.app', ['title' => 'Comments Management'])

@push('styles')
<link href="{{ asset('default/datatable.css') }}" rel="stylesheet" />
<style>
    /* Custom Professional Styles */
    .comment-table-wrapper {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .page-header-modern {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2rem;
        border-radius: 8px;
        margin-bottom: 2rem;
        color: white;
    }

    .page-header-modern h1 {
        color: white;
        margin: 0;
        font-weight: 600;
    }

    .page-header-modern .breadcrumb {
        background: transparent;
        margin: 0.5rem 0 0 0;
        padding: 0;
    }

    .page-header-modern .breadcrumb-item,
    .page-header-modern .breadcrumb-item a {
        color: rgba(255,255,255,0.9);
    }

    .page-header-modern .breadcrumb-item.active {
        color: white;
        font-weight: 500;
    }

    .stats-card {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 2rem;
    }

    .stat-item {
        text-align: center;
        padding: 1rem;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #667eea;
        display: block;
    }

    .stat-label {
        color: #6c757d;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .card-header-custom {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 2px solid #667eea;
        padding: 1.25rem 1.5rem;
    }

    .card-header-custom h3 {
        margin: 0;
        color: #333;
        font-weight: 600;
        font-size: 1.25rem;
    }

    /* DataTable Custom Styling */
    #datatable thead th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        padding: 1rem 0.75rem;
        border: none;
    }

    #datatable tbody td {
        vertical-align: middle;
        padding: 1rem 0.75rem;
        border-bottom: 1px solid #e9ecef;
    }

    #datatable tbody tr {
        transition: all 0.2s ease;
    }

    #datatable tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.01);
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    /* Comment Display Styling */
    .comment-box {
        background: #f8f9fa;
        padding: 0.75rem 1rem;
        border-radius: 6px;
        border-left: 3px solid #667eea;
        margin-bottom: 0.5rem;
    }

    .comment-text {
        color: #333;
        margin: 0;
        font-size: 0.9rem;
        line-height: 1.5;
    }

    .reply-box {
        background: #fff;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        border-left: 3px solid #28a745;
        margin: 0.5rem 0 0.5rem 2rem;
        font-size: 0.85rem;
    }

    .reply-indicator {
        display: inline-block;
        background: #28a745;
        color: white;
        padding: 0.2rem 0.5rem;
        border-radius: 3px;
        font-size: 0.7rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    /* User Badge Styling */
    .user-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .user-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .user-info {
        display: flex;
        flex-direction: column;
    }

    .user-name {
        font-weight: 600;
        color: #333;
        font-size: 0.9rem;
    }

    .user-email {
        font-size: 0.75rem;
        color: #6c757d;
    }

    /* Post Badge */
    .post-badge {
        display: inline-block;
        background: #e3f2fd;
        color: #1976d2;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-weight: 500;
        font-size: 0.85rem;
        border: 1px solid #bbdefb;
    }

    .post-badge:hover {
        background: #bbdefb;
        text-decoration: none;
    }

    /* Action Buttons */
    .btn-action {
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
        border-radius: 4px;
        margin: 0 0.15rem;
        transition: all 0.2s;
    }

    .btn-action i {
        margin-right: 0.25rem;
    }

    .btn-reply {
        background: #28a745;
        color: white;
        border: none;
    }

    .btn-reply:hover {
        background: #218838;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(40,167,69,0.3);
    }

    .btn-edit {
        background: #ffc107;
        color: #333;
        border: none;
    }

    .btn-edit:hover {
        background: #e0a800;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(255,193,7,0.3);
    }

    .btn-delete {
        background: #dc3545;
        color: white;
        border: none;
    }

    .btn-delete:hover {
        background: #c82333;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(220,53,69,0.3);
    }

    /* Modal Improvements */
    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 8px 8px 0 0;
    }

    .modal-header h5 {
        color: white;
    }

    .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }

    .modal-content {
        border-radius: 8px;
        border: none;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stat-item {
            margin-bottom: 1rem;
        }

        .reply-box {
            margin-left: 1rem;
        }

        .btn-action {
            display: block;
            width: 100%;
            margin: 0.25rem 0;
        }
    }
</style>
@endpush

@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">

            <!-- MODERN PAGE HEADER -->
            <div class="page-header-modern">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="page-title">
                            <i class="fe fe-message-square me-2"></i>Comments Management
                        </h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}">
                                    <i class="fe fe-home"></i> Dashboard
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Comments</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- STATS CARDS -->
            <div class="row stats-card">
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item">
                        <span class="stat-number" id="totalComments">0</span>
                        <span class="stat-label">Total Comments</span>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item">
                        <span class="stat-number" id="totalReplies">0</span>
                        <span class="stat-label">Total Replies</span>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item">
                        <span class="stat-number" id="todayComments">0</span>
                        <span class="stat-label">Today</span>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item">
                        <span class="stat-number" id="activeUsers">0</span>
                        <span class="stat-label">Active Users</span>
                    </div>
                </div>
            </div>

            <!-- MAIN TABLE -->
            <div class="row">
                <div class="col-12">
                    <div class="card comment-table-wrapper">
                        <div class="card-header-custom">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3>
                                    <i class="fe fe-list me-2"></i>All Comments
                                </h3>
                                <div>
                                    <button class="btn btn-sm btn-outline-secondary" id="refreshTable">
                                        <i class="fe fe-refresh-cw"></i> Refresh
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="datatable" class="table table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="20%">User</th>
                                            <th width="35%">Comment & Replies</th>
                                            <th width="20%">Post</th>
                                            <th width="20%">Actions</th>
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
<div class="modal fade" id="commentModal" tabindex="-1" aria-labelledby="commentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="commentForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="commentModalLabel">
                        <i class="fe fe-message-circle me-2"></i>Add Reply
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="parent_id" id="parent_id">
                    <input type="hidden" name="news_id" id="news_id">
                    <div class="mb-3">
                        <label for="comment_text" class="form-label fw-bold">Your Reply</label>
                        <textarea
                            name="comment"
                            id="comment_text"
                            class="form-control"
                            rows="5"
                            placeholder="Write your reply here..."
                            required
                        ></textarea>
                        <div class="form-text">Be respectful and constructive in your reply.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fe fe-x me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fe fe-send me-1"></i>Submit Reply
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Comment Modal -->
<div class="modal fade" id="editCommentModal" tabindex="-1" aria-labelledby="editCommentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editCommentForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCommentModalLabel">
                        <i class="fe fe-edit me-2"></i>Edit Comment
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="comment_id" id="edit_comment_id">
                    <div class="mb-3">
                        <label for="edit_comment_text" class="form-label fw-bold">Comment Text</label>
                        <textarea
                            name="comment"
                            id="edit_comment_text"
                            class="form-control"
                            rows="5"
                            required
                        ></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fe fe-x me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fe fe-check me-1"></i>Update Comment
                    </button>
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

    // Initialize DataTable with custom styling
    let table = $('#datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.comment.index') }}",
            data: function(d) {
                // Add any additional parameters here
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'commenter', name: 'commenter' },
            { data: 'comment', name: 'comment', orderable: false, searchable: false },
            { data: 'post', name: 'post' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        language: {
            emptyTable: '<div class="empty-state"><i class="fe fe-message-square"></i><p>No comments found</p></div>',
            zeroRecords: '<div class="empty-state"><i class="fe fe-search"></i><p>No matching comments found</p></div>',
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
        },
        drawCallback: function() {
            // Update stats after table loads
            updateStats();
        }
    });

    // Refresh table button
    $('#refreshTable').on('click', function() {
        table.ajax.reload(null, false);
        $(this).find('i').addClass('fa-spin');
        setTimeout(() => {
            $(this).find('i').removeClass('fa-spin');
        }, 1000);
    });

    // Update statistics
    function updateStats() {
        // You can fetch these from your backend
        $.get("{{ route('admin.comment.stats') }}", function(data) {
            $('#totalComments').text(data.total || 0);
            $('#totalReplies').text(data.replies || 0);
            $('#todayComments').text(data.today || 0);
            $('#activeUsers').text(data.users || 0);
        }).fail(function() {
            // Fallback if stats endpoint doesn't exist
            console.log('Stats endpoint not configured');
        });
    }

    // Open reply modal
    $(document).on('click', '.reply-comment', function(){
        $('#parent_id').val($(this).data('id'));
        $('#news_id').val($(this).data('news-id'));
        $('#comment_text').val('');
        $('#commentModal').modal('show');
    });

    // Submit reply / new comment
    $('#commentForm').submit(function(e){
        e.preventDefault();
        let submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i>Sending...');

        $.post("{{ route('admin.comment.store') }}", $(this).serialize(), function(resp){
            $('#commentModal').modal('hide');
            table.ajax.reload(null, false);
            toastr.success(resp.message || 'Reply added successfully!');
            $('#commentForm')[0].reset();
        }).fail(function(xhr) {
            toastr.error(xhr.responseJSON?.message || 'Failed to add reply');
        }).always(function() {
            submitBtn.prop('disabled', false).html('<i class="fe fe-send me-1"></i>Submit Reply');
        });
    });

    // Edit comment / reply
    $(document).on('click', '.edit-comment, .edit-reply', function(){
        let id = $(this).data('id');
        $.get("/admin/comment/" + id + "/edit", function(resp){
            $('#edit_comment_id').val(resp.id);
            $('#edit_comment_text').val(resp.comment);
            $('#editCommentModal').modal('show');
        }).fail(function() {
            toastr.error('Failed to load comment');
        });
    });

    // Submit edit
    $('#editCommentForm').submit(function(e){
        e.preventDefault();
        let id = $('#edit_comment_id').val();
        let submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i>Updating...');

        $.ajax({
            url: "/admin/comment/" + id,
            type: "PUT",
            data: $(this).serialize(),
            success: function(resp){
                $('#editCommentModal').modal('hide');
                table.ajax.reload(null, false);
                toastr.success(resp.message || 'Comment updated successfully!');
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Failed to update comment');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fe fe-check me-1"></i>Update Comment');
            }
        });
    });

    // Delete comment / reply
    $(document).on('click', '.delete-comment, .delete-reply', function(){
        let id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fe fe-trash-2 me-1"></i>Yes, delete it!',
            cancelButtonText: '<i class="fe fe-x me-1"></i>Cancel'
        }).then((result)=>{
            if(result.isConfirmed){
                $.ajax({
                    url: "/admin/comments/" + id,
                    type: "DELETE",
                    success: function(resp){
                        table.ajax.reload(null, false);
                        Swal.fire(
                            'Deleted!',
                            resp.message || 'Comment has been deleted.',
                            'success'
                        );
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            xhr.responseJSON?.message || 'Failed to delete comment',
                            'error'
                        );
                    }
                });
            }
        });
    });

    // Initial stats load
    updateStats();

});
</script>
@endpush
