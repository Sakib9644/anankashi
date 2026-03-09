@extends('backend.app', ['title' => 'Comments'])

@push('styles')
<link href="{{ asset('default/datatable.css') }}" rel="stylesheet" />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
<style>

/* ═══════════════════════════════════════════════════════
   DESIGN TOKENS
═══════════════════════════════════════════════════════ */
:root {
  --bg:             #f5f4f0;
  --surface:        #ffffff;
  --surface-alt:    #f0ede8;
  --border:         #e4e1db;
  --border-mid:     #ccc9c2;
  --text-primary:   #1c1b18;
  --text-secondary: #6b6860;
  --text-muted:     #a8a49c;
  --accent:         #c17f3b;
  --accent-soft:    #f5ead9;
  --accent-dark:    #9e6428;
  --danger:         #b94040;
  --danger-soft:    #faeaea;
  --success:        #3a7d5a;
  --success-soft:   #e6f4ec;
  --info:           #3a6b9e;
  --info-soft:      #e6eef7;
  --radius-xs:      4px;
  --radius-sm:      6px;
  --radius-md:      10px;
  --radius-lg:      14px;
  --radius-xl:      18px;
  --shadow-card:    0 1px 3px rgba(0,0,0,.06), 0 4px 12px rgba(0,0,0,.04);
  --shadow-hover:   0 2px 8px rgba(0,0,0,.09), 0 8px 24px rgba(0,0,0,.06);
  --shadow-modal:   0 20px 60px rgba(0,0,0,.18), 0 4px 16px rgba(0,0,0,.08);
  --font-sans:      'DM Sans', system-ui, sans-serif;
  --font-display:   'DM Serif Display', Georgia, serif;
  --transition:     180ms cubic-bezier(.4,0,.2,1);
}

/* ═══════════════════════════════════════════════════════
   PAGE CHROME
═══════════════════════════════════════════════════════ */
body {
  font-family: var(--font-sans) !important;
  background: var(--bg) !important;
}

/* Page header */
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 24px 0 20px;
  margin-bottom: 0;
  border-bottom: none;
}

.page-title {
  font-family: var(--font-display) !important;
  font-size: 26px !important;
  font-weight: 400 !important;
  color: var(--text-primary) !important;
  letter-spacing: -.4px;
  margin: 0;
}

/* Breadcrumb */
.breadcrumb {
  margin-bottom: 0;
  background: none;
  padding: 0;
}
.breadcrumb-item a {
  color: var(--text-secondary);
  font-size: 12.5px;
  text-decoration: none;
  transition: color var(--transition);
}
.breadcrumb-item a:hover { color: var(--accent); }
.breadcrumb-item.active { color: var(--text-muted); font-size: 12.5px; }
.breadcrumb-item + .breadcrumb-item::before { color: var(--border-mid); }

/* ═══════════════════════════════════════════════════════
   CARD
═══════════════════════════════════════════════════════ */
.card {
  background: var(--surface) !important;
  border: 1px solid var(--border) !important;
  border-radius: var(--radius-lg) !important;
  box-shadow: var(--shadow-card) !important;
  overflow: hidden;
  margin-top: 20px;
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 24px 16px !important;
  background: var(--surface) !important;
  border-bottom: 1.5px solid var(--border) !important;
}

.card-title {
  font-family: var(--font-sans) !important;
  font-size: 14px !important;
  font-weight: 600 !important;
  color: var(--text-primary) !important;
  letter-spacing: .1px;
  text-transform: uppercase;
  margin: 0;
}

.card-body {
  padding: 20px 24px !important;
}

/* ═══════════════════════════════════════════════════════
   DATATABLE OVERRIDES
═══════════════════════════════════════════════════════ */
#datatable {
  font-family: var(--font-sans) !important;
  border-collapse: collapse !important;
  font-size: 13.5px;
}

#datatable thead tr {
  background: var(--surface-alt) !important;
}

#datatable thead th {
  font-size: 11px !important;
  font-weight: 600 !important;
  text-transform: uppercase !important;
  letter-spacing: .7px !important;
  color: var(--text-secondary) !important;
  padding: 12px 14px !important;
  border: none !important;
  border-bottom: 1.5px solid var(--border) !important;
  white-space: nowrap;
}

