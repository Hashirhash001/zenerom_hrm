@extends('layouts.app')

@section('content')
<div class="nk-content">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="nk-block">
                                    <div class="row g-gs">
                                        <div class="col-lg-4 col-xl-4 col-xxl-3">
                                            <div class="card card-bordered">
                                                <div class="card-inner-group">
                                                    <div class="card-inner">
                                                        <div class="user-card user-card-s2">
                                                            <div class="user-avatar lg bg-primary">
                                                                <span>{{ strtoupper(substr($project->name,0,1)) }}</span>
                                                            </div>
                                                            <div class="user-info">
                                                                <div class="badge bg-light rounded-pill ucap">{{ $project->customer ? $project->customer->name : 'Unknown' }}</div>
                                                                <h5>{{ $project->name }}</h5>
                                                            </div>
                                                        </div>
                                                    </div>
                                                   <!--  <div class="card-inner card-inner-sm">
                                                        <ul class="btn-toolbar justify-center gx-1">
                                                            <li><a  class="btn btn-trigger btn-icon"><em class="icon ni ni-shield-off"></em></a></li>
                                                            <li><a  class="btn btn-trigger btn-icon"><em class="icon ni ni-mail"></em></a></li>
                                                            <li><a  class="btn btn-trigger btn-icon"><em class="icon ni ni-bookmark"></em></a></li>
                                                            <li><a  class="btn btn-trigger btn-icon text-danger"><em class="icon ni ni-na"></em></a></li>
                                                        </ul>
                                                    </div> -->
                                                   <!--  <div class="card-inner">
                                                        <div class="row text-center">
                                                            <div class="col-4">
                                                                <div class="profile-stats">
                                                                    <span class="amount">23</span>
                                                                    <span class="sub-text">Total Order</span>
                                                                </div>
                                                            </div>
                                                            <div class="col-4">
                                                                <div class="profile-stats">
                                                                    <span class="amount">20</span>
                                                                    <span class="sub-text">Complete</span>
                                                                </div>
                                                            </div>
                                                            <div class="col-4">
                                                                <div class="profile-stats">
                                                                    <span class="amount">3</span>
                                                                    <span class="sub-text">Progress</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div> -->
                                                    <!-- .card-inner -->
                                                    <div class="card-inner">
                                                <ul class="nk-activity">
                                                    @foreach($project->customer->contacts as $contact)
                                                    <li class="nk-activity-item">
                                                        <div class="nk-activity-media user-avatar bg-success">
                                                            {{ strtoupper(substr($contact->contact_name,0,1)) }}
                                                        </div>
                                                        <div class="nk-activity-data">
                                                            <div class="label">{{ $contact->contact_name }}</div>
                                                            <span class="time">{{ $contact->contact_email }}</span>
                                                            <span class="time">{{ $contact->contact_phone  }}</span>
                                                        </div>
                                                    </li>
                                                    @endforeach
                                                    
                                                </ul>
                                                    </div><!-- .card-inner -->
                                                </div>
                                            </div>
                                        </div><!-- .col -->
                                        <div class="col-lg-8 col-xl-8 col-xxl-9">
                                            <div class="card card-bordered">
                                                <div class="card-inner">
                                                    <div class="card card-bordered card-preview">
                                            <div class="card-inner">
                                                <ul class="nav nav-tabs mt-n3" role="tablist">
                                                    <li class="nav-item" role="presentation">
                                                        <a class="nav-link active" data-bs-toggle="tab" href="#tabItem5" aria-selected="true" role="tab">
                                                            <em class="icon ni ni-link-group"></em>
                                                            <span>Services</span>
                                                        </a>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <a class="nav-link" data-bs-toggle="tab" href="#tabItem6" aria-selected="false" tabindex="-1" role="tab">
                                                            <em class="icon ni ni-calendar-alt"></em>
                                                            <span>Service Timeline</span></a>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <a class="nav-link" data-bs-toggle="tab" href="#tabItem7" aria-selected="false" tabindex="-1" role="tab">
                                                            <em class="icon ni ni-users"></em>
                                                            <span>Team</span></a>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <a class="nav-link" data-bs-toggle="tab" href="#tabItem8" aria-selected="false" tabindex="-1" role="tab">
                                                            <em class="icon ni ni-folders"></em>
                                                            <span>Project Description</span></a>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <a class="nav-link" data-bs-toggle="tab" href="#tabItem9" aria-selected="false" tabindex="-1" role="tab">
                                                            <em class="icon ni ni-update"></em>
                                                            <span>Updates</span></a>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <a class="nav-link" data-bs-toggle="tab" href="#tabItem10" aria-selected="false" tabindex="-1" role="tab">
                                                            <em class="icon ni ni-reports"></em>
                                                            <span>Tasks</span></a>
                                                    </li>
                                                </ul>
                                                <div class="tab-content">
   <div class="tab-pane active" id="tabItem5" role="tabpanel">
    <div class="card card-bordered">
        <div class="card-inner border-bottom">
            <div class="card-title-group">
                <div class="card-title">
                    <h6 class="title">Services</h6>
                </div>
                <div class="card-tools">
                    <!-- Button to open add service modal -->
                    <a  class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProjectServiceModal">
                        <em class="icon ni ni-plus"></em><span>Add Service</span>
                    </a>
                </div>
            </div>
        </div>
        <div class="card-inner">
            @if($project->projectServices->isNotEmpty())
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Assigned Staff</th>
                        <th>Status</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($project->projectServices as $ps)
                        <tr id="projectServiceRow-{{ $ps->id }}">
                            <td>{{ $ps->service ? $ps->service->name : 'N/A' }}</td>
                            <td>
                                @if($ps->assignedStaff)
                                    {{ $ps->assignedStaff->employee_id }} - {{ $ps->assignedStaff->first_name }} {{ $ps->assignedStaff->last_name }}
                                @else
                                    Not Assigned
                                @endif
                            </td>
                            <td>{{ ucfirst($ps->status) }}</td>
                            <td>{{ $ps->notes }}</td>
                            <td>
                                <a  class="editProjectServiceBtn" data-id="{{ $ps->id }}">
                                    <em class="icon ni ni-edit"></em>
                                </a>
                                <a  class="deleteProjectServiceBtn" data-id="{{ $ps->id }}">
                                    <em class="icon ni ni-trash"></em>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No services added for this project.</p>
        @endif
        </div>
    </div>
