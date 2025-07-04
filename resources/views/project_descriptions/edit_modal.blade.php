{{-- resources/views/project_descriptions/edit_modal.blade.php --}}
<div class="modal-header">
    <h5 class="modal-title" id="editProjectDescriptionModalLabel">Edit Project Description</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
	@isset($projectDescription)
    <form id="editProjectDescriptionForm">
        @csrf
        @method('PATCH')
        <!-- Hidden fields to link the description with its project and service -->
        <input type="hidden" name="id" value="{{ $projectDescription->id }}">
        <input type="hidden" name="project_id" value="{{ $projectDescription->project_id }}">
        <input type="hidden" name="project_service_id" value="{{ $projectDescription->project_service_id }}">
        
        <div class="mb-3">
            <label for="edit_title" class="form-label">Title</label>
            <input type="text" class="form-control" id="edit_title" name="title" value="{{ $projectDescription->title }}" required>
        </div>
        <div class="mb-3">
            <label for="edit_details" class="form-label">Details</label>
            <textarea class="form-control" id="edit_details" name="details" rows="5" required>{{ $projectDescription->details }}</textarea>
        </div>
        <div class="mb-3">
            <label for="edit_status" class="form-label">Status</label>
            <select class="form-control" id="edit_status" name="status" required>
                <option value="active" {{ $projectDescription->status == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ $projectDescription->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="edit_entered_date" class="form-label">Entered Date</label>
            <input type="date" class="form-control" id="edit_entered_date" name="entered_date" value="{{ \Carbon\Carbon::parse($projectDescription->entered_date)->format('Y-m-d') }}" required>
        </div>
        <button type="button" class="btn btn-primary" id="updateProjectDescriptionBtn">Update Description</button>
    </form>
    @else
        <p>Error: Milestone data is not available.</p>
    @endisset
</div>
