@extends('layouts.app')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="nk-block-head nk-block-head-sm">
                    <div class="nk-block-between">
                        <div class="nk-block-head-content">
                            <h3 class="nk-block-title page-title">Roles</h3>
                            <div class="nk-block-des text-soft">
                                <p>You have total {{ $roles->count() }} Roles.</p>
                            </div>
                        </div><!-- .nk-block-head-content -->
                        <div class="nk-block-head-content">
                            <div class="toggle-wrap nk-block-tools-toggle">
                                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu">
                                    <em class="icon ni ni-menu-alt-r"></em>
                                </a>
                                <div class="toggle-expand-content" data-content="pageMenu">
                                    <ul class="nk-block-tools g-3">
                                        <li>
                                            <div>
                                                <input type="text" class="form-control" id="roleSearch" placeholder="Search By Name">
                                            </div>
                                        </li>
                                        <li class="nk-block-tools-opt d-none d-sm-block">
                                            <!-- Add Role button triggers the modal -->
                                            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                                                <em class="icon ni ni-plus"></em><span>Add Role</span>
                                            </a>
                                        </li>
                                        <li class="nk-block-tools-opt d-block d-sm-none">
                                            <a href="#" class="btn btn-icon btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                                                <em class="icon ni ni-plus"></em>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div><!-- .toggle-wrap -->
                        </div><!-- .nk-block-head-content -->
                    </div><!-- .nk-block-between -->
                </div><!-- .nk-block-head -->
                <div class="nk-block">
                    <div id="rolesContainer">
                        @include('roles._list', ['roles' => $roles])
                    </div>
                </div><!-- .nk-block -->
            </div>
        </div>
    </div>
</div>

<!-- Modal for Adding New Role -->
<div class="modal fade" id="addRoleModal" tabindex="-1" aria-labelledby="addRoleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="addRoleForm">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="addRoleModalLabel">Add New Role</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="role_name" class="form-label">Role Name</label>
            <input type="text" class="form-control" id="role_name" name="name" required>
          </div>
          <div class="mb-3">
            <label for="role_description" class="form-label">Description</label>
            <textarea class="form-control" id="role_description" name="description"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="saveRoleBtn">Save Role</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal for Editing Role (Content loaded via Ajax) -->
<div class="modal fade" id="editRoleModal" tabindex="-1" aria-labelledby="editRoleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <!-- Edit form will be loaded here -->
    </div>
  </div>
</div>
<script src="{{ asset('assets1/jquery.min.js') }}"></script>
<script src="{{ asset('assets1/jquery-ui/jquery-ui.js') }}"></script>
<script>
// Save Role via Ajax
function saveRole() {
    var formData = new FormData($('#addRoleForm')[0]);
    $.ajax({
        url: "{{ route('role.store') }}",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            alert(response.message);
            $('#addRoleModal').modal('hide');
            location.reload();
        },
        error: function(xhr, status, error) {
            console.log("Error saving role:", xhr.responseText);
            alert('Error: ' + xhr.responseText);
        }
    });
}

// Delete Role via Ajax
function deleteRole(id) {
    if (confirm('Are you sure you want to delete this role?')) {
        $.ajax({
            url: "{{ url('roles') }}/" + id,
            type: "DELETE",
            data: { _token: "{{ csrf_token() }}" },
            success: function(response) {
                alert(response.message);
                location.reload();
            },
            error: function(xhr, status, error) {
                console.log("Error deleting role:", xhr.responseText);
                alert('Error: ' + xhr.responseText);
            }
        });
    }
}

// Load Edit Role form via Ajax
function editRole(id) {
    $.ajax({
        url: "{{ url('roles') }}/" + id + "/edit",
        type: "GET",
        success: function(response) {
            $('#editRoleModal .modal-content').html(response);
            $('#editRoleModal').modal('show');
        },
        error: function(xhr, status, error) {
            console.log("Error loading edit form:", xhr.responseText);
            alert("Error loading edit form.");
        }
    });
}

// Update Role via Ajax
function updateRole() {
    var formData = new FormData($('#editRoleForm')[0]);
    var roleId = $('#editRoleForm input[name="role_id"]').val();
    $.ajax({
        url: "{{ url('roles') }}/" + roleId,
        type: "POST", // Method spoofing PATCH with _method in form
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            alert(response.message);
            $('#editRoleModal').modal('hide');
            location.reload();
        },
        error: function(xhr, status, error) {
            console.log("Error updating role:", xhr.responseText);
            alert("Error updating role.");
        }
    });
}

$(document).on('click', '#saveRoleBtn', function(e) {
    saveRole();
});

$(document).on('click', '#updateRoleBtn', function(e) {
    updateRole();
});

// Use delegated events for dynamically loaded buttons
$(document).on('click', '.deleteRoleBtn', function(e) {
    var id = $(this).data('id');
    deleteRole(id);
});

$(document).on('click', '.editRoleBtn', function(e) {
    var id = $(this).data('id');
    editRole(id);
});

// Live search for roles
$('#roleSearch').on('keyup', function(){
    var query = $(this).val();
    $.ajax({
        url: "{{ route('role.search') }}",
        type: "GET",
        data: { query: query },
        success: function(response) {
            $('#rolesContainer').html(response.html);
        },
        error: function(xhr, status, error) {
            console.log("Search error:", xhr.responseText);
        }
    });
});
</script>
@endsection