</div>



<div class="tab-pane" id="tabItem6" role="tabpanel">
    <div class="card-inner">
        <div class="timeline">
            <div class="card-inner border-bottom">
            <div class="card-title-group">
                <div class="card-title">
                    <h6 class="title">Service Timeline</h6>
                </div>
                <div class="card-tools">
                    <!-- Button to open the "Add Milestone" modal -->
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addMilestoneModal">
                        <em class="icon ni ni-plus"></em> Add Timeline
                    </button>
                </div>
            </div>
        </div><br /><br />

            @foreach($project->projectServices as $ps)
    <div class="project-service-timeline mb-4" id="timelineSection-{{ $ps->id }}">
        <h6 class="timeline-head">
            {{ $ps->service->name ?? 'Service' }} Timeline
            <!-- Optionally, add a button to add a milestone for this service -->
            <!-- <button type="button" class="btn btn-primary btn-sm float-end addMilestoneForServiceBtn" data-service-id="{{ $ps->id }}">
                <em class="icon ni ni-plus"></em> Add Milestone
            </button> -->
        </h6>
        <ul class="timeline-list" id="timelineList-{{ $ps->id }}">
            @foreach($ps->milestones()->orderBy('due_date')->get() as $milestone)
                <li class="timeline-item" id="milestoneRow-{{ $milestone->id }}">
                    <div class="timeline-status 
                        @if($milestone->status=='pending') bg-primary is-outline 
                        @elseif($milestone->status=='completed') bg-success 
                        @elseif($milestone->status=='delayed') bg-danger 
                        @endif"></div>
                    <div class="timeline-date">
                        {{ $milestone->due_date ? \Carbon\Carbon::parse($milestone->due_date)->format('d M') : 'No Date' }}
                        <em class="icon ni ni-alarm-alt"></em>
                    </div>
                    <div class="timeline-data">
                        <h6 class="timeline-title">{{ $milestone->title }}</h6>
                        <div class="timeline-des">
                            <p>{{ $milestone->description }}</p>
                            <span class="time">{{ $milestone->status }}</span>
                        </div>
                        <div class="timeline-actions" style="float:right;">
                            <a  class="editMilestoneBtn" data-id="{{ $milestone->id }}" title="Edit">
                                <em class="icon ni ni-edit"></em>
                            </a>
                            <a  class="deleteMilestoneBtn" data-id="{{ $milestone->id }}" title="Delete">
                                <em class="icon ni ni-trash"></em>
                            </a>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@endforeach

        </div>
    </div>
</div>



<div class="tab-pane" id="tabItem7" role="tabpanel">
    <!-- Global Add Staff Button for all services -->
    <div class="mb-3">
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProjectServiceStaffModal">
            <em class="icon ni ni-plus"></em> Add Staff
        </button>
    </div>

    @foreach($project->projectServices as $ps)
        <div class="project-service-staff mb-4" id="staffSection-{{ $ps->id }}">
            <h6 class="timeline-head">
                {{ $ps->service->name ?? 'Service' }} - Assigned Staff
            </h6>
            <div class="row">
                @if($ps->assignedStaffs->isNotEmpty())
                    @foreach($ps->assignedStaffs as $staff)
                        <div class="col-md-3 mb-3" id="staffRow-{{ $staff->id }}-{{ $ps->id }}">
                            <div class="user-card">
                                <div class="user-avatar" style="width:60px; height:60px; border-radius:50%; background-color: {{ $staff->photo ? 'transparent' : '#e0e0e0' }}; text-align: center; line-height: 60px; font-size: 24px;">
                                    @if($staff->photo)
                                        <img src="{{ asset('uploads/employees/' . $staff->photo) }}" alt="{{ $staff->first_name }}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                                    @else
                                        <span>{{ strtoupper(substr($staff->first_name, 0, 1)) }}</span>
                                    @endif
                                </div>
                                <div class="user-info mt-2 text-center">
                                    <span class="lead-text">{{ $staff->first_name }} {{ $staff->last_name }}</span>
                                    <span class="sub-text">{{ $staff->employee_id }}</span>
                                </div>
                                <div class="user-action text-center mt-2">
                                    <button type="button" class="btn btn-sm btn-warning toggleStaffBtn" data-staff-id="{{ $staff->id }}" data-service-id="{{ $ps->id }}">
                                        @if($staff->pivot->status == 'active')
                                            Deactivate
                                        @else
                                            Activate
                                        @endif
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger deleteStaffBtn" data-staff-id="{{ $staff->id }}" data-service-id="{{ $ps->id }}">
                                        <em class="icon ni ni-trash"></em>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="col-12">
                        <p>No staff assigned for this service.</p>
                    </div>
                @endif
            </div><!-- .row -->
        </div>
    @endforeach
