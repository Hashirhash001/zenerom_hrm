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
                            <h3 class="nk-block-title page-title">Projects</h3>
                            <div class="nk-block-des text-soft">
                                <p>You have total {{ $projects->count() }} Projects.</p>
                            </div>
                        </div>
                        <div class="nk-block-head-content">
                            <div class="toggle-wrap nk-block-tools-toggle">
                                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu">
                                    <em class="icon ni ni-menu-alt-r"></em>
                                </a>
                                <div class="toggle-expand-content" data-content="pageMenu">
                                    <ul class="nk-block-tools g-3">
                                        <li>
                                            <div>
                                                <input type="text" class="form-control" id="projectSearch" placeholder="Search Projects">
                                            </div>
                                        </li>
                                        <li class="nk-block-tools-opt d-none d-sm-block">
                                            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProjectModal">
                                                <em class="icon ni ni-plus"></em><span>Add Project</span>
                                            </a>
                                        </li>
                                        <li class="nk-block-tools-opt d-block d-sm-none">
                                            <a href="#" class="btn btn-icon btn-primary" data-bs-toggle="modal" data-bs-target="#addProjectModal">
                                                <em class="icon ni ni-plus"></em>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div><!-- .toggle-wrap -->
                        </div>
                    </div>
                </div>
                <!-- End Header -->

                <!-- Projects List Container -->
                <div id="projectsContainer">
                    @include('projects._list', ['projects' => $projects])
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Adding New Project -->
<div class="modal fade" id="addProjectModal" tabindex="-1" aria-labelledby="addProjectModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      @include('projects.create_modal', ['customers' => $customers, 'departments' => $departments])
    </div>
  </div>
</div>

<!-- Modal for Editing Project -->
<div class="modal fade" id="editProjectModal" tabindex="-1" aria-labelledby="editProjectModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <!-- Edit form loaded via Ajax -->
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
<script src="{{ asset('assets1/jquery.min.js') }}"></script>
<script src="{{ asset('assets1/jquery-ui/jquery-ui.js') }}"></script>
<script>
    // Save new Project via Ajax
    function saveProject() {
        var formData = new FormData($('#addProjectForm')[0]);
        $.ajax({
            url: "{{ route('project.store') }}",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                showPopup(response.message);
                $('#addProjectModal').modal('hide');
                location.reload();
            },
            error: function(xhr) {
                console.log("Save Project Error:", xhr.responseText);
                showPopup("Error saving project.");
            }
        });
    }

    // Load Edit Project form via Ajax
    function editProject(id) {
        $.ajax({
            url: "{{ url('projects') }}/" + id + "/edit",
            type: "GET",
            success: function(response) {
                $('#editProjectModal .modal-content').html(response);
                $('#editProjectModal').modal('show');
            },
            error: function(xhr) {
                console.log("Edit Project Error:", xhr.responseText);
                showPopup("Error loading edit form.");
            }
        });
    }

    // Update Project via Ajax
    function updateProject() {
        var formData = new FormData($('#editProjectForm')[0]);
        var projectId = $('#editProjectForm input[name="id"]').val();
        $.ajax({
            url: "{{ url('projects') }}/" + projectId,
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                showPopup(response.message);
                $('#editProjectModal').modal('hide');
                location.reload();
            },
            error: function(xhr) {
                console.log("Update Project Error:", xhr.responseText);
                showPopup("Error updating project.");
            }
        });
    }

    // Delete Project via Ajax
    function deleteProject(id) {
        if (confirm('Are you sure you want to delete this project?')) {
            $.ajax({
                url: "{{ url('projects') }}/" + id,
                type: "DELETE",
                data: { _token: "{{ csrf_token() }}" },
                success: function(response) {
                    showPopup(response.message);
                    location.reload();
                },
                error: function(xhr) {
                    console.log("Delete Project Error:", xhr.responseText);
                    showPopup("Error deleting project.");
                }
            });
        }
    }

    // Live search for projects
    $('#projectSearch').on('keyup', function(){
        var query = $(this).val();
        $.ajax({
            url: "{{ route('project.search') }}",
            type: "GET",
            data: { query: query },
            success: function(response) {
                $('#projectsContainer').html(response.html);
            },
            error: function(xhr) {
                console.log("Project Search Error:", xhr.responseText);
            }
        });
    });

    // Utility: Show popup modal with message
    function showPopup(message) {
        $('#popupMessageModal .modal-body').html("<p>" + message + "</p>");
        $('#popupMessageModal').modal('show');
    }

    // Bind events
    $(document).on('click', '#saveProjectBtn', function(e) {
        saveProject();
    });
    $(document).on('click', '.editProjectBtn', function(e) {
        var id = $(this).data('id');
        editProject(id);
    });
    $(document).on('click', '.deleteProjectBtn', function(e) {
        var id = $(this).data('id');
        deleteProject(id);
    });
    $(document).on('click', '#updateProjectBtn', function(e) {
        updateProject();
    });
</script>
@endsection
