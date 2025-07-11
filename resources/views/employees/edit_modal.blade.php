<form id="editEmployeeForm" enctype="multipart/form-data">
    @csrf
    @method('PATCH')
    <div class="modal-header">
          <h5 class="modal-title" id="addEmployeeModalLabel">Update Employee</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
    <input type="hidden" name="employee_id" value="{{ $employee->id }}">
    <!-- Employee ID (custom field) -->
    <div class="mb-3">
      <label for="edit_employee_id" class="form-label">Employee ID</label>
      <input type="text" class="form-control" id="edit_employee_id" name="employee_id" value="{{ $employee->employee_id }}" required>
    </div>
    <!-- Basic Information -->
    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="edit_first_name" class="form-label">First Name</label>
            <input type="text" class="form-control" id="edit_first_name" name="first_name" value="{{ $employee->first_name }}" required>
        </div>
        <div class="col-md-4 mb-3">
            <label for="edit_middle_name" class="form-label">Middle Name</label>
            <input type="text" class="form-control" id="edit_middle_name" name="middle_name" value="{{ $employee->middle_name }}">
        </div>
        <div class="col-md-4 mb-3">
            <label for="edit_last_name" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="edit_last_name" name="last_name" value="{{ $employee->last_name }}" required>
        </div>
    </div>
    <!-- Contact Information -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="edit_email" class="form-label">Personal Email</label>
            <input type="email" class="form-control" id="edit_email" name="email" value="{{ $employee->email }}" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="edit_company_email" class="form-label">Company Email</label>
            <input type="email" class="form-control" id="edit_company_email" name="company_email" value="{{ $employee->company_email }}">
        </div>
    </div>
    <!-- Phone & WhatsApp -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="edit_phone" class="form-label">Phone</label>
            <input type="text" class="form-control" id="edit_phone" name="phone" value="{{ $employee->phone }}">
        </div>
        <div class="col-md-6 mb-3">
            <label for="edit_whatsapp" class="form-label">WhatsApp</label>
            <input type="text" class="form-control" id="edit_whatsapp" name="whatsapp" value="{{ $employee->whatsapp }}">
        </div>
    </div>
    <!-- Emergency Contacts -->
    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="edit_emergency_contact" class="form-label">Emergency Contact</label>
            <input type="text" class="form-control" id="edit_emergency_contact" name="emergency_contact" value="{{ $employee->emergency_contact }}">
        </div>
        <div class="col-md-4 mb-3">
            <label for="edit_emergency_contact_name" class="form-label">Emergency Contact Name</label>
            <input type="text" class="form-control" id="edit_emergency_contact_name" name="emergency_contact_name" value="{{ $employee->emergency_contact_name }}">
        </div>
        <div class="col-md-4 mb-3">
            <label for="edit_age" class="form-label">Age</label>
            <input type="number" class="form-control" id="edit_age" name="age" value="{{ $employee->age }}">
        </div>
    </div>
    <!-- Additional Information -->
    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="edit_gender" class="form-label">Gender</label>
            <select class="form-control" id="edit_gender" name="gender">
                <option value="">Select</option>
                <option value="Male" @if($employee->gender=='Male') selected @endif>Male</option>
                <option value="Female" @if($employee->gender=='Female') selected @endif>Female</option>
                <option value="Other" @if($employee->gender=='Other') selected @endif>Other</option>
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label for="edit_dob" class="form-label">Date of Birth</label>
            <input type="date" class="form-control" id="edit_dob" name="dob" value="{{ $employee->dob }}">
        </div>
        <div class="col-md-4 mb-3">
            <label for="edit_blood_group" class="form-label">Blood Group</label>
            <input type="text" class="form-control" id="edit_blood_group" name="blood_group" value="{{ $employee->blood_group }}">
        </div>
    </div>
    <!-- Addresses -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="edit_permanent_address" class="form-label">Permanent Address</label>
            <textarea class="form-control" id="edit_permanent_address" name="permanent_address">{{ $employee->permanent_address }}</textarea>
        </div>
        <div class="col-md-6 mb-3">
            <label for="edit_local_address" class="form-label">Local Address</label>
            <textarea class="form-control" id="edit_local_address" name="local_address">{{ $employee->local_address }}</textarea>
        </div>
    </div>
    <!-- File Uploads -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="edit_image" class="form-label">Profile Image</label>
            <input type="file" class="form-control" id="edit_image" name="image">
        </div>
        <div class="col-md-6 mb-3">
            <label for="edit_cv_file" class="form-label">CV File</label>
            <input type="file" class="form-control" id="edit_cv_file" name="cv_file">
        </div>
    </div>
    <!-- Department & Role -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="edit_department_id" class="form-label">Department</label>
            <select class="form-control" id="edit_department_id" name="department_id">
                <option value="">Select Department</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" @if($employee->department_id == $dept->id) selected @endif>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label for="edit_role_id" class="form-label">Role</label>
            <select class="form-control" id="edit_role_id" name="role_id">
                <option value="">Select Role</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" @if($employee->role_id == $role->id) selected @endif>{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <!-- Work Timing -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="work_start_time" class="form-label">Work Start Time</label>
            <input type="time" class="form-control" id="work_start_time" name="work_start_time" value="{{ $employee->work_start_time }}">
        </div>
        <div class="col-md-6 mb-3">
            <label for="work_end_time" class="form-label">Work End Time</label>
            <input type="time" class="form-control" id="work_end_time" name="work_end_time" value="{{ $employee->work_end_time }}">
        </div>
    </div>
    <!-- Hidden Status -->
    <input type="hidden" name="status" value="{{ $employee->status }}">
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="updateEmployeeBtn">Update Employee</button>
    </div>
</form>