</div>




<div class="tab-pane" id="tabItem8" role="tabpanel">
    <div class="card-inner">
        <!-- Add Description Button -->
        <div class="mb-3">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProjectDescriptionModal">
                <em class="icon ni ni-plus"></em> Add Description
            </button>
        </div>

        <!-- Accordion: Group descriptions by project service -->
        @foreach($project->projectServices as $ps)
            <div class="mb-4">
                <h6 class="timeline-head">
                    {{ $ps->service->name ?? 'Service' }} - Descriptions
                </h6>
                <div id="accordionService-{{ $ps->id }}" class="accordion">
                    @foreach($ps->descriptions()->orderBy('entered_date')->get() as $desc)
                        <div class="accordion-item" id="descriptionRow-{{ $desc->id }}">
                            <a class="accordion-head collapsed" data-bs-toggle="collapse" data-bs-target="#accordionDesc-{{ $desc->id }}" aria-expanded="false">
                                <h6 class="title">{{ $desc->title }}</h6>
                                <span class="accordion-icon"></span>
                            </a>
                            <div id="accordionDesc-{{ $desc->id }}" class="accordion-body collapse" data-bs-parent="#accordionService-{{ $ps->id }}">
                                <div class="accordion-inner">
                                    {!! $desc->details !!}
                                    <p><small>Entered on: {{ $desc->entered_date ? \Carbon\Carbon::parse($desc->entered_date)->format('d M Y') : 'N/A' }}</small></p>
                                    <!-- Files Section -->
                                    @if($desc->files->count())
                                        <ul class="list-group mb-2">
                                            @foreach($desc->files as $file)
                                                <li class="list-group-item d-flex justify-content-between align-items-center" id="fileRow-{{ $file->id }}">
                                                    <a href="{{ asset('uploads/project_descriptions/' . $file->file_name) }}" download>
                                                        {{ $file->file_name }}
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger deleteFileBtn" data-file-id="{{ $file->id }}">
                <em class="icon ni ni-trash"></em> Delete
            </button>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    <!-- Action Buttons -->
                                    <div class="mt-2">
                                        <!-- <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDocumentModal">
                                            <em class="icon ni ni-plus"></em> Upload Document
                                        </button> -->
                                        <button type="button" class="btn btn-primary btn-sm addDocumentBtn" data-description-id="{{ $desc->id }}">
                <em class="icon ni ni-plus"></em> Add Document
            </button>
                                        <button type="button" class="btn btn-sm btn-warning editDescriptionBtn" data-id="{{ $desc->id }}">
                                            <em class="icon ni ni-edit"></em> Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger deleteDescriptionBtn" data-id="{{ $desc->id }}">
                                            <em class="icon ni ni-trash"></em> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    @if($ps->descriptions()->count() == 0)
                        <p>No descriptions found for this service.</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="tab-pane" id="tabItem9" role="tabpanel">
    <div class="d-flex mb-3">
        <input type="hidden" id="updateSearch" class="form-control me-2" placeholder="Search updates...">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUpdateModal">
            <em class="icon ni ni-plus"></em> Add New Update
        </button>
    </div>
    
    <!-- Container for the list of updates -->
    <div id="updateList">
        @foreach($project->updates as $update)
            @include('project_updates._update_item', ['update' => $update])
        @endforeach
    </div>
</div>
<div class="tab-pane" id="tabItem10" role="tabpanel">
     <div class="card-inner">
        <h5>Project Tasks</h5>
        @if($project->tasks->count())
            @php
                // Group tasks by service name (or "Uncategorized" if none)
                $tasksGrouped = $project->tasks->groupBy(function($task) {
                    return $task->service ? $task->service->name : 'Uncategorized';
                });
            @endphp

            @foreach($tasksGrouped as $serviceName => $tasks)
                <div class="card mb-3">
                    <div class="card-header">
                        <h6>{{ $serviceName }}</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Assigned Staff</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    // Sort tasks in descending order by 'date' (or created_at if date not set)
                                    $sortedTasks = $tasks->sortByDesc(function($task) {
                                        return $task->date ?? $task->created_at;
                                    });
                                @endphp
                                @foreach($sortedTasks as $task)
                                    <tr>
                                        <td>{{ $task->id }}</td>
                                        <td>{{ $task->title }}</td>
                                        <td>{{ $task->description }}</td>
                                        <td>
                                            @if($task->date)
                                                {{ \Carbon\Carbon::parse($task->date)->format('d M Y') }}
                                            @else
                                                {{ $task->created_at->format('d M Y') }}
                                            @endif
                                        </td>
                                        <td>{{ ucfirst($task->status) }}</td>
                                        <td>
                                            @if($task->taskUsers->count())
                                                @php
                                                    // Extract unique staff from taskUsers (using the staff relation)
                                                    $assignedStaff = $task->taskUsers->pluck('staff')->filter()->unique('id');
                                                @endphp
                                                @foreach($assignedStaff as $staff)
                                                    {{ $staff->first_name }} {{ $staff->middle_name }} {{ $staff->last_name }}@if(!$loop->last), @endif
                                                @endforeach
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        @else
            <p>No tasks found for this project.</p>
        @endif
    </div>
