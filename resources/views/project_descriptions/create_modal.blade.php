<div class="modal-header">
    <h5 class="modal-title" id="addProjectDescriptionModalLabel">Add New Project Description</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="addProjectDescriptionForm" enctype="multipart/form-data">
        @csrf
        <!-- Hidden field to link the description to the project -->
        <input type="hidden" name="project_id" value="{{ $project->id }}">
        
        <!-- Instead of "Entry Identifier", list the project services -->
        <div class="mb-3">
            <label for="project_service_id" class="form-label">Project Service</label>
            <select class="form-control" id="project_service_id" name="project_service_id" required>
                <option value="">Select Project Service</option>
                @foreach($project->projectServices as $ps)
                    <option value="{{ $ps->id }}">{{ $ps->service->name ?? 'Service' }}</option>
                @endforeach
            </select>
        </div>
        
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" id="title" name="title" placeholder="Enter title" required>
        </div>
        <div class="mb-3">
            <label for="details" class="form-label">Details</label>
            <textarea class="form-control summernote" id="details" name="details" placeholder="Enter description"></textarea>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-control" id="status" name="status" required>
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="entered_date" class="form-label">Entered Date</label>
            <input type="date" class="form-control" id="entered_date" name="entered_date">
        </div>
        <div class="mb-3">
            <label for="files" class="form-label">Upload Files</label>
            <input type="file" class="form-control" id="files" name="files[]" multiple>
        </div>
        <button type="button" class="btn btn-primary" id="saveProjectDescriptionBtn">Save Description</button>
    </form>
</div>