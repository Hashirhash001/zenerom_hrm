@extends('layouts.app')
@section('content')
<div class="nk-content">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <!-- Header Section -->
                <div class="nk-block-head nk-block-head-sm">
                    <div class="nk-block-between">
                        <div class="nk-block-head-content">
                            <h3 class="nk-block-title page-title">Task Assignment Details</h3>
                        </div>
                        <div class="nk-block-head-content">
                            <div class="toggle-wrap nk-block-tools-toggle">
                                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu">
                                    <em class="icon ni ni-menu-alt-r"></em>
                                </a>
                                <!-- <div class="toggle-expand-content" data-content="pageMenu">
                                    <ul class="nk-block-tools g-3">
                                        <li>
                                            <div>
                                                <input type="text" class="form-control" id="taskSearch" placeholder="Search Tasks">
                                            </div>
                                        </li>
                                    </ul>
                                </div> -->
                            </div><!-- .toggle-wrap -->
                        </div>
                    </div>
                </div>
                <!-- End Header -->

                <!-- Task Basic Details -->
                <div id="taskDetails" class="mb-4">
                    <h5>Task Details: {{ $task->title }}</h5>
                    <p><strong>Description:</strong> {{ $task->description }}</p>
                    <p><strong>Deadline:</strong> {{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('d M Y') : 'N/A' }}</p>
                </div>
                
                <hr>
                
                <!-- Filter Section -->
                <div id="filterSection" class="mb-4">
                    <h4>Filter Assignments</h4>
                    <form id="assignmentFilterForm" class="row g-3">
                        <div class="col-md-3">
                            <label for="filterStartDate" class="form-label">Start Date</label>
                            <input type="date" id="filterStartDate" class="form-control" name="filter_start_date" value="{{ date('Y-m-d'); }}">
                        </div>
                        <div class="col-md-3">
                            <label for="filterEndDate" class="form-label">End Date</label>
                            <input type="date" id="filterEndDate" class="form-control" name="filter_end_date" value="{{ date('Y-m-d'); }}">
                        </div>
                        <div class="col-md-3" style="display:none;">
                            <label for="filterStaff" class="form-label">Staff</label>
                            <select id="filterStaff" class="form-control" name="filter_staff">
                                <option value="">All</option>
                                @php
                                    // Unique staff from the assignments collection.
                                    $uniqueStaff = $task->assignments->pluck('staff')->filter()->unique('id');
                                @endphp
                                @foreach($uniqueStaff as $staff)
                                    <option value="{{ $staff->id }}">
                                        {{ $staff->first_name }} {{ $staff->middle_name }} {{ $staff->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterStatus" class="form-label">Status</label>
                            <select id="filterStatus" class="form-control" name="filter_status">
                                <option value="">All</option>
                                <option value="0">Pending</option>
                                <option value="1">Completed</option>
                                <!-- Add more statuses if needed -->
                            </select>
                        </div>
                        <div class="col-md-12">
                            <button type="button" class="btn btn-primary" id="applyFilterBtn">Apply Filters</button>
                            <button type="reset" class="btn btn-secondary" id="clearFilterBtn">Clear Filters</button>
                        </div>
                    </form>
                </div>
                
                <!-- Assignments List Section -->
                <div id="assignmentsList">
                    @if($task->assignments->count())
                        @foreach($task->assignments as $assignment)
                        <div class="card mb-3 assignment-card" 
                             data-date="{{ \Carbon\Carbon::parse($assignment->date)->toDateString() }}" 
                             data-staff="{{ optional($assignment->staff)->id }}" 
                             data-status="{{ $assignment->status }}">
                            <div class="card-header">
                                <strong>Staff:</strong> {{ optional($assignment->staff)->first_name }} {{ optional($assignment->staff)->middle_name }} {{ optional($assignment->staff)->last_name }}
                                | <strong>Date:</strong> {{ \Carbon\Carbon::parse($assignment->date)->format('d M Y') }}
                                | <strong>Status:</strong> {{ $assignment->status == 0 ? 'Pending' : 'Completed' }}
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
                                                <small> ({{ $comment->created_at }})</small>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p>No comments.</p>
                                @endif
                            </div>
                            <div class="card-footer"><!-- 
                  <button class="btn btn-sm btn-primary editAssignmentBtn" data-assignment="{{ $assignment->id }}">Edit Assignment</button> -->

                  <button class="btn btn-sm btn-info uploadDocumentBtn" data-assignment="{{ $assignment->id }}">Upload Document</button>
                  <button class="btn btn-sm btn-secondary addCommentBtn" data-assignment="{{ $assignment->id }}">Add Comment</button>
                </div>
                        </div>
                        @endforeach
                    @else
                        <p>No assignments found for this task.</p>
                    @endif
                </div>
            </div>
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

<!-- jQuery filter script -->
<script>
    $(document).ready(function(){
        console.log("Apply Filter button clicked.");
            // Get filter values.
            var startDate = $('#filterStartDate').val(); // Format: YYYY-MM-DD
            var endDate   = $('#filterEndDate').val();     // Format: YYYY-MM-DD
            var staffId   = $('#filterStaff').val();
            var status    = $('#filterStatus').val();
            console.log("Filters:", { startDate: startDate, endDate: endDate, staffId: staffId, status: status });
            
            // Iterate through each assignment card.
            $('.assignment-card').each(function(){
                var card = $(this);
                var cardDate = card.data('date'); // e.g., "2025-03-12"
                var cardStaff = card.data('staff'); // e.g., "3"
                var cardStatus = card.data('status').toString(); // e.g., "0" or "1"
                console.log("Card data:", { cardDate: cardDate, cardStaff: cardStaff, cardStatus: cardStatus });
                
                var show = true;
                
                // Filter by start date.
                if(startDate && cardDate < startDate){
                    show = false;
                }
                // Filter by end date.
                if(endDate && cardDate > endDate){
                    show = false;
                }
                // Filter by staff.
                if(staffId && cardStaff != staffId){
                    show = false;
                }
                // Filter by status.
                if(status && cardStatus !== status){
                    show = false;
                }
                
                if(show){
                    card.show();
                } else {
                    card.hide();
                }
            });
    });


    $(document).ready(function(){
        $('#applyFilterBtn').click(function(){
            console.log("Apply Filter button clicked.");
            // Get filter values.
            var startDate = $('#filterStartDate').val(); // Format: YYYY-MM-DD
            var endDate   = $('#filterEndDate').val();     // Format: YYYY-MM-DD
            var staffId   = $('#filterStaff').val();
            var status    = $('#filterStatus').val();
            console.log("Filters:", { startDate: startDate, endDate: endDate, staffId: staffId, status: status });
            
            // Iterate through each assignment card.
            $('.assignment-card').each(function(){
                var card = $(this);
                var cardDate = card.data('date'); // e.g., "2025-03-12"
                var cardStaff = card.data('staff'); // e.g., "3"
                var cardStatus = card.data('status').toString(); // e.g., "0" or "1"
                console.log("Card data:", { cardDate: cardDate, cardStaff: cardStaff, cardStatus: cardStatus });
                
                var show = true;
                
                // Filter by start date.
                if(startDate && cardDate < startDate){
                    show = false;
                }
                // Filter by end date.
                if(endDate && cardDate > endDate){
                    show = false;
                }
                // Filter by staff.
                if(staffId && cardStaff != staffId){
                    show = false;
                }
                // Filter by status.
                if(status && cardStatus !== status){
                    show = false;
                }
                
                if(show){
                    card.show();
                } else {
                    card.hide();
                }
            });
        });

        $('#clearFilterBtn').click(function(){
            // Reset filter form.
            $('#assignmentFilterForm')[0].reset();
            // Show all assignment cards.
            $('.assignment-card').show();
        });
    });
    $(document).ready(function(){

    // Edit Assignment Modal
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

    // Save edited assignment
    $(document).on('click', '#saveAssignmentBtn', function(){
        var formData = $('#editAssignmentForm').serialize();
        var assignmentId = $('#editAssignmentForm input[name="assignment_id"]').val();
        $.ajax({
            url: "/assignments/" + assignmentId,
            type: "PUT",
            data: formData,
            success: function(response){
                alert(response.message);
                $('#editAssignmentModal').modal('hide');
                location.reload();
            },
            error: function(xhr){
                console.log("Error updating assignment:", xhr.responseText);
            }
        });
    });

    // Upload Document Modal
$(document).on('click', '.uploadDocumentBtn', function(){
    // Use .attr() to ensure the value is retrieved
    var assignmentId = $(this).attr('data-assignment');
    console.log("Upload Document: assignmentId =", assignmentId);
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
});
// Delete Assignment
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
                    // Option 1: Remove the card from the DOM
                  
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

</script>
@endsection