</div>




                                                </div>
                                            </div>
                                        </div>
                                                    
                                                   
                                                    <!-- <div class="nk-block"><br />
                                                        <div class="card card-bordered">
                                                            <div class="card-inner">
                                                                <div class="between-center flex-wrap flex-md-nowrap g-3">
                                                                    <div class="media media-center gx-3 wide-xs">
                                                                        <div class="media-object">
                                                                            <em class="icon icon-circle icon-circle-lg ni ni-facebook-f"></em>
                                                                        </div>
                                                                        <div class="media-content">
                                                                            <p>You have successfully connected with your facebook account, you can easily log in using your account too.</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="nk-block-actions flex-shrink-0">
                                                                        <a  class="btn btn-lg btn-danger">Revoke Access</a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div> -->


                                                </div><!-- .card-inner -->
                                            </div><!-- .card -->
                                        </div><!-- .col -->
                                    </div><!-- .row -->
                                </div>

            </div>
        </div>
    </div>
</div>

<!-- Trigger -->


<!-- Modal -->
<div class="modal fade" id="addProjectServiceModal" tabindex="-1" aria-labelledby="addProjectServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            @include('projects._project_service_create_modal')
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editProjectServiceModal" tabindex="-1" aria-labelledby="editProjectServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            @include('projects._project_service_edit_modal')
        </div>
    </div>
</div>

<!-- Add Milestone Modal -->
<div class="modal fade" id="addMilestoneModal" tabindex="-1" aria-labelledby="addMilestoneModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            @include('project_milestones.create_modal')
        </div>
    </div>
</div>

<div class="modal fade" id="editMilestoneModal" tabindex="-1" aria-labelledby="editMilestoneModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            @include('project_milestones.edit_modal')
        </div>
    </div>
</div>

<!-- Add Staff Modal -->
<div class="modal fade" id="addProjectServiceStaffModal" tabindex="-1" aria-labelledby="addProjectServiceStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            @include('project_service_staff.create_modal')
        </div>
    </div>
</div>
<!-- Add Project Description Modal Container -->
<div class="modal fade" id="addProjectDescriptionModal" tabindex="-1" aria-labelledby="addProjectDescriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            @include('project_descriptions.create_modal')
        </div>
    </div>
</div>

<!-- Edit Project Description Modal Container -->
<div class="modal fade" id="editProjectDescriptionModal" tabindex="-1" aria-labelledby="editProjectDescriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            @include('project_descriptions.edit_modal')
        </div>
    </div>
</div>

<!-- Popup Modal for messages -->
<div class="modal fade" id="popupMessageModal" tabindex="-1" aria-labelledby="popupMessageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="popupMessageModalLabel">Message</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Message content will be injected here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="addDocumentModal" tabindex="-1" aria-labelledby="addDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            @include('project_descriptions.create_document_modal')

        </div>
    </div>
</div>
<!-- Add Update Modal -->
<div class="modal fade" id="addUpdateModal" tabindex="-1" aria-labelledby="addUpdateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            @include('project_updates.create_modal')
        </div>
    </div>
</div>


<!-- Edit Update Modal -->
<div class="modal fade" id="editUpdateModal" tabindex="-1" aria-labelledby="editUpdateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            @include('project_updates.edit_modal')
        </div>
    </div>
</div>

<!-- Summernote CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-lite.min.css" rel="stylesheet">
<script src="{{ asset('assets1/jquery.min.js') }}"></script>
<script src="{{ asset('assets1/jquery-ui/jquery-ui.js') }}"></script>
<!-- Summernote JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-lite.min.js"></script>


<script>

// Save New Update
$(document).on('click', '#saveUpdateBtn', function(e) {
    e.preventDefault();
    var formData = new FormData($('#addUpdateForm')[0]);
    $.ajax({
        url: "{{ route('project_update.store') }}",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            if(response.success){
                // Append the rendered HTML for the new update.
                $('#updateList').append(response.updateHtml);

                var modalEl = document.getElementById('addUpdateModal'); // change to your modal's ID
                var modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) {
                    modalInstance.hide();
                } else {
                    modalInstance = new bootstrap.Modal(modalEl);
                    modalInstance.hide();
                }
                showPopup(response.message);
            } else {
                showPopup("Error saving update.");
            }
        },
        error: function(xhr) {
            console.log("Error saving update:", xhr.responseText);
            showPopup("Error saving update.");
        }
    });
});

// Listen for click on edit button
$(document).on('click', '.editUpdateBtn', function(e) {
    e.preventDefault();
    // Get the update ID from the clicked button
    var updateId = $(this).data('id');

    // Make an AJAX GET request to retrieve the edit form HTML
    $.ajax({
        url: '/project_update/' + updateId + '/edit', // Ensure your route is defined correctly
        type: 'GET',
        success: function(response) {
            // Load the response HTML into the modal's content area
            $('#editUpdateModal .modal-content').html(response);

            // Initialize and show the modal (using Bootstrap 5 syntax)
            var modalEl = document.getElementById('editUpdateModal');
            var modalInstance = new bootstrap.Modal(modalEl);
            modalInstance.show();
        },
        error: function(xhr) {
            console.error("Error loading update:", xhr.responseText);
            // Optionally, display an error message
            showPopup("Error loading update.");
        }
    });
});