#datatable tbody tr {
  transition: background var(--transition);
  border-bottom: 1px solid var(--border) !important;
}

#datatable tbody tr:last-child { border-bottom: none !important; }

#datatable tbody tr:hover { background: var(--surface-alt) !important; }

#datatable tbody td {
  padding: 14px 14px !important;
  border: none !important;
  color: var(--text-primary);
  vertical-align: middle;
}

/* DataTables controls */
.dataTables_wrapper .dataTables_length select,
.dataTables_wrapper .dataTables_filter input {
  border: 1px solid var(--border) !important;
  border-radius: var(--radius-sm) !important;
  padding: 6px 10px !important;
  font-family: var(--font-sans) !important;
  font-size: 13px !important;
  color: var(--text-primary) !important;
  background: var(--surface) !important;
  outline: none;
  transition: border-color var(--transition), box-shadow var(--transition);
}

.dataTables_wrapper .dataTables_filter input:focus {
  border-color: var(--accent) !important;
  box-shadow: 0 0 0 3px var(--accent-soft) !important;
}

.dataTables_wrapper .dataTables_info {
  font-size: 12.5px !important;
  color: var(--text-muted) !important;
  font-family: var(--font-sans) !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
  border-radius: var(--radius-sm) !important;
  font-family: var(--font-sans) !important;
  font-size: 13px !important;
  padding: 4px 10px !important;
  color: var(--text-secondary) !important;
  border: 1px solid transparent !important;
  transition: all var(--transition) !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
  background: var(--surface-alt) !important;
  border-color: var(--border) !important;
  color: var(--text-primary) !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current,
.dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
  background: var(--text-primary) !important;
  color: #fff !important;
  border-color: var(--text-primary) !important;
}

/* ═══════════════════════════════════════════════════════
   COMMENT CELL — 3-level visual hierarchy
═══════════════════════════════════════════════════════ */
.comment-cell { max-width: 420px; }

/* ── LEVEL 1: Main Comment ───────────────────────────
   Amber left bar · warm cream bg · bold dark text
─────────────────────────────────────────────────── */
.comment-main-wrap {
  background: #fffdf8;
  border: 1px solid #e8c98a;
  border-left: 4px solid var(--accent);
  border-radius: var(--radius-md);
  padding: 10px 13px;
  position: relative;
}

.comment-meta-inline {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 6px;
}

.cmeta-avatar {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--accent), #e09a55);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  color: #fff;
  font-weight: 600;
  flex-shrink: 0;
}

.cmeta-name { font-size: 12px; font-weight: 600; color: var(--text-primary); }
.cmeta-time { font-size: 11px; color: var(--text-muted); }

