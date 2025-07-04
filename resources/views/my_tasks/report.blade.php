@extends('layouts.app')

@section('content')
<div class="nk-content">

<div class="container">
    <h4 class="mb-4">
        My Tasks for 
        {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} 
        to 
        {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
    </h4>
    
    <!-- Filter Form (without staff filter) -->
    <form id="assignmentFilterForm" method="GET" action="{{ route('my_tasks.report') }}" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="form-control">
        </div>
        <div class="col-md-4">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="form-control">
        </div>
        <div class="col-md-4">
            <label for="filterStatus" class="form-label">Status</label>
            <select id="filterStatus" class="form-control" name="filter_status">
                <option value="">All</option>
                <option value="0">Pending</option>
                <option value="1">Completed</option>
            </select>
        </div>
        <div class="col-md-12">
            <button type="button" class="btn btn-primary" id="applyFilterBtn">Apply Filters</button>
            <button type="reset" class="btn btn-secondary" id="clearFilterBtn">Clear Filters</button>
        </div>
    </form>
    
    <!-- Task Assignments List Section -->
    <div id="assignmentsList">
        @if($assignments->count())
            @foreach($assignments as $assignment)
                <div class="card mb-3 assignment-card" 
                     data-date="{{ \Carbon\Carbon::parse($assignment->date)->toDateString() }}" 
                     data-status="{{ $assignment->status }}">
                    <div class="card-header">
                        <strong>Staff:</strong> 
                        {{ optional($assignment->staff)->first_name }} {{ optional($assignment->staff)->middle_name }} {{ optional($assignment->staff)->last_name }}
                        | <strong>Date:</strong> {{ \Carbon\Carbon::parse($assignment->date)->format('d M Y') }}
                        | <strong>Status:</strong> {{ $assignment->status == 0 ? 'Pending' : 'Completed' }}
                        | <strong>Project:</strong> {{ optional(optional($assignment->task)->project)->name ?? 'N/A' }}
                        | <strong>Service:</strong> {{ optional(optional($assignment->task)->service)->name ?? 'N/A' }}
                    </div>
                    <div class="card-body">
                        <h6>Documents</h6>
                        @if($assignment->documents->count())
                            <ul>
                                @foreach($assignment->documents as $document)
                                    <li>
                                        <a href="{{ asset($document->file_path) }}" target="_blank">{{ $document->document_name }}</a>
                                        - {{ $document->description }}
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p>No documents uploaded.</p>
                        @endif

                        <h6>Comments</h6>
                        @if($assignment->comments->count())
                            <ul>
                                @foreach($assignment->comments as $comment)
                                    <li>
                                        <strong>{{ optional($comment->user)->first_name }} {{ optional($comment->user)->middle_name }} {{ optional($comment->user)->last_name }}:</strong>
                                        {{ $comment->comment }}
                                        <small> ({{ \Carbon\Carbon::parse($comment->created_at)->format('d M Y H:i') }})</small>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p>No comments.</p>
                        @endif
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-sm btn-info uploadDocumentBtn" data-assignment="{{ $assignment->id }}">Upload Document</button>
                        <button class="btn btn-sm btn-secondary addCommentBtn" data-assignment="{{ $assignment->id }}">Add Comment</button>
                    </div>
                </div>
            @endforeach
        @else
            <p>No task assignments found for the selected date range.</p>
        @endif
    </div>
</div>
</div>


<!-- Edit Assignment Modal -->
<div class="modal fade" id="editAssignmentModal" tabindex="-1" aria-labelledby="editAssignmentModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" id="editAssignmentContent">
      <!-- Content loaded via Ajax -->
    </div>
  </div>
</div>

<!-- Upload Document Modal -->
<div class="modal fade" id="uploadDocumentModal" tabindex="-1" aria-labelledby="uploadDocumentModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" id="uploadDocumentContent">
      <!-- Content loaded via Ajax -->
    </div>
  </div>
</div>

<!-- Add Comment Modal -->
<div class="modal fade" id="addCommentModal" tabindex="-1" aria-labelledby="addCommentModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" id="addCommentContent">
      <!-- Content loaded via Ajax -->
    </div>
  </div>
</div>


<script src="{{ asset('assets1/jquery.min.js') }}"></script>
<script>
$(document).ready(function(){
    // Client-side filtering using date range and status only.
    $('#applyFilterBtn').click(function(){
        var startDate = $('#start_date').val();
        var endDate   = $('#end_date').val();
        var status    = $('#filterStatus').val();
        
        $('.assignment-card').each(function(){
            var card = $(this);
            var cardDate = card.data('date'); // e.g., "2025-03-12"
            var cardStatus = card.data('status').toString(); // e.g., "0" or "1"
            
            var show = true;
            if(startDate && cardDate < startDate) show = false;
            if(endDate && cardDate > endDate) show = false;
            if(status !== "" && cardStatus !== status) show = false;
            
            show ? card.show() : card.hide();
        });
    });
    
    $('#clearFilterBtn').click(function(){
        $('#assignmentFilterForm')[0].reset();
        $('.assignment-card').show();
    });
    
    // --- Ajax functionalities (these remain unchanged) ---
    
    // Edit Assignment Modal (if needed)
    $(document).on('click', '.editAssignmentBtn', function(){
        var assignmentId = $(this).data('assignment');
        $.ajax({
            url: "/assignments/" + assignmentId + "/edit",
            type: "GET",
            success: function(response){
                $('#editAssignmentContent').html(response);
                $('#editAssignmentModal').modal('show');
            },
            error: function(xhr){
                console.log("Error loading edit assignment form:", xhr.responseText);
            }
        });
    });
    
    // Upload Document Modal
    $(document).on('click', '.uploadDocumentBtn', function(){
        var assignmentId = $(this).data('assignment');
        if (!assignmentId) {
            alert("Assignment ID is undefined.");
            return;
        }
        $.ajax({
            url: "/assignments/" + assignmentId + "/upload-document",
            type: "GET",
            success: function(response){
                $('#uploadDocumentContent').html(response);
                $('#uploadDocumentModal').modal('show');
            },
            error: function(xhr){
                console.log("Error loading upload document form:", xhr.responseText);
            }
        });
    });
    
    // Upload Document Form Submission
    $(document).on('click', '#uploadDocumentBtn', function(){
        var formData = new FormData($('#uploadDocumentForm')[0]);
        var assignmentId = $('#uploadDocumentForm input[name="task_assigned_id"]').val();
        if (!assignmentId) {
            alert("Error: Assignment ID is missing.");
            return;
        }
        $.ajax({
            url: "/assignments/" + assignmentId + "/upload-document",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response){
                alert(response.message);
                $('#uploadDocumentModal').modal('hide');
                location.reload();
            },
            error: function(xhr){
                console.log("Error uploading document:", xhr.responseText);
                alert("Failed to upload document.");
            }
        });
    });
    
    // Add Comment Modal
    $(document).on('click', '.addCommentBtn', function(){
        var assignmentId = $(this).data('assignment');
        $.ajax({
            url: "/assignments/" + assignmentId + "/add-comment",
            type: "GET",
            success: function(response){
                $('#addCommentContent').html(response);
                $('#addCommentModal').modal('show');
            },
            error: function(xhr){
                console.log("Error loading add comment form:", xhr.responseText);
            }
        });
    });
    
    // Add Comment Form Submission
    $(document).on('click', '#addCommentBtn', function(){
        var formData = $('#addCommentForm').serialize();
        var assignmentId = $('#addCommentForm input[name="assignment_id"]').val();
        $.ajax({
            url: "/assignments/" + assignmentId + "/add-comment",
            type: "POST",
            data: formData,
            success: function(response){
                alert(response.message);
                $('#addCommentModal').modal('hide');
                location.reload();
            },
            error: function(xhr){
                console.log("Error adding comment:", xhr.responseText);
            }
        });
    });
    
    // Delete Assignment (if needed)
    $(document).on('click', '.deleteAssignmentBtn', function(e) {
        e.preventDefault();
        var assignmentId = $(this).attr('data-assignment');
        if (!assignmentId) {
            alert("Assignment ID is undefined.");
            return;
        }
        if (confirm("Are you sure you want to delete this assignment?")) {
            $.ajax({
                url: "/assignments/" + assignmentId,
                type: "DELETE",
                data: { _token: "{{ csrf_token() }}" },
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert("Failed to delete assignment.");
                    }
                },
                error: function(xhr) {
                    console.log("Error deleting assignment:", xhr.responseText);
                    alert("Error deleting assignment.");
                }
            });
        }
    });
});
</script>
@endsection