// Update Update
$(document).on('click', '#updateUpdateBtn', function(e) {
    e.preventDefault();
    var formData = new FormData($('#editUpdateForm')[0]);
    // Use the correct input name to get the update ID
    var updateId = $('#editUpdateForm input[name="update_id"]').val();
    $.ajax({
        url: "/project-updates/" + updateId, // Ensure this matches your route
        type: "POST", // using method spoofing (_method=PUT) via the form
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            if (response.success) {
                // Replace the update item's HTML with the updated version
                $('#updateRow-' + updateId).replaceWith(response.updateHtml);
                // Hide the modal using Bootstrap 5
                var modalEl = document.getElementById('editUpdateModal');
                var modalInstance = bootstrap.Modal.getInstance(modalEl);
                if(modalInstance) {
                    modalInstance.hide();
                }
                showPopup(response.message);
            } else {
                showPopup("Error updating update.");
            }
        },
        error: function(xhr) {
            console.log("Error updating update:", xhr.responseText);
            showPopup("Error updating update.");
        }
    });
});


// Delete Update
$(document).on('click', '.deleteUpdateBtn', function() {
    if(confirm("Are you sure you want to delete this update?")){
        var updateId = $(this).data('id');
        $.ajax({
            url: "/project-updates/" + updateId,
            type: "DELETE",
            data: {_token: "{{ csrf_token() }}"},
            success: function(response) {
                if(response.success){
                    $('#updateRow-' + updateId).remove();
                    showPopup(response.message);
                } else {
                    showPopup("Error deleting update.");
                }
            },
            error: function(xhr) {
                console.log("Error deleting update:", xhr.responseText);
                showPopup("Error deleting update.");
            }
        });
    }
});

// Search/Filter Updates (optional)
$(document).on('keyup', '#updateSearch', function() {
    var searchQuery = $(this).val();
    $.ajax({
        url: "{{ route('project_update.index') }}",
        type: "GET",
        data: { search: searchQuery },
        success: function(response) {
            if(response.success){
                $('#updateList').html(response.html);
            }
        },
        error: function(xhr) {
            console.log("Error searching updates:", xhr.responseText);
        }
    });
});




$(document).on('click', '.addDocumentBtn', function() {
    // Get the description id from the clicked button's data attribute.
    var descriptionId = $(this).data('description-id');
    
    // Set the value of the hidden input inside the modal.
    $('#project_description_id').val(descriptionId);
    
    // Show the modal.
    $('#addDocumentModal').modal('show');
});
$(document).on('click', '#saveDocumentBtn', function(e) {
    e.preventDefault();
    var formData = new FormData($('#addDocumentForm')[0]);
    
    $.ajax({
        url: "{{ route('project_description_document.store') }}", // Make sure this route name is correct.
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            if(response.success) {
                // Append the new document item to the list
                var descriptionId = response.project_description_id;
                // $('#documentList').append(response.documentHtml);
                $('#noFilesMsg-' + descriptionId).remove();
                 $('#documentList-' + descriptionId).append(response.documentHtml);
                
                // Hide the modal window
                $('#addDocumentModal').modal('hide');
                
                // Optionally show a success message (you can replace alert with your preferred method)
                alert(response.message);
                location.reload()

            } else {
                alert("Error: " + response.message);
            }
        },
        error: function(xhr) {
            console.log("Upload Document Error:", xhr.responseText);
            alert("Error uploading document.");
        }
    });
});

$(document).on('click', '.deleteFileBtn', function() {
    // Try using .attr() if .data() returns undefined.
    var fileId = $(this).attr('data-file-id');
    if (!fileId) {
        alert("File ID not found.");
        return;
    }
    
    if (confirm('Are you sure you want to delete this file?')) {
        var deleteUrl = "{{ route('project_description_file.destroy', ':id') }}";
        deleteUrl = deleteUrl.replace(':id', fileId);

        $.ajax({
            url: deleteUrl,
            type: "DELETE",
            data: { _token: "{{ csrf_token() }}" },
            success: function(response) {
                alert(response.message);
                $("#fileRow-" + fileId).remove();
            },
            error: function(xhr, status, error) {
                console.log("Delete File Error:", xhr.responseText);
                alert("Error deleting file.");
            }
        });
    }
});


// Save new project description via Ajax (Add Modal)
$(document).on('click', '#saveProjectDescriptionBtn', function(e) {
    e.preventDefault();
    var formData = new FormData($('#addProjectDescriptionForm')[0]);
    $.ajax({
        url: "{{ route('project_description.storedata') }}",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            if (response.success) {
                var psId = response.project_service_id;
                // Append new HTML to the accordion container for the service
                $('#accordionService-' + psId).append(response.descriptionHtml);
                // Clear form inputs
                $('#addProjectDescriptionForm')[0].reset();
                
                // Hide the modal using Bootstrap 5's API
                var modalEl = document.getElementById('addProjectDescriptionModal');
                var modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (!modalInstance) {
                    modalInstance = new bootstrap.Modal(modalEl);
                }
                modalInstance.hide();
                
                showPopup(response.message);
            } else {
                showPopup("Error saving project description.");
            }
        },
        error: function(xhr) {
            console.log("Error saving description:", xhr.responseText);
            showPopup("Error saving project description.");
        }
    });
});


// Example AJAX call to load the edit modal:
$(document).on('click', '.editDescriptionBtn', function() {
    var descriptionId = $(this).data('id');
    $.ajax({
        url: "/project-descriptions/" + descriptionId + "/edit",
        type: "GET",
        success: function(response) {
            $('#editProjectDescriptionModalContainer').html(response);
            $('#editProjectDescriptionModalContainer .modal').modal('show');
        },
        error: function(xhr) {
            console.log("Error loading edit modal:", xhr.responseText);
            alert("Error loading the edit form.");
        }
    });
});

