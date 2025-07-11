@extends('layouts.app')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <!-- Header -->
                <div class="nk-block-head nk-block-head-sm">
                    <div class="nk-block-between">
                        <div class="nk-block-head-content">
                            <h3 class="nk-block-title page-title">Employees</h3>
                            <div class="nk-block-des text-soft">
                                <p>You have total {{ $employees->count() }} Employees.</p>
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
                                                <input type="text" class="form-control" id="employeeSearch" placeholder="Search Employees">
                                            </div>
                                        </li>
                                        <li class="nk-block-tools-opt d-none d-sm-block">
                                            <!-- Add Employee button triggers the add modal -->
                                            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                                                <em class="icon ni ni-plus"></em><span>Add Employee</span>
                                            </a>
                                        </li>
                                        @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 2)
                                                <li class="nk-block-tools-opt d-none d-sm-block">
                                                    <a  class="btn btn-primary" href="{{ route('staff-task.report') }}">
                                                        <em class="icon ni ni-file-docs"></em>
                                                        <span>Staff Task Report</span>
                                                    </a>
                                                </li>
                                            @endif
                                        <li class="nk-block-tools-opt d-block d-sm-none">
                                            <a href="#" class="btn btn-icon btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
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

                <!-- Employee List Container -->
                <div id="employeesContainer">
                    @include('employees._list', ['employees' => $employees])
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Adding New Employee -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="addEmployeeForm" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="addEmployeeModalLabel">Add New Employee</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Employee ID -->
          <div class="row">
            <div class="col-md-4 mb-3">
              <label for="employee_id" class="form-label">Employee ID</label>
              <input type="text" class="form-control" id="employee_id" name="employee_id" required>
            </div>
          </div>
          <!-- Basic Information -->
          <div class="row">
            <div class="col-md-4 mb-3">
              <label for="first_name" class="form-label">First Name</label>
              <input type="text" class="form-control" id="first_name" name="first_name" required>
            </div>
            <div class="col-md-4 mb-3">
              <label for="middle_name" class="form-label">Middle Name</label>
              <input type="text" class="form-control" id="middle_name" name="middle_name">
            </div>
            <div class="col-md-4 mb-3">
              <label for="last_name" class="form-label">Last Name</label>
              <input type="text" class="form-control" id="last_name" name="last_name" required>
            </div>
          </div>
          <!-- Contact Information -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="email" class="form-label">Personal Email</label>
              <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="company_email" class="form-label">Company Email</label>
              <input type="email" class="form-control" id="company_email" name="company_email">
            </div>
          </div>
          <!-- Phone & WhatsApp -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="phone" class="form-label">Phone</label>
              <input type="text" class="form-control" id="phone" name="phone">
            </div>
            <div class="col-md-6 mb-3">
              <label for="whatsapp" class="form-label">WhatsApp</label>
              <input type="text" class="form-control" id="whatsapp" name="whatsapp">
            </div>
          </div>
          <!-- Emergency Contacts -->
          <div class="row">
            <div class="col-md-4 mb-3">
              <label for="emergency_contact" class="form-label">Emergency Contact</label>
              <input type="text" class="form-control" id="emergency_contact" name="emergency_contact">
            </div>
            <div class="col-md-4 mb-3">
              <label for="emergency_contact_name" class="form-label">Emergency Contact Name</label>
              <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name">
            </div>
            <div class="col-md-4 mb-3">
              <label for="age" class="form-label">Age</label>
              <input type="number" class="form-control" id="age" name="age">
            </div>
          </div>
          <!-- Additional Information -->
          <div class="row">
            <div class="col-md-4 mb-3">
              <label for="gender" class="form-label">Gender</label>
              <select class="form-control" id="gender" name="gender">
                <option value="">Select</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label for="dob" class="form-label">Date of Birth</label>
              <input type="date" class="form-control" id="dob" name="dob">
            </div>
            <div class="col-md-4 mb-3">
              <label for="blood_group" class="form-label">Blood Group</label>
              <input type="text" class="form-control" id="blood_group" name="blood_group">
            </div>
          </div>
          <!-- Addresses -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="permanent_address" class="form-label">Permanent Address</label>
              <textarea class="form-control" id="permanent_address" name="permanent_address"></textarea>
            </div>
            <div class="col-md-6 mb-3">
              <label for="local_address" class="form-label">Local Address</label>
              <textarea class="form-control" id="local_address" name="local_address"></textarea>
            </div>
          </div>
          <!-- File Uploads -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="image" class="form-label">Profile Image</label>
              <input type="file" class="form-control" id="image" name="image">
            </div>
            <div class="col-md-6 mb-3">
              <label for="cv_file" class="form-label">CV File</label>
              <input type="file" class="form-control" id="cv_file" name="cv_file">
            </div>
          </div>
          <!-- Department & Role -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="department_id" class="form-label">Department</label>
              <select class="form-control" id="department_id" name="department_id">
                <option value="">Select Department</option>
                @foreach($departments as $dept)
                  <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="role_id" class="form-label">Role</label>
              <select class="form-control" id="role_id" name="role_id">
                <option value="">Select Role</option>
                @foreach($roles as $role)
                  <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <!-- Work Timing -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="work_start_time" class="form-label">Work Start Time</label>
                    <input type="time" class="form-control" id="work_start_time" name="work_start_time">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="work_end_time" class="form-label">Work End Time</label>
                    <input type="time" class="form-control" id="work_end_time" name="work_end_time">
                </div>
            </div>
          <!-- Hidden Status -->
          <input type="hidden" name="status" value="1">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="saveEmployeeBtn">Save Employee</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- End Add Employee Modal -->

