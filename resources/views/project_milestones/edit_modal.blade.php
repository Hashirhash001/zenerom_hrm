<div class="modal-header">
    <h5 class="modal-title" id="editMilestoneModalLabel">Edit Milestone</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    @isset($projectMilestone)
    <form id="editMilestoneForm">
        @csrf
        @method('PATCH')
        <!-- Hidden fields to identify the milestone and link to the project -->
        <input type="hidden" name="id" value="{{ $projectMilestone->id }}">
        <input type="hidden" name="project_id" value="{{ $projectMilestone->project_id }}">
        
        <!-- Dropdown for selecting the project service -->
        <div class="mb-3">
            <label for="edit_project_service_id" class="form-label">Service</label>
            <select class="form-control" id="edit_project_service_id" name="project_service_id" required>
                <option value="">Select Service</option>
                @foreach($project->projectServices as $ps)
                    <option value="{{ $ps->id }}" {{ $projectMilestone->project_service_id == $ps->id ? 'selected' : '' }}>
                        {{ $ps->service->name ?? 'N/A' }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div class="mb-3">
            <label for="edit_milestone_title" class="form-label">Title</label>
            <input type="text" class="form-control" id="edit_milestone_title" name="title" value="{{ $projectMilestone->title }}" required>
        </div>
        <div class="mb-3">
            <label for="edit_milestone_description" class="form-label">Description</label>
            <textarea class="form-control" id="edit_milestone_description" name="description">{{ $projectMilestone->description }}</textarea>
        </div>
        <div class="mb-3">
            <label for="edit_milestone_due_date" class="form-label">Due Date</label>
            <input type="date" class="form-control" id="edit_milestone_due_date" name="due_date" value="{{ $projectMilestone->due_date }}">
        </div>
        <div class="mb-3">
            <label for="edit_milestone_status" class="form-label">Status</label>
            <select class="form-control" id="edit_milestone_status" name="status" required>
                <option value="pending" {{ $projectMilestone->status == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="completed" {{ $projectMilestone->status == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="delayed" {{ $projectMilestone->status == 'delayed' ? 'selected' : '' }}>Delayed</option>
            </select>
        </div>
        <button type="button" class="btn btn-primary" id="updateMilestoneBtn">Update Milestone</button>
    </form>
    @else
        <p>Error: Milestone data is not available.</p>
    @endisset
</div>