// Update project description via Ajax (Edit Modal)
$(document).on('click', '#updateProjectDescriptionBtn', function() {
    var formData = new FormData($('#editProjectDescriptionForm')[0]);
    var descriptionId = $('#editProjectDescriptionForm input[name="id"]').val();
    
    $.ajax({
        url: "/project-descriptions/" + descriptionId,
        type: "POST", // using method spoofing (_method=PATCH) in the form
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            // Replace the updated description block with new HTML (including file list)
            $('#descriptionRow-' + descriptionId).replaceWith(response.descriptionHtml);
            
            // Hide the modal window
            $('#editProjectDescriptionModal').modal('hide');
            
            // Optionally display a notification
            alert(response.message);
        },
        error: function(xhr) {
            console.log("Update Description Error:", xhr.responseText);
            alert("Error updating description.");
        }
    });
});



// Delete a description file via Ajax
$(document).on('click', '.deleteDescriptionBtn', function() {
    if (confirm('Are you sure you want to delete this description?')) {
        var descId = $(this).data('id');
        var deleteUrl = "{{ route('project_description.destroy', ':id') }}";
        deleteUrl = deleteUrl.replace(':id', descId);

        $.ajax({
            url: deleteUrl,
            type: "DELETE",
            data: { _token: "{{ csrf_token() }}" },
            success: function(response) {
                if(response.success) {
                    // Remove the corresponding accordion item from the DOM
                    $('#descriptionRow-' + descId).remove();
                    showPopup(response.message);
                } else {
                    showPopup("Failed to delete description.");
                }
            },
            error: function(xhr, status, error) {
                console.log("Delete Error:", xhr.responseText);
                showPopup("Error deleting description.");
            }
        });
    }
});
$(document).on('click', '.editDescriptionBtn', function(e) {
    e.preventDefault();
    var descId = $(this).data('id');
    var editUrl = "{{ route('project_description.edit', ':id') }}";
    editUrl = editUrl.replace(':id', descId);
    $.ajax({
        url: editUrl,
        type: "GET",
        success: function(response) {
            $('#editProjectDescriptionModal .modal-content').html(response);
            // Initialize Summernote in the modal if necessary:
            $('#editProjectDescriptionModal').on('shown.bs.modal', function() {
                $(this).find('.summernote').summernote({
                    height: 200,
                    placeholder: 'Enter description here...',
                    toolbar: [
                        ['style', ['bold', 'italic', 'underline']],
                        ['para', ['ul', 'ol']],
                        ['insert', ['link']],
                        ['view', ['codeview']]
                    ]
                });
            });
            var modalEl = document.getElementById('editProjectDescriptionModal');
            var modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modalInstance.show();
        },
        error: function(xhr) {
            console.error("Error loading edit form:", xhr.responseText);
            alert("Error loading edit form.");
        }
    });
});


// When "Add Staff" button is clicked for a specific service, set the hidden input in the modal and open it
$(document).on('click', '.addStaffForServiceBtn', function(e) {
    e.preventDefault();
    var serviceId = $(this).data('service-id');
    $('#staff_project_service_id').val(serviceId);
    $('#addProjectServiceStaffModal').modal('show');
});

// When "Add Staff" button is clicked, the modal is triggered (see HTML above)