<!-- Modal for Viewing Employee Details -->
<div class="modal fade" id="viewEmployeeModal" tabindex="-1" aria-labelledby="viewEmployeeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <!-- Content loaded via Ajax -->
    </div>
  </div>
</div>

<!-- Modal for Editing Employee -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <!-- Edit form loaded via Ajax -->
    </div>
  </div>
</div>

<!-- Modal for Resignation -->
<div class="modal fade" id="resignEmployeeModal" tabindex="-1" aria-labelledby="resignEmployeeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <!-- Resignation form loaded via Ajax -->
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
        <!-- Message goes here -->
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


// Save new Employee via Ajax
function saveEmployee() {
    var formData = new FormData($('#addEmployeeForm')[0]);
    $.ajax({
        url: "{{ route('employee.store') }}",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            alert(response.message);
            $('#addEmployeeModal').modal('hide');
            location.reload();
        },
        error: function(xhr, status, error) {
            console.log("Save Error:", xhr.responseText);
            alert("Error saving employee.");
        }
    });
}



// Delete Employee via Ajax
function deleteEmployee(id) {
    if (confirm('Are you sure you want to delete this employee?')) {
        $.ajax({
            url: "{{ url('employees') }}/" + id,
            type: "DELETE",
            data: { _token: "{{ csrf_token() }}" },
            success: function(response) {
                alert(response.message);
                location.reload();
            },
            error: function(xhr, status, error) {
                console.log("Delete Error:", xhr.responseText);
                alert("Error deleting employee.");
            }
        });
    }
}

// Load Edit Employee form via Ajax
function editEmployee(id) {
    $.ajax({
        url: "{{ url('employees') }}/" + id + "/edit",
        type: "GET",
        success: function(response) {
            $('#editEmployeeModal .modal-content').html(response);
            $('#editEmployeeModal').modal('show');
        },
        error: function(xhr, status, error) {
            console.log("Edit Form Error:", xhr.responseText);
            alert("Error loading edit form.");
        }
    });
}

// Update Employee via Ajax
function updateEmployee() {
    var formData = new FormData($('#editEmployeeForm')[0]);
    var employeeId = $('#editEmployeeForm input[name="employee_id"]').val();
    $.ajax({
        url: "{{ url('employees') }}/" + employeeId,
        type: "POST", // using method spoofing (_method in form)
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            alert(response.message);
            $('#editEmployeeModal').modal('hide');
            location.reload();
        },
        error: function(xhr, status, error) {
            console.log("Update Error:", xhr.responseText);
            alert("Error updating employee.");
        }
    });
}

// Load View Employee Details via Ajax
function viewEmployee(id) {
    $.ajax({
        url: "{{ url('employees') }}/" + id + "/view",
        type: "GET",
        success: function(response) {
            $('#viewEmployeeModal .modal-content').html(response);
            $('#viewEmployeeModal').modal('show');
        },
        error: function(xhr, status, error) {
            console.log("View Error:", xhr.responseText);
            alert("Error loading employee details.");
        }
    });
}

// Load Resignation form via Ajax
function resignEmployee(id) {
    $.ajax({
        url: "{{ url('employees') }}/" + id + "/resign",
        type: "GET",
        success: function(response) {
            $('#resignEmployeeModal .modal-content').html(response);
            $('#resignEmployeeModal').modal('show');
        },
        error: function(xhr, status, error) {
            console.log("Resign Form Error:", xhr.responseText);
            alert("Error loading resignation form.");
        }
    });
}



