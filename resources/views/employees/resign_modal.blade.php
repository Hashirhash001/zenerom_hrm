<div class="modal-header">
    <h5 class="modal-title">Resignation Details for {{ $employee->first_name }} {{ $employee->last_name }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="resignEmployeeForm">
        @csrf
        <div class="mb-3">
            <label for="resignation" class="form-label">Resignation Date</label>
            <input type="date" class="form-control" id="resignation" name="resignation" required>
        </div>
        <div class="mb-3">
            <label for="resignation_details" class="form-label">Resignation Details</label>
            <textarea class="form-control" id="resignation_details" name="resignation_details"></textarea>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    <button type="button" class="btn btn-primary" id="submitResignationBtn" data-employee="{{ $employee->id }}">Submit Resignation</button>
</div>