// Save new staff assignment via Ajax
$(document).on('click', '#saveProjectServiceStaffBtn', function(e) {
    e.preventDefault();
    var formData = new FormData($('#addProjectServiceStaffForm')[0]);
    $.ajax({
        url: "{{ route('project_user.store') }}",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            var staff = response.staff; // returned employee data
            var serviceId = staff.pivot.project_service_id;
            var staffHtml = `
                <div class="col-md-3 mb-3" id="staffRow-${staff.id}-${serviceId}">
                    <div class="user-card">
                        <div class="user-avatar" style="width:60px; height:60px; border-radius:50%; background-color: ${ staff.photo ? 'transparent' : '#e0e0e0' }; text-align: center; line-height: 60px; font-size: 24px;">
                            ${ staff.photo ? `<img src="/uploads/employees/${staff.photo}" alt="${staff.first_name}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">` : `<span>${staff.first_name.charAt(0).toUpperCase()}</span>` }
                        </div>
                        <div class="user-info mt-2 text-center">
                            <span class="lead-text">${staff.first_name} ${staff.last_name}</span>
                            <span class="sub-text">${staff.employee_id}</span>
                        </div>
                        <div class="user-action text-center mt-2">
                            <button type="button" class="btn btn-sm btn-warning toggleStaffBtn" data-staff-id="${staff.id}" data-service-id="${serviceId}">
                                ${ staff.pivot.status === 'active' ? 'Deactivate' : 'Activate' }
                            </button>
                            <button type="button" class="btn btn-sm btn-danger deleteStaffBtn" data-pivot-id="${response.assignment_id}" data-service-id="${serviceId}">
                                <em class="icon ni ni-trash"></em>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            $('#staffSection-' + serviceId + ' .row').append(staffHtml);
            $('#addProjectServiceStaffForm')[0].reset();
            $('#addProjectServiceStaffModal').modal('hide');
            showPopup(response.message);
        },
        error: function(xhr) {
            console.log("Error adding staff assignment:", xhr.responseText);
            showPopup("Error adding staff assignment.");
        }
    });
});

$(document).on('click', '.toggleStaffBtn', function(e) {
    e.preventDefault();
    var staffId = $(this).data('staff-id');
    var serviceId = $(this).data('service-id');
    var btn = $(this);
    $.ajax({
        url: "{{ route('project_user.toggle') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            project_service_id: serviceId,
            user_id: staffId
        },
        success: function(response) {
            btn.text(response.new_status === 'active' ? 'Deactivate' : 'Activate');
            showPopup(response.message);
        },
        error: function(xhr) {
            console.log("Toggle Staff Error:", xhr.responseText);
            showPopup("Error toggling staff status.");
        }
    });
});
$(document).on('click', '.deleteStaffBtn', function(e) {
    e.preventDefault();
    if (confirm('Are you sure you want to remove this staff from the service?')) {
        var pivotId = $(this).data('pivot-id'); // assuming your controller returns assignment id
        var serviceId = $(this).data('service-id');
        $.ajax({
            url: "{{ route('project_user.destroy', ':id') }}".replace(':id', pivotId),
            type: "DELETE",
            data: { _token: "{{ csrf_token() }}" },
            success: function(response) {
                $('#staffRow-' + $(this).data('staff-id') + '-' + serviceId).remove();
                showPopup(response.message);
            },
            error: function(xhr) {
                console.log("Delete Staff Error:", xhr.responseText);
                showPopup("Error deleting staff assignment.");
            }
        });
    }
});


// Save new milestone (this might be triggered from a form inside a modal)
function saveMilestone() {
    var formData = new FormData($('#addMilestoneForm')[0]);
    $.ajax({
        url: "{{ route('project_milestone.store') }}",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            showPopup(response.message);
            $('#addMilestoneModal').modal('hide');
            location.reload(); // Alternatively, update the timeline section dynamically
        },
        error: function(xhr) {
            console.log("Save Milestone Error:", xhr.responseText);
            showPopup("Error saving milestone.");
        }
    });
}

// Load edit milestone form via Ajax
function editMilestone(id) {
    $.ajax({
        url: "{{ url('project-milestones') }}/" + id + "/edit",
        type: "GET",
        success: function(response) {
            $('#editMilestoneModal .modal-content').html(response);
            $('#editMilestoneModal').modal('show');
        },
        error: function(xhr) {
            console.log("Edit Milestone Error:", xhr.responseText);
            showPopup("Error loading edit form.");
        }
    });
}

$(document).on('click', '#updateMilestoneBtn', function(e) {
    e.preventDefault();
    var formData = new FormData($('#editMilestoneForm')[0]);
    var milestoneId = $('#editMilestoneForm input[name="id"]').val();
    
    $.ajax({
        url: "{{ url('project-milestones') }}/" + milestoneId,
        type: "POST", // using method spoofing via _method: PATCH in the form
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            var milestone = response.milestone;
            var serviceId = milestone.project_service_id;
            
            // Build updated HTML for the timeline item (similar to save function)
            var statusClass = milestone.status === 'pending' ? 'bg-primary is-outline' : 
                              milestone.status === 'completed' ? 'bg-success' : 'bg-danger';
            var dueDate = milestone.due_date ? milestone.due_date : 'No Date';
            
            var updatedHtml = `
                <div class="timeline-status ${statusClass}"></div>
                <div class="timeline-date">
                    ${dueDate} <em class="icon ni ni-alarm-alt"></em>
                </div>
                <div class="timeline-data">
                    <h6 class="timeline-title">${milestone.title}</h6>
                    <div class="timeline-des">
                        <p>${ milestone.description || '' }</p>
                        <span class="time">${ milestone.status }</span>
                    </div>
                    <div class="timeline-actions" style="float:right;">
                        <a  class="editMilestoneBtn" data-id="${milestone.id}" title="Edit">
                            <em class="icon ni ni-edit"></em>
                        </a>
                        <a  class="deleteMilestoneBtn" data-id="${milestone.id}" title="Delete">
                            <em class="icon ni ni-trash"></em>
                        </a>
                    </div>
                </div>
            `;
            // Replace the HTML inside the corresponding timeline item
            $('#milestoneRow-' + milestoneId).html(updatedHtml);
            
            // Hide the edit modal
            $('#editMilestoneModal').modal('hide');
            
            // Show popup message
            showPopup(response.message);
        },
        error: function(xhr, status, error) {
            console.log("Update Milestone Error:", xhr.responseText);
            showPopup("Error updating milestone.");
        }
    });
});


$(document).on('click', '#saveMilestoneBtn', function(e) {
    e.preventDefault();
    var formData = new FormData($('#addMilestoneForm')[0]);
    
    $.ajax({
        url: "{{ route('project_milestone.store') }}",  // Ensure this route exists
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            // Assuming response contains the new milestone object with project_service_id
            var milestone = response.milestone;
            var serviceId = milestone.project_service_id;
            
            // Build HTML for the new timeline item
            var statusClass = milestone.status === 'pending' ? 'bg-primary is-outline' : 
                              milestone.status === 'completed' ? 'bg-success' : 'bg-danger';
            var dueDate = milestone.due_date ? milestone.due_date : 'No Date';
            
            var newTimelineItem = `
                <li class="timeline-item" id="milestoneRow-${milestone.id}">
                    <div class="timeline-status ${statusClass}"></div>
                    <div class="timeline-date">
                        ${dueDate} <em class="icon ni ni-alarm-alt"></em>
                    </div>
                    <div class="timeline-data">
                        <h6 class="timeline-title">${milestone.title}</h6>
                        <div class="timeline-des">
                            <p>${ milestone.description || '' }</p>
                            <span class="time">${ milestone.status }</span>
                        </div>
                        <div class="timeline-actions" style="float:right;">
                            <a  class="editMilestoneBtn" data-id="${milestone.id}" title="Edit">
                                <em class="icon ni ni-edit"></em>
                            </a>
                            <a  class="deleteMilestoneBtn" data-id="${milestone.id}" title="Delete">
                                <em class="icon ni ni-trash"></em>
                            </a>
                        </div>
                    </div>
                </li>
            `;
            // Append to the correct timeline UL
            $('#timelineList-' + serviceId).append(newTimelineItem);

            // Clear the form inputs
            $('#addMilestoneForm')[0].reset();

            // Hide the add milestone modal
            $('#addMilestoneModal').modal('hide');

            // Show popup message
            showPopup(response.message);
        },
        error: function(xhr, status, error) {
            console.log("Save Milestone Error:", xhr.responseText);
            showPopup("Error saving milestone.");
        }
    });
});


// Delete milestone via Ajax
function deleteMilestone(id) {
    if (confirm('Are you sure you want to delete this milestone?')) {
        $.ajax({
            url: "{{ url('project-milestones') }}/" + id,
            type: "DELETE",
            data: { _token: "{{ csrf_token() }}" },
            success: function(response) {
                showPopup(response.message);
                // Remove the milestone from the DOM
                $('#milestoneRow-' + id).remove();
            },
            error: function(xhr, status, error) {
                console.log("Delete Milestone Error:", xhr.responseText);
                showPopup("Error deleting milestone.");
            }
        });
    }
}


// Bind events for edit and delete buttons (adjust selectors as needed)
$(document).on('click', '.editMilestoneBtn', function() {
    var id = $(this).data('id');
    editMilestone(id);
});

$(document).on('click', '.deleteMilestoneBtn', function() {
    var id = $(this).data('id');
    deleteMilestone(id);
});

// Save new Project Service via Ajax
function saveProjectService() {
    var formData = new FormData($('#addProjectServiceForm')[0]);
    $.ajax({
        url: "{{ route('project_service.store') }}",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            showPopup(response.message);
            $('#addProjectServiceModal').modal('hide');
            location.reload(); // or update the #projectServicesTableBody via DOM manipulation
        },
        error: function(xhr) {
            console.log("Save Project Service Error:", xhr.responseText);
            showPopup("Error saving project service.");
        }
    });
}

// Load Edit Project Service form via Ajax
function editProjectService(id) {
    $.ajax({
        url: "{{ url('project-services') }}/" + id + "/edit",
        type: "GET",
        success: function(response) {
            $('#editProjectServiceModal').modal('show');
            $('#editProjectServiceModal .modal-content').html(response);
        },
        error: function(xhr) {
            console.log("Edit Project Service Error:", xhr.responseText);
            showPopup("Error loading edit form.");
        }
    });
}

// Update Project Service via Ajax
function updateProjectService() {
    var formData = new FormData($('#editProjectServiceForm')[0]);
    var projectServiceId = $('#editProjectServiceForm input[name="id"]').val();
    $.ajax({
        url: "{{ url('project-services') }}/" + projectServiceId,
        type: "POST", // using method spoofing (_method: PATCH in form)
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            showPopup(response.message);
            $('#editProjectServiceModal').modal('hide');
            location.reload(); // or update the table row dynamically
        },
        error: function(xhr) {
            console.log("Update Project Service Error:", xhr.responseText);
            showPopup("Error updating project service.");
        }
    });
}

// Delete Project Service via Ajax
function deleteProjectService(id) {
    if (confirm('Are you sure you want to delete this project service?')) {
        $.ajax({
            url: "{{ url('project-services') }}/" + id,
            type: "DELETE",
            data: { _token: "{{ csrf_token() }}" },
            success: function(response) {
                showPopup(response.message);
                $('#projectServiceRow-' + id).remove();
            },
            error: function(xhr) {
                console.log("Delete Project Service Error:", xhr.responseText);
                showPopup("Error deleting project service.");
            }
        });
    }
}

// Utility: Show popup modal with message
function showPopup(message) {
    $('#popupMessageModal .modal-body').html("<p>" + message + "</p>");
    $('#popupMessageModal').modal('show');
}

// Bind events
$(document).on('click', '#saveProjectServiceBtn', function(e) {
    saveProjectService();
});
$(document).on('click', '.editProjectServiceBtn', function(e) {
    var id = $(this).data('id');
    editProjectService(id);
});
$(document).on('click', '#updateProjectServiceBtn', function(e) {
    updateProjectService();
});
$(document).on('click', '.deleteProjectServiceBtn', function(e) {
    var id = $(this).data('id');
    deleteProjectService(id);
});



// $(document).ready(function() {
//     setTimeout(function() {
//         if (typeof $.fn.summernote !== 'undefined') { 
//             $('#summernote').summernote({
//                 height: 200,
//                 placeholder: 'Type here...',
//                 toolbar: [
//                     ['style', ['bold', 'italic', 'underline']],
//                     ['para', ['ul', 'ol']],
//                     ['insert', ['link']],
//                     ['view', ['codeview']]
//                 ]
//             });
//         } else {
//             console.error("❌ Summernote still not loaded.");
//         }
//     }, 1000); // 1-second delay
// });
// </script>
 <script>
// var jq = jQuery.noConflict();
// jq(document).ready(function() {
//     jq('#summernote').summernote({
//         height: 200
//     });
// });
</script>

@endsection
