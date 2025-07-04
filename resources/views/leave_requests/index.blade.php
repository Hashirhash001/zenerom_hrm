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
                            <h3 class="nk-block-title page-title">Leave Requests</h3>
                            <div class="nk-block-des text-soft">
                                <p>You have total {{ $leaveRequests->count() }} leave requests.</p>
                            </div>
                        </div>
                        <div class="nk-block-head-content">
                            <div class="toggle-wrap nk-block-tools-toggle">
                                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu">
                                    <em class="icon ni ni-menu-alt-r"></em>
                                </a>
                                <div class="toggle-expand-content" data-content="pageMenu">
                                    <ul class="nk-block-tools g-3">
                                        <li class="nk-block-tools-opt d-none d-sm-block">
                                            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLeaveRequestModal">
                                                <em class="icon ni ni-plus"></em><span>Add Leave Request</span>
                                            </a>
                                        </li>
                                        <li class="nk-block-tools-opt d-block d-sm-none">
                                            <a href="#" class="btn btn-icon btn-primary" data-bs-toggle="modal" data-bs-target="#addLeaveRequestModal">
                                                <em class="icon ni ni-plus"></em>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Header -->

                <!-- Filters -->
                <div class="col-md-12">
                    <form method="GET" action="{{ route('leave_requests.index') }}" class="row g-3 mb-4">
                        <div class="col-md-2">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">All</option>
                                <option value="Draft" {{ request('status') == 'Draft' ? 'selected' : '' }}>Draft</option>
                                <option value="Submitted" {{ request('status') == 'Submitted' ? 'selected' : '' }}>Submitted</option>
                                <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                                <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="Canceled" {{ request('status') == 'Canceled' ? 'selected' : '' }}>Canceled</option>
                            </select>
                        </div>
                        @if(in_array(auth()->user()->role_id, [3, 7]))
                        <div class="col-md-2">
                            <label for="employee_id" class="form-label">Employee</label>
                            <select name="employee_id" id="employee_id" class="form-control">
                                <option value="">All</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-3 align-self-end">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <a href="{{ route('leave_requests.index') }}" class="btn btn-secondary">Clear</a>
                        </div>
                    </form>
                </div>

                <!-- Leave Requests Table -->
                <div class="nk-block">
                    <div class="card card-bordered">
                        <div class="card-inner">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Employee</th>
                                        <th>Leave Type</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="leaveRequestsTable">
                                    @foreach($leaveRequests as $index => $leaveRequest)
                                        <tr id="leaveRequestRow-{{ $leaveRequest->id }}">
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ optional($leaveRequest->user)->name ?? 'N/A' }}</td>
                                            <td>{{ $leaveRequest->leave_type }}</td>
                                            <td>{{ $leaveRequest->start_date->format('Y-m-d') }}</td>
                                            <td>{{ $leaveRequest->end_date->format('Y-m-d') }}</td>
                                            <td>{{ $leaveRequest->duration }} days</td>
                                            <td>
                                                <span class="badge bg-{{ $leaveRequest->status == 'Approved' ? 'success' : ($leaveRequest->status == 'Rejected' ? 'danger' : ($leaveRequest->status == 'Submitted' ? 'warning' : 'secondary')) }} text-white">
                                                    {{ $leaveRequest->status ?? 'Unknown' }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-info viewLeaveBtn" data-id="{{ $leaveRequest->id }}" data-bs-toggle="modal" data-bs-target="#viewLeaveRequestModal">View</a>
                                                @if(in_array(auth()->user()->role_id, [3, 7]) && $leaveRequest->status == 'Submitted')
                                                    <a href="#" class="btn btn-sm btn-success approveLeaveBtn" data-id="{{ $leaveRequest->id }}" data-bs-toggle="modal" data-bs-target="#approveLeaveModal">Approve</a>
                                                    <a href="#" class="btn btn-sm btn-danger rejectLeaveBtn" data-id="{{ $leaveRequest->id }}" data-bs-toggle="modal" data-bs-target="#rejectLeaveModal">Reject</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="mt-3">
                                {{ $leaveRequests->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Leave Request Modal -->
<div class="modal fade" id="addLeaveRequestModal" tabindex="-1" aria-labelledby="addLeaveRequestModalLabel" inert>
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            @include('leave_requests.create')
        </div>
    </div>
</div>

<!-- Approve Leave Modal -->
<div class="modal fade" id="approveLeaveModal" tabindex="-1" aria-labelledby="approveLeaveModalLabel" inert>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveLeaveModalLabel">Approve Leave Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="approveLeaveForm">
                    @csrf
                    <input type="hidden" name="leave_request_id" id="approveLeaveRequestId">
                    <div class="form-group">
                        <label for="approver_comments">Comments (Optional)</label>
                        <textarea class="form-control" name="approver_comments" id="approver_comments" rows="4"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Approve</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reject Leave Modal -->
<div class="modal fade" id="rejectLeaveModal" tabindex="-1" aria-labelledby="rejectLeaveModalLabel" inert>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectLeaveModalLabel">Reject Leave Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="rejectLeaveForm">
                    @csrf
                    <input type="hidden" name="leave_request_id" id="rejectLeaveRequestId">
                    <div class="form-group">
                        <label for="reject_comments">Comments (Required)</label>
                        <textarea class="form-control" name="approver_comments" id="reject_comments" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- View Leave Request Details Modal -->
<div class="modal fade" id="viewLeaveRequestModal" tabindex="-1" aria-labelledby="viewLeaveRequestModalLabel" inert>
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewLeaveRequestModalLabel">Leave Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="leaveRequestDetails">
                    <p><strong>Employee:</strong> <span id="detailEmployee"></span></p>
                    <p><strong>Leave Type:</strong> <span id="detailLeaveType"></span></p>
                    <p><strong>Start Date:</strong> <span id="detailStartDate"></span></p>
                    <p><strong>End Date:</strong> <span id="detailEndDate"></span></p>
                    <p><strong>Duration:</strong> <span id="detailDuration"></span></p>
                    <p><strong>Reason:</strong> <span id="detailReason"></span></p>
                    <p><strong>Status:</strong> <span id="detailStatus"></span></p>
                    <p><strong>Approver:</strong> <span id="detailApprover"></span></p>
                    <p><strong>Approved/Rejected At:</strong> <span id="detailApprovedAt"></span></p>
                    <p><strong>Approver Comments:</strong> <span id="detailApproverComments"></span></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Popup Modal for Messages -->
<div class="modal fade" id="popupMessageModal" tabindex="-1" aria-labelledby="popupMessageModalLabel" inert>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="popupMessageModalLabel">Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="popupMessageContent"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets1/jquery.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
{{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script> --}}

<script>
$(document).ready(function() {
    // Show popup message
    function showPopup(message) {
        $('#popupMessageContent').text(message);
        $('#popupMessageModal').modal('show');
    }

    // Create Leave Request
    $(document).on('submit', '#addLeaveRequestForm', function(e) {
        e.preventDefault();
        var formData = new FormData(this);

        $.ajax({
            url: "{{ route('leave_requests.store') }}",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.success) {
                    showPopup(response.message);
                    if (document.activeElement) {
                        document.activeElement.blur();
                        console.log('Blurred active element on create');
                    }
                    $('#addLeaveRequestModal').modal('hide');
                    setTimeout(() => {
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').css('padding-right', '');
                        $('#addLeaveRequestModal').removeAttr('aria-hidden').removeAttr('aria-modal').css('display', 'none');
                        location.reload();
                    }, 500);
                } else {
                    showPopup("Error: " + response.message);
                }
            },
            error: function(xhr) {
                console.error("Create Leave Request Error:", xhr.responseText);
                showPopup("Error creating leave request: " + (xhr.responseJSON?.message || "Server error"));
            }
        });
    });

    // View Leave Request Details
    $(document).on('click', '.viewLeaveBtn', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        console.log('View Leave ID:', id); // Debug
        if (!id || isNaN(id)) {
            showPopup('Invalid leave request ID.');
            console.error('Invalid leave request ID:', id);
            return;
        }

        $.ajax({
            url: "{{ url('leave-requests') }}/" + id,
            type: "GET",
            success: function(response) {
                if (response.success) {
                    $('#detailEmployee').text(response.data.employee || 'N/A');
                    $('#detailLeaveType').text(response.data.leave_type || 'N/A');
                    $('#detailStartDate').text(response.data.start_date || 'N/A');
                    $('#detailEndDate').text(response.data.end_date || 'N/A');
                    $('#detailDuration').text(response.data.duration ? response.data.duration + ' days' : 'N/A');
                    $('#detailReason').text(response.data.reason || 'N/A');
                    $('#detailStatus').text(response.data.status || 'N/A');
                    $('#detailApprover').text(response.data.approver || 'N/A');
                    $('#detailApprovedAt').text(response.data.approved_at || 'N/A');
                    $('#detailApproverComments').text(response.data.approver_comments || 'N/A');
                    $('#viewLeaveRequestModal').modal('show');
                } else {
                    showPopup("Error: " + response.message);
                }
            },
            error: function(xhr) {
                console.error("View Leave Error:", xhr.responseText);
                showPopup("Error loading leave request details: " + (xhr.responseJSON?.message || "Server error"));
            }
        });
    });

    // Approve Leave Request
    $(document).on('click', '.approveLeaveBtn', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        console.log('Approve Leave ID:', id); // Debug
        if (!id || isNaN(id)) {
            showPopup('Invalid leave request ID.');
            console.error('Invalid leave request ID:', id);
            return;
        }
        $('#approveLeaveRequestId').val(id);
    });

    $(document).on('submit', '#approveLeaveForm', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        var id = $('#approveLeaveRequestId').val();

        $.ajax({
            url: "{{ url('leave-requests') }}/" + id + "/approve",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.success) {
                    showPopup(response.message);
                    if (document.activeElement) {
                        document.activeElement.blur();
                        console.log('Blurred active element on approve');
                    }
                    $('#approveLeaveModal').modal('hide');
                    setTimeout(() => {
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').css('padding-right', '');
                        $('#approveLeaveModal').removeAttr('aria-hidden').removeAttr('aria-modal').css('display', 'none');
                        location.reload();
                    }, 500);
                } else {
                    showPopup("Error: " + response.message);
                }
            },
            error: function(xhr) {
                console.error("Approve Leave Error:", xhr.responseText);
                showPopup("Error approving leave request: " + (xhr.responseJSON?.message || "Server error"));
            }
        });
    });

    // Reject Leave Request
    $(document).on('click', '.rejectLeaveBtn', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        console.log('Reject Leave ID:', id); // Debug
        if (!id || isNaN(id)) {
            showPopup('Invalid leave request ID.');
            console.error('Invalid leave request ID:', id);
            return;
        }
        $('#rejectLeaveRequestId').val(id);
    });

    $(document).on('submit', '#rejectLeaveForm', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        var id = $('#rejectLeaveRequestId').val();

        $.ajax({
            url: "{{ url('leave-requests') }}/" + id + "/reject",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.success) {
                    showPopup(response.message);
                    if (document.activeElement) {
                        document.activeElement.blur();
                        console.log('Blurred active element on reject');
                    }
                    $('#rejectLeaveModal').modal('hide');
                    setTimeout(() => {
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').css('padding-right', '');
                        $('#rejectLeaveModal').removeAttr('aria-hidden').removeAttr('aria-modal').css('display', 'none');
                        location.reload();
                    }, 500);
                } else {
                    showPopup("Error: " + response.message);
                }
            },
            error: function(xhr) {
                console.error("Reject Leave Error:", xhr.responseText);
                showPopup("Error rejecting leave request: " + (xhr.responseJSON?.message || "Server error"));
            }
        });
    });

    // General Modal Hidden Handler
    $(document).on('hidden.bs.modal', '.modal', function() {
        if (document.activeElement) {
            document.activeElement.blur();
            console.log('Blurred active element on modal close:', document.activeElement);
        }
        $(this).removeAttr('aria-hidden').removeAttr('aria-modal').css('display', 'none');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');
    });

    // Remove inert when modal is shown
    $(document).on('shown.bs.modal', '.modal', function() {
        $(this).removeAttr('inert');
    });
});
</script>
@endsection
