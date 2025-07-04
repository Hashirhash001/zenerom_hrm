@extends('layouts.app')

@section('content')
<div class="nk-content">

<div class="container">
    <h4 class="mb-4">
        Today's Team Tasks Report for 
        {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} to 
        {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
    </h4>
    
    <!-- Filter Form -->
    <form id="teamTaskFilterForm" method="GET" action="{{ route('team_tasks.report') }}" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="form-control">
        </div>
        <div class="col-md-4">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="form-control">
        </div>
        <div class="col-md-4">
            <label for="staff_id" class="form-label">Staff</label>
            <select name="staff_id" id="staff_id" class="form-control">
                <option value="">All</option>
                @foreach($staffs as $staff)
                    <option value="{{ $staff->id }}" {{ ($staffId == $staff->id) ? 'selected' : '' }}>
                        {{ $staff->first_name }} {{ $staff->last_name }} ({{ $staff->employee_id }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary" id="applyFilterBtn">Apply Filters</button>
            <button type="reset" class="btn btn-secondary" id="clearFilterBtn">Clear Filters</button>
        </div>
    </form>
    
    <!-- Task Assignments List Section -->
    <div id="assignmentsList">
        @if($assignments->count())
            @foreach($assignments as $assignment)
                <div class="card mb-3 assignment-card" 
                     data-date="{{ \Carbon\Carbon::parse($assignment->date)->toDateString() }}" 
                     data-staff="{{ optional($assignment->staff)->id }}" 
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
                        <!-- Ajax buttons for Upload Document and Add Comment -->
                        <button class="btn btn-sm btn-info uploadDocumentBtn" data-assignment="{{ $assignment->id }}">Upload Document</button>
                        <button class="btn btn-sm btn-secondary addCommentBtn" data-assignment="{{ $assignment->id }}">Add Comment</button>
                    </div>
                </div>
            @endforeach
        @else
            <p>No team tasks assigned for today in your department.</p>
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
    // Client-side filtering: this example filters the assignments displayed.
    $('#applyFilterBtn').click(function(e){
        e.preventDefault();
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();
        var staffId = $('#staff_id').val();
        $('.assignment-card').each(function(){
            var card = $(this);
            var cardDate = card.data('date');
            var cardStaff = card.data('staff');
            var show = true;
            if(startDate && cardDate < startDate) show = false;
            if(endDate && cardDate > endDate) show = false;
            if(staffId && cardStaff != staffId) show = false;
            show ? card.show() : card.hide();
        });
    });
    
    $('#clearFilterBtn').click(function(){
        $('#teamTaskFilterForm')[0].reset();
        $('.assignment-card').show();
    });
    
    // Ajax for Upload Document Modal
    $(document).on('click', '.uploadDocumentBtn', function(){
        var assignmentId = $(this).data('assignment');
        if(!assignmentId){
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
    
    // Ajax for Upload Document Form Submission
    $(document).on('click', '#uploadDocumentBtn', function(){
        var formData = new FormData($('#uploadDocumentForm')[0]);
        var assignmentId = $('#uploadDocumentForm input[name="task_assigned_id"]').val();
        if(!assignmentId){
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
    
    // Ajax for Add Comment Modal
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
    
    // Ajax for Add Comment Form Submission
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
    
    // (Optional) Delete Assignment Ajax functionality.
    $(document).on('click', '.deleteAssignmentBtn', function(e) {
        e.preventDefault();
        var assignmentId = $(this).attr('data-assignment');
        if(!assignmentId){
            alert("Assignment ID is undefined.");
            return;
        }
        if(confirm("Are you sure you want to delete this assignment?")){
            $.ajax({
                url: "/assignments/" + assignmentId,
                type: "DELETE",
                data: { _token: "{{ csrf_token() }}" },
                success: function(response){
                    if(response.success){
                        alert(response.message);
                        location.reload();
                    } else {
                        alert("Failed to delete assignment.");
                    }
                },
                error: function(xhr){
                    console.log("Error deleting assignment:", xhr.responseText);
                    alert("Error deleting assignment.");
                }
            });
        }
    });
});
</script>
@endsection
