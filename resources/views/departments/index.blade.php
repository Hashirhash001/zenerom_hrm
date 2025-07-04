@extends('layouts.app')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="nk-block-head nk-block-head-sm">
                    <div class="nk-block-between">
                        <div class="nk-block-head-content">
                            <h3 class="nk-block-title page-title">Departments</h3>
                            <div class="nk-block-des text-soft">
                                <p>You have total {{ $departments->count() }} Departments.</p>
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
                                                <input type="text" class="form-control" id="search" placeholder="Search By Name">
                                            </div>
                                        </li>
                                        <li class="nk-block-tools-opt d-none d-sm-block">
                                            <!-- Add Department button triggers the modal -->
                                            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                                                <em class="icon ni ni-plus"></em><span>Add Department</span>
                                            </a>
                                        </li>
                                        <li class="nk-block-tools-opt d-block d-sm-none">
                                            <a href="#" class="btn btn-icon btn-primary" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                                                <em class="icon ni ni-plus"></em>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="nk-block">
                    <!-- Wrap the departments list in a container with an ID -->
                    <div id="departmentsContainer">
                        @include('departments._list', ['departments' => $departments])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Adding New Department -->
<div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="addDepartmentForm" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="addDepartmentModalLabel">Add New Department</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="name" class="form-label">Department Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
          </div>
          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description"></textarea>
          </div>
          <div class="mb-3">
            <label for="image" class="form-label">Department Image</label>
            <input type="file" class="form-control" id="image" name="image">
          </div>
          <input type="hidden" name="status" value="1">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="saveDepartmentBtn">Save Department</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal for Editing Department (content loaded via Ajax) -->
<div class="modal fade" id="editDepartmentModal" tabindex="-1" aria-labelledby="editDepartmentModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <!-- The edit form HTML will be loaded here via Ajax -->
    </div>
  </div>
</div>

<script src="{{ asset('assets1/jquery.min.js') }}"></script>
<script src="{{ asset('assets1/jquery-ui/jquery-ui.js') }}"></script>

<script>
$(document).ready(function(){
    // Trigger Ajax search when user types in the search box
    $('#search').on('keyup', function() {
        var query = $(this).val();

        $.ajax({
            url: "{{ route('department.search') }}",
            type: "GET",
            data: { query: query },
            success: function(response) {
                // Update the departments container with the returned HTML
                $('#departmentsContainer').html(response.html);
            },
            error: function(xhr, status, error) {
                console.log("Search Error:", xhr.responseText);
            }
        });
    });
});
function saveDepartment() {
    // Create FormData object from the form
    var formData = new FormData($('#addDepartmentForm')[0]);

    $.ajax({
        url: "{{ route('department.store') }}",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            // On success, alert the message, close the modal, and reload the page
            alert(response.message);
            $('#addDepartmentModal').modal('hide');
            location.reload();  // Reload to see the new department
        },
        error: function(xhr, status, error) {
            // Log error details to the console for debugging
            console.log("Status: " + status);
            console.log("Error: " + error);
            console.log(xhr.responseText);
            alert('Error: ' + xhr.responseText);
        }
    });
}

function deleteDepartment(id) {
    if (confirm('Are you sure you want to delete this department?')) {
        $.ajax({
            url: "{{ url('departments') }}/" + id, // Adjust URL based on your route prefix if needed
            type: "DELETE",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                alert(response.message);
                location.reload(); // Reload the page to update the list
            },
            error: function(xhr, status, error) {
                console.log("Status: " + status);
                console.log("Error: " + error);
                console.log(xhr.responseText);
                alert('Error deleting department: ' + xhr.responseText);
            }
        });
    }
}

function editDepartment(id) {
    $.ajax({
        url: "{{ url('departments') }}/" + id + "/edit",
        type: "GET",
        success: function(response) {
            // Load the returned HTML into the modal's content
            $('#editDepartmentModal .modal-content').html(response);
            // Show the modal
            $('#editDepartmentModal').modal('show');
        },
        error: function(xhr, status, error) {
            console.log("Error loading edit form:", xhr.responseText);
            alert("Error loading edit form.");
        }
    });
}

function updateDepartment() {
    // Create FormData object from the edit form
    var formData = new FormData($('#editDepartmentForm')[0]);
    var departmentId = $('#editDepartmentForm input[name="department_id"]').val();

    $.ajax({
        url: "{{ url('departments') }}/" + departmentId,
        type: "POST", // using method spoofing with _method PATCH
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            alert(response.message);
            $('#editDepartmentModal').modal('hide');
            location.reload();  // Reload page to reflect changes
        },
        error: function(xhr, status, error) {
            console.log("Update error:", xhr.responseText);
            alert("Error updating department.");
        }
    });
}

$(document).on('click', '#updateDepartmentBtn', function(e) {
    updateDepartment();
});

$(document).ready(function(){
    $('#saveDepartmentBtn').on('click', function(e){
        saveDepartment();
        //alert('hai');
    });
});
</script>

@endsection