/* Level 1 type label */
.cmeta-level-badge {
  font-size: 9.5px;
  font-weight: 700;
  letter-spacing: .5px;
  text-transform: uppercase;
  border-radius: 3px;
  padding: 1px 6px;
  margin-left: 2px;
}
.badge-main  { background: var(--accent); color: #fff; }
.badge-reply { background: #3a6b9e;       color: #fff; }
.badge-nested{ background: #3a7d5a;       color: #fff; }

/* Level 1 comment body: bold, darkest */
.comment-body-main {
  font-size: 13px;
  font-weight: 500;
  color: var(--text-primary);
  line-height: 1.55;
}

/* ── LEVEL 2: Replies ────────────────────────────────
   Blue-slate left bar · soft blue bg · italic text
─────────────────────────────────────────────────── */
.reply-list {
  list-style: none;
  padding: 0;
  margin: 8px 0 0 16px;
  border-left: 3px solid #a8c0db;
  padding-left: 11px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.reply-item {
  background: #f0f5fb;
  border: 1px solid #cddaec;
  border-radius: var(--radius-sm);
  padding: 8px 11px;
}

.reply-meta { display: flex; align-items: center; gap: 5px; margin-bottom: 5px; flex-wrap: wrap; }
.reply-author { font-size: 12px; font-weight: 600; color: #2a4d6e; }
.reply-time   { font-size: 11px; color: var(--text-muted); }

/* Level 2 reply body: medium, blue-toned, italic */
.reply-body {
  font-size: 12.5px;
  font-weight: 400;
  color: #3d5a78;
  line-height: 1.5;
  font-style: italic;
}

/* ── LEVEL 3: Reply-to-Reply ─────────────────────────
   Green dashed bar · mint bg · light small text
─────────────────────────────────────────────────── */
.subreply-list {
  list-style: none;
  padding: 0;
  margin: 7px 0 0 10px;
  border-left: 2px dashed #85c4a0;
  padding-left: 10px;
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.subreply-item {
  background: #f0faf5;
  border: 1px solid #b8dfc9;
  border-radius: var(--radius-xs);
  padding: 7px 10px;
}

.subreply-meta { display: flex; align-items: center; gap: 5px; margin-bottom: 4px; flex-wrap: wrap; }
.subreply-author { font-size: 11.5px; font-weight: 600; color: #2a5a3e; }
.subreply-time   { font-size: 10.5px; color: var(--text-muted); }

/* Level 3 body: smallest, green-toned, lightest weight */
.subreply-body {
  font-size: 12px;
  font-weight: 300;
  color: #3a5a47;
  line-height: 1.45;
}

/* ═══════════════════════════════════════════════════════
   ACTION BUTTONS (in table)
═══════════════════════════════════════════════════════ */
.action-group {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}

.btn-icon {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  border: 1px solid transparent;
  border-radius: var(--radius-sm);
  padding: 5px 11px;
  font-family: var(--font-sans);
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  white-space: nowrap;
  transition: all var(--transition);
  text-decoration: none;
  line-height: 1;
}

.btn-icon svg {
  width: 12px;
  height: 12px;
  stroke: currentColor;
  fill: none;
  stroke-width: 2;
  stroke-linecap: round;
  stroke-linejoin: round;
  flex-shrink: 0;
}

/* Reply */
.btn-reply {
  background: var(--info-soft);
  color: var(--info);
  border-color: #c0d4ec;
}
.btn-reply:hover {
  background: var(--info);
  color: #fff;
  border-color: var(--info);
}

/* Edit */
.btn-edit {
  background: var(--accent-soft);
  color: var(--accent-dark);
  border-color: #e8c98a;
}
.btn-edit:hover {
  background: var(--accent);
  color: #fff;
  border-color: var(--accent);
}

/* Delete */
.btn-delete {
  background: var(--danger-soft);
  color: var(--danger);
  border-color: #e8b4b4;
}
.btn-delete:hover {
  background: var(--danger);
  color: #fff;
  border-color: var(--danger);
}

/* ═══════════════════════════════════════════════════════
   POST CELL
═══════════════════════════════════════════════════════ */
.post-cell {
  max-width: 180px;
}

.post-title-link {
  font-size: 13px;
  font-weight: 500;
  color: var(--text-primary);
  text-decoration: none;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-height: 1.45;
  transition: color var(--transition);
}
.post-title-link:hover { color: var(--accent); }

/* ═══════════════════════════════════════════════════════
   COMMENTER CELL
═══════════════════════════════════════════════════════ */
.commenter-cell {
  display: flex;
  align-items: center;
  gap: 9px;
  min-width: 120px;
}

.commenter-avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--accent), #e09a55);
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: var(--font-display);
  font-size: 14px;
  color: #fff;
  flex-shrink: 0;
  letter-spacing: 0;
}

.commenter-info { display: flex; flex-direction: column; gap: 1px; }
.commenter-name { font-size: 13px; font-weight: 600; color: var(--text-primary); }
.commenter-role {
  font-size: 11px;
  color: var(--text-muted);
  font-weight: 400;
}

/* Row index */
.row-index {
  font-size: 12px;
  font-weight: 600;
  color: var(--text-muted);
  font-variant-numeric: tabular-nums;
}

/* ═══════════════════════════════════════════════════════
   MODALS
═══════════════════════════════════════════════════════ */
.modal-backdrop.show { opacity: .4; }

.modal-dialog { max-width: 520px; }

.modal-content {
  border: 1px solid var(--border) !important;
  border-radius: var(--radius-xl) !important;
  box-shadow: var(--shadow-modal) !important;
  overflow: hidden;
  font-family: var(--font-sans);
}

.modal-header {
  padding: 20px 24px 18px !important;
  border-bottom: 1.5px solid var(--border) !important;
  background: var(--surface);
}

.modal-title {
  font-family: var(--font-display) !important;
  font-size: 20px !important;
  font-weight: 400 !important;
  color: var(--text-primary) !important;
  letter-spacing: -.2px;
}

.modal-body {
  padding: 24px !important;
  background: var(--bg);
}

.modal-footer {
  padding: 16px 24px !important;
  border-top: 1.5px solid var(--border) !important;
  background: var(--surface);
  gap: 8px;
}

/* Modal form textarea */
.modal-body .form-control {
  border: 1.5px solid var(--border) !important;
  border-radius: var(--radius-md) !important;
  padding: 12px 14px !important;
  font-family: var(--font-sans) !important;
  font-size: 14px !important;
  font-weight: 300 !important;
  color: var(--text-primary) !important;
  background: var(--surface) !important;
  line-height: 1.6 !important;
  resize: none !important;
  transition: border-color var(--transition), box-shadow var(--transition) !important;
}

.modal-body .form-control:focus {
  border-color: var(--accent) !important;
  box-shadow: 0 0 0 3px var(--accent-soft) !important;
  outline: none !important;
}

.modal-body .form-control::placeholder {
  color: var(--text-muted) !important;
  font-style: italic !important;
}

/* Modal form label */
.modal-label {
  font-size: 12px;
  font-weight: 600;
  letter-spacing: .5px;
  text-transform: uppercase;
  color: var(--text-secondary);
  margin-bottom: 8px;
  display: block;
}

/* Modal buttons */
.modal-footer .btn-primary-modal {
  background: var(--text-primary);
  color: #fff;
  border: none;
  border-radius: var(--radius-sm);
  padding: 9px 20px;
  font-family: var(--font-sans);
  font-size: 13.5px;
  font-weight: 500;
  cursor: pointer;
  transition: background var(--transition), transform var(--transition);
}
.modal-footer .btn-primary-modal:hover { background: #2d2c29; transform: translateY(-1px); }
.modal-footer .btn-primary-modal:active { transform: translateY(0); }

.modal-footer .btn-secondary-modal {
  background: transparent;
  color: var(--text-secondary);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  padding: 9px 16px;
  font-family: var(--font-sans);
  font-size: 13.5px;
  font-weight: 500;
  cursor: pointer;
  transition: all var(--transition);
}
.modal-footer .btn-secondary-modal:hover {
  color: var(--text-primary);
  border-color: var(--border-mid);
  background: var(--surface-alt);
}

/* btn-close override */
.btn-close {
  opacity: .5;
  transition: opacity var(--transition);
}
.btn-close:hover { opacity: 1; }

/* ═══════════════════════════════════════════════════════
   STATS ROW (optional summary cards)
═══════════════════════════════════════════════════════ */
.stats-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 14px;
  margin-bottom: 20px;
}

.stat-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  padding: 16px 18px;
  box-shadow: var(--shadow-card);
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.stat-label {
  font-size: 11px;
  font-weight: 600;
  letter-spacing: .6px;
  text-transform: uppercase;
  color: var(--text-muted);
}

.stat-value {
  font-family: var(--font-display);
  font-size: 28px;
  color: var(--text-primary);
  line-height: 1;
  letter-spacing: -1px;
}

.stat-card.accent-card { border-left: 3px solid var(--accent); }
.stat-card.danger-card { border-left: 3px solid var(--danger); }
.stat-card.success-card { border-left: 3px solid var(--success); }

/* ═══════════════════════════════════════════════════════
   BADGE
═══════════════════════════════════════════════════════ */
.badge-count {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 20px;
  height: 20px;
  padding: 0 6px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  background: var(--surface-alt);
  color: var(--text-secondary);
  border: 1px solid var(--border);
}

.badge-count.has-replies {
  background: var(--accent-soft);
  color: var(--accent-dark);
  border-color: #e8c98a;
}
</style>
@endpush

@section('content')
<div class="app-content main-content mt-0">
  <div class="side-app">
    <div class="main-container container-fluid">

      <!-- PAGE HEADER -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Comments</h1>
          <ol class="breadcrumb mt-1">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Comments</li>
          </ol>
        </div>
      </div>

      <!-- STATS ROW -->
      <div class="stats-row">
        <div class="stat-card accent-card">
          <span class="stat-label">Total Comments</span>
          <span class="stat-value" id="stat-total">—</span>
        </div>
        <div class="stat-card success-card">
          <span class="stat-label">Approved</span>
          <span class="stat-value" id="stat-approved">—</span>
        </div>
        <div class="stat-card danger-card">
          <span class="stat-label">Replies</span>
          <span class="stat-value" id="stat-replies">—</span>
        </div>
      </div>

      <!-- MAIN TABLE CARD -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Comments List</h3>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table id="datatable" class="table text-nowrap w-100">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Post</th>
                      <th>Commenter</th>
                      <th>Comment &amp; Replies</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div><!-- /.main-container -->
  </div>
</div>

<!-- ─── ADD / REPLY COMMENT MODAL ─────────────────────── -->
<div class="modal fade" id="commentModal" tabindex="-1" aria-labelledby="commentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="commentForm">
        <div class="modal-header">
          <h5 class="modal-title" id="commentModalLabel">Add Reply</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="parent_id" id="parent_id">
          <input type="hidden" name="news_id" id="news_id">
          <label class="modal-label" for="comment_text">Your reply</label>
          <textarea
            name="comment"
            id="comment_text"
            class="form-control"
            rows="5"
            placeholder="Write your reply here…"
          ></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-secondary-modal" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn-primary-modal">Post Reply</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ─── EDIT COMMENT MODAL ────────────────────────────── -->
<div class="modal fade" id="editCommentModal" tabindex="-1" aria-labelledby="editCommentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="editCommentForm">
        <div class="modal-header">
          <h5 class="modal-title" id="editCommentModalLabel">Edit Comment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="comment_id" id="edit_comment_id">
          <label class="modal-label" for="edit_comment_text">Edit content</label>
          <textarea
            name="comment"
            id="edit_comment_text"
            class="form-control"
            rows="5"
          ></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-secondary-modal" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn-primary-modal">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {

  // ── CSRF setup ──────────────────────────────────────
  $.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

  // ── DataTable init ───────────────────────────────────
  let table = $('#datatable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "{{ route('admin.comment.index') }}",
      dataSrc: function (json) {
        // Update stat cards if server sends counts
        if (json.stats) {
          $('#stat-total').text(json.stats.total ?? '—');
          $('#stat-approved').text(json.stats.approved ?? '—');
          $('#stat-replies').text(json.stats.replies ?? '—');
        }
        return json.data;
      }
    },
    columns: [
      {
        data: 'DT_RowIndex',
        name: 'DT_RowIndex',
        orderable: false,
        searchable: false,
        render: (d) => `<span class="row-index">${d}</span>`
      },
      {
        data: 'post',
        name: 'post',
        render: (d) => `<div class="post-cell">
          <a href="#" class="post-title-link">${d}</a>
        </div>`
      },
      {
        data: 'commenter',
        name: 'commenter',
        render: (d, type, row) => {
          const initial = (typeof d === 'string' && d.length > 0) ? d[0].toUpperCase() : '?';
          return `<div class="commenter-cell">
            <div class="commenter-avatar">${initial}</div>
            <div class="commenter-info">
              <span class="commenter-name">${d}</span>
              <span class="commenter-role">Visitor</span>
            </div>
          </div>`;
        }
      },
      {
        data: 'comment',
        name: 'comment',
        orderable: false,
        searchable: true,
        render: (d) => d  // server returns pre-built HTML via renderComment()
      },
      {
        data: 'action',
        name: 'action',
        orderable: false,
        searchable: false
      }
    ],
    language: {
      processing: `<div style="display:flex;align-items:center;gap:8px;font-family:var(--font-sans);font-size:13px;color:var(--text-muted)">
        <svg width="16" height="16" viewBox="0 0 24 24" stroke="var(--accent)" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
        </svg>
        Loading…
      </div>`,
      emptyTable: `<div style="padding:32px 0;text-align:center;font-family:var(--font-sans);color:var(--text-muted);font-size:14px">
        No comments found.
      </div>`
    },
    dom: "<'row align-items-center mb-3'<'col-sm-6'l><'col-sm-6 d-flex justify-content-end'f>>" +
         "<'row'<'col-12'tr>>" +
         "<'row align-items-center mt-3'<'col-sm-5'i><'col-sm-7 d-flex justify-content-end'p>>",
    pageLength: 15,
    lengthMenu: [10, 15, 25, 50],
    drawCallback: function () {
      // Re-init tooltips if using Bootstrap tooltips
    }
  });

  // ── Open reply modal ──────────────────────────────────
  $(document).on('click', '.reply-comment', function () {
    const id       = $(this).data('id');
    const newsId   = $(this).data('news-id');
    const author   = $(this).data('author') || '';
    $('#parent_id').val(id);
    $('#news_id').val(newsId);
    $('#commentModalLabel').text(author ? `Reply to ${author}` : 'Add Reply');
    $('#comment_text').val('');
    $('#commentModal').modal('show');
    setTimeout(() => $('#comment_text').focus(), 300);
  });

  // ── Submit new reply ──────────────────────────────────
  $('#commentForm').submit(function (e) {
    e.preventDefault();
    const $btn = $(this).find('[type=submit]');
    $btn.prop('disabled', true).text('Posting…');

    $.post("{{ route('admin.comment.store') }}", $(this).serialize())
      .done(function (resp) {
        $('#commentModal').modal('hide');
        table.ajax.reload(null, false);
        toastr.success(resp.message || 'Reply posted.');
        $('#commentForm')[0].reset();
      })
      .fail(function () {
        toastr.error('Something went wrong. Please try again.');
      })
      .always(function () {
        $btn.prop('disabled', false).text('Post Reply');
      });
  });

  // ── Open edit modal ───────────────────────────────────
  $(document).on('click', '.edit-comment, .edit-reply', function () {
    const id = $(this).data('id');
    $.get('/admin/comment/' + id + '/edit')
      .done(function (resp) {
        $('#edit_comment_id').val(resp.id);
        $('#edit_comment_text').val(resp.comment);
        $('#editCommentModal').modal('show');
        setTimeout(() => $('#edit_comment_text').focus(), 300);
      })
      .fail(function () {
        toastr.error('Could not load comment data.');
      });
  });

  // ── Submit edit ───────────────────────────────────────
  $('#editCommentForm').submit(function (e) {
    e.preventDefault();
    const id   = $('#edit_comment_id').val();
    const $btn = $(this).find('[type=submit]');
    $btn.prop('disabled', true).text('Saving…');

    $.ajax({
      url:  '/admin/comment/' + id,
      type: 'PUT',
      data: $(this).serialize()
    })
      .done(function (resp) {
        $('#editCommentModal').modal('hide');
        table.ajax.reload(null, false);
        toastr.success(resp.message || 'Comment updated.');
      })
      .fail(function () {
        toastr.error('Something went wrong. Please try again.');
      })
      .always(function () {
        $btn.prop('disabled', false).text('Save Changes');
      });
  });

  // ── Delete comment / reply ────────────────────────────
  $(document).on('click', '.delete-comment, .delete-reply', function () {
    const id     = $(this).data('id');
    const isReply = $(this).hasClass('delete-reply');

    Swal.fire({
      title:             isReply ? 'Delete this reply?' : 'Delete this comment?',
      text:              'This action cannot be undone.',
      icon:              'warning',
      showCancelButton:  true,
      confirmButtonText: 'Yes, delete',
      cancelButtonText:  'Cancel',
      confirmButtonColor: '#b94040',
      customClass: {
        popup:         'swal-custom-popup',
        confirmButton: 'swal-btn-confirm',
        cancelButton:  'swal-btn-cancel'
      }
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url:  '/admin/comment/' + id,
          type: 'DELETE'
        })
          .done(function (resp) {
            table.ajax.reload(null, false);
            toastr.success(resp.message || 'Deleted successfully.');
          })
          .fail(function () {
            toastr.error('Delete failed. Please try again.');
          });
      }
    });
  });

});
</script>