// Submit Access Control Form via Ajax
$(document).on('click', '#saveAccessControlBtn', function(e) {
    var formData = new FormData($('#accessControlForm')[0]);
    // Retrieve the employee's id from the hidden input
    var employeeId = $('#employee_id1234').val();
    console.log("Employee ID: " + employeeId); // Debug output

    // Ensure employeeId is not empty
    if(!employeeId) {
        showPopup("Employee ID is missing.");
        return;
    }

    $.ajax({
        url: "{{ url('employees') }}/" + employeeId + "/access",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            showPopup(response.message);
        },
        error: function(xhr, status, error) {
            console.log("Access Control Error:", xhr.responseText);
            showPopup("Error saving access control privileges.");
        }
    });
});


// Example function to show a popup modal with a message



//access control submenu activate settings
$(document).on('change', '.parent-view', function() {
    var parentId = $(this).data('parent-id');
    var isChecked = $(this).is(':checked');
    console.log("Parent ID: " + parentId + " isChecked: " + isChecked); // Debugging output
    // Find all child checkboxes for this parent (by class)
    $('.child-' + parentId).prop('disabled', !isChecked);
    if (!isChecked) {
        $('.child-' + parentId).prop('checked', false);
    }
});


// password inline validation
$(document).on('blur', '#account_password', function() {
    var password = $(this).val();
    if(password && password.length < 6) {
        $(this).addClass('is-invalid');
        $(this).siblings('.invalid-feedback').remove();
        $(this).after('<div class="invalid-feedback">Password must be at least 6 characters.</div>');
    } else {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').remove();
    }
});



// Submit Resignation via Ajax
$(document).on('click', '#submitResignationBtn', function(e) {
    var employeeId = $(this).data('employee');
    var formData = new FormData($('#resignEmployeeForm')[0]);
    $.ajax({
        url: "{{ url('employees') }}/" + employeeId + "/resign",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            alert(response.message);
            $('#resignEmployeeModal').modal('hide');
            location.reload();
        },
        error: function(xhr, status, error) {
            console.log("Resignation Error:", xhr.responseText);
            alert("Error submitting resignation.");
        }
    });
});


// Toggle Activation via Ajax
function toggleEmployee(id) {
    $.ajax({
        url: "{{ url('employees') }}/" + id + "/toggle",
        type: "POST",
        data: { _token: "{{ csrf_token() }}" },
        success: function(response) {
            alert(response.message);
            location.reload();
        },
        error: function(xhr, status, error) {
            console.log("Toggle Error:", xhr.responseText);
            alert("Error toggling employee status.");
        }
    });
}
// Function to show a popup modal with a message
function showPopup(message) {
    $('#popupMessageModal .modal-body').html("<p>" + message + "</p>");
    $('#popupMessageModal').modal('show');
}

// Save Account via Ajax
$(document).on('click', '#saveAccountBtn', function(e) {
    var formData = new FormData($('#accountForm')[0]);
    $.ajax({
        url: "{{ route('employee.account.save') }}",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            showPopup(response.message);
        },
        error: function(xhr, status, error) {
            console.log("Account Save Error:", xhr.responseText);
            showPopup("Error saving account details. Please try again.");
        }
    });
});

// Bind events
$(document).on('click', '#saveEmployeeBtn', function(e) {
    saveEmployee();
});
$(document).on('click', '.deleteEmployeeBtn', function(e) {
    var id = $(this).data('id');
    deleteEmployee(id);
});
$(document).on('click', '.editEmployeeBtn', function(e) {
    var id = $(this).data('id');
    editEmployee(id);
});

$(document).on('click', '.viewEmployeeBtn', function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    viewEmployee(id);
});
$(document).on('click', '#updateEmployeeBtn', function(e) {
    updateEmployee();
});
$(document).on('click', '.resignEmployeeBtn', function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    resignEmployee(id);
});
$(document).on('click', '.toggleEmployeeBtn', function(e) {
    var id = $(this).data('id');
    toggleEmployee(id);
});

// Live search for employees
$('#employeeSearch').on('keyup', function(){
    var query = $(this).val();
    $.ajax({
        url: "{{ route('employee.search') }}",
        type: "GET",
        data: { query: query },
        success: function(response) {
            $('#employeesContainer').html(response.html);
        },
        error: function(xhr, status, error) {
            console.log("Search Error:", xhr.responseText);
        }
    });
});
</script>
@endsection
