<div class="modal-header">
    <h5 class="modal-title" id="addMilestoneModalLabel">Add New Milestone</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="addMilestoneForm">
        @csrf
        <!-- Hidden field to link milestone to the project -->
        <input type="hidden" name="project_id" value="{{ $project->id }}">
        
        <!-- Dropdown to select the specific project service -->
        <div class="mb-3">
            <label for="project_service_id" class="form-label">Service</label>
            <select class="form-control" id="project_service_id" name="project_service_id" required>
                <option value="">Select Service</option>
                @foreach($project->projectServices as $ps)
                    <option value="{{ $ps->id }}">
                        {{ $ps->service->name ?? 'N/A' }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div class="mb-3">
            <label for="milestone_title" class="form-label">Title</label>
            <input type="text" class="form-control" id="milestone_title" name="title" placeholder="Enter milestone title" required>
        </div>
        <div class="mb-3">
            <label for="milestone_description" class="form-label">Description</label>
            <textarea class="form-control" id="milestone_description" name="description" placeholder="Enter description"></textarea>
        </div>
        <div class="mb-3">
            <label for="milestone_due_date" class="form-label">Due Date</label>
            <input type="date" class="form-control" id="milestone_due_date" name="due_date">
        </div>
        <div class="mb-3">
            <label for="milestone_status" class="form-label">Status</label>
            <select class="form-control" id="milestone_status" name="status" required>
                <option value="pending" selected>Pending</option>
                <option value="completed">Completed</option>
                <option value="delayed">Delayed</option>
            </select>
        </div>
        <button type="button" class="btn btn-primary" id="saveMilestoneBtn">Save Milestone</button>
    </form>
</div>