{{-- ─── Helper: server-side comment HTML renderer (PHP) ───
     In your CommentController or DataTable class, use this
     pattern to build the `comment` column HTML.

     3 VISUAL LEVELS:
       Level 1 = Main Comment   → amber left bar, cream bg, bold text
       Level 2 = Reply          → blue left bar, blue bg, italic text
       Level 3 = Reply-to-Reply → green dashed bar, mint bg, light text

     private function renderComment($comment): string
     {
         $text    = e($comment->comment);
         $initial = strtoupper(substr($comment->commenter_name ?? '?', 0, 1));
         $name    = e($comment->commenter_name ?? 'Unknown');
         $time    = $comment->created_at->diffForHumans();

         // ── Level 1: Main Comment ──────────────────────────
         $html = <<<HTML
           <div class="comment-cell">
             <div class="comment-main-wrap">
               <div class="comment-meta-inline">
                 <div class="cmeta-avatar">{$initial}</div>
                 <span class="cmeta-name">{$name}</span>
                 <span class="cmeta-time">{$time}</span>
                 <span class="cmeta-level-badge badge-main">Comment</span>
               </div>
               <div class="comment-body-main">{$text}</div>
             </div>
           HTML;

         // ── Level 2: Replies ───────────────────────────────
         $replies = $comment->replies ?? [];
         if (count($replies)) {
             $html .= '<ul class="reply-list">';
             foreach ($replies as $reply) {
                 $ri   = strtoupper(substr($reply->commenter_name ?? '?', 0, 1));
                 $rn   = e($reply->commenter_name ?? 'Unknown');
                 $rt   = $reply->created_at->diffForHumans();
                 $rb   = e($reply->comment);

                 $html .= <<<HTML
                   <li class="reply-item">
                     <div class="reply-meta">
                       <span class="cmeta-level-badge badge-reply">Reply</span>
                       <span class="reply-author">{$rn}</span>
                       <span class="reply-time">· {$rt}</span>
                     </div>
                     <div class="reply-body">{$rb}</div>
                   HTML;

                 // ── Level 3: Reply-to-Reply ────────────────
                 $subreplies = $reply->replies ?? [];
                 if (count($subreplies)) {
                     $html .= '<ul class="subreply-list">';
                     foreach ($subreplies as $sub) {
                         $si  = strtoupper(substr($sub->commenter_name ?? '?', 0, 1));
                         $sn  = e($sub->commenter_name ?? 'Unknown');
                         $st  = $sub->created_at->diffForHumans();
                         $sb  = e($sub->comment);
                         $html .= <<<HTML
                           <li class="subreply-item">
                             <div class="subreply-meta">
                               <span class="cmeta-level-badge badge-nested">Nested</span>
                               <span class="subreply-author">{$sn}</span>
                               <span class="subreply-time">· {$st}</span>
                             </div>
                             <div class="subreply-body">{$sb}</div>
                           </li>
                           HTML;
                     }
                     $html .= '</ul>';
                 }

                 $html .= '</li>';
             }
             $html .= '</ul>';
         }

         $html .= '</div>'; // .comment-cell
         return $html;
     }

     And for the `action` column:

     private function renderActions($comment): string
     {
         $id     = $comment->id;
         $newsId = $comment->news_id;
         $name   = e($comment->commenter_name ?? 'user');
         return <<<HTML
           <div class="action-group">
             <button class="btn-icon btn-reply reply-comment"
                     data-id="{$id}" data-news-id="{$newsId}" data-author="{$name}">
               <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
               Reply
             </button>
             <button class="btn-icon btn-edit edit-comment" data-id="{$id}">
               <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
               Edit
             </button>
             <button class="btn-icon btn-delete delete-comment" data-id="{$id}">
               <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
               Delete
             </button>
           </div>
           HTML;
     }
--}}
@endpush
