@extends('backend.app', ['title' => 'News'])

@push('styles')
<link href="{{ asset('default/datatable.css') }}" rel="stylesheet" />
@endpush

@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">News</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ url('admin/dashboard') }}">
                                <i class="fe fe-home me-2 fs-14"></i>Home
                            </a>
                        </li>
                        <li class="breadcrumb-item">News</li>
                        <li class="breadcrumb-item active">Index</li>
                    </ol>
                </div>
            </div>
            <!-- PAGE-HEADER END -->

            <!-- ROW -->
            <div class="row">
                <div class="col-12">
                    <div class="card product-sales-main">

                        <div class="card-header border-bottom">
                            <div class="card-options ms-auto">
                                <a href="{{ route('admin.news.create') }}" class="btn btn-primary btn-sm">Add News</a>
                            </div>
                        </div>

                        <div class="card-body">
                            <table class="table table-bordered text-nowrap border-bottom" id="datatable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Thumbnail</th>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
            <!-- ROW END -->

        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
$(document).ready(function () {

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        }
    });

    if (!$.fn.DataTable.isDataTable('#datatable')) {
        $('#datatable').DataTable({
            order: [],
            lengthMenu: [[10, 25, 50, 100, -1],[10, 25, 50, 100, "All"]],
            processing: true,
            serverSide: true,
            responsive: true,

            language: {
                processing: `<div class="text-center">
                    <img src="{{ asset('default/loader.gif') }}" style="width:50px">
                </div>`
            },

            ajax: {
                url: "{{ route('admin.news.index') }}",
                type: "GET",
            },

            columns: [
                { data: 'DT_RowIndex', orderable:false, searchable:false },
                { data: 'thumbnail', orderable:false, searchable:false },
                { data: 'title', name:'title' },
                { data: 'status', orderable:false, searchable:false },
                { data: 'action', orderable:false, searchable:false, className:'text-center' },
            ],
        });
    }
});

/* ================= STATUS ================= */
function showStatusChangeAlert(id) {
    event.preventDefault();

    Swal.fire({
        title: 'Are you sure?',
        text: 'You want to update the status?',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Yes',
    }).then((result) => {
        if (result.isConfirmed) {
            statusChange(id);
        }
    });
}

function statusChange(id) {
    NProgress.start();
    let url = "{{ route('admin.news.status', ':id') }}";

    $.get(url.replace(':id', id), function (resp) {
        NProgress.done();
        toastr.success(resp.message);
        $('#datatable').DataTable().ajax.reload();
    });
}

/* ================= DELETE ================= */
function showDeleteConfirm(id) {
    event.preventDefault();

    Swal.fire({
        title: 'Are you sure?',
        text: 'This data will be deleted permanently!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
    }).then((result) => {
        if (result.isConfirmed) {
            deleteItem(id);
        }
    });
}

function deleteItem(id) {
    NProgress.start();
    let url = "{{ route('admin.news.destroy', ':id') }}";

    $.ajax({
        type: "DELETE",
        url: url.replace(':id', id),
        success: function (resp) {
            NProgress.done();
            toastr.success(resp.message);
            $('#datatable').DataTable().ajax.reload();
        }
    });
}

/* ================= EDIT ================= */
function goToEdit(id) {
    let url = "{{ route('admin.news.edit', ':id') }}";
    window.location.href = url.replace(':id', id);
}
</script>
@endpush
