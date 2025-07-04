<div class="modal-header">
    <h5 class="modal-title" id="uploadDocumentModalLabel">Upload Document</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="uploadDocumentForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="task_assigned_id" value="{{ $assignment->id }}">
        <input type="hidden" name="task_id" value="{{ $assignment->task_id }}">
        <div class="mb-3">
            <label for="documentName" class="form-label">Document Name</label>
            <input type="text" class="form-control" id="document_name" name="document_name" required>
        </div>
        <div class="mb-3">
            <label for="documentDescription" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description"></textarea>
        </div>
        <div class="mb-3">
            <label for="documentFile" class="form-label">Select Document</label>
            <input type="file" class="form-control" id="file_path" name="file_path" required>
        </div>
        <button type="button" class="btn btn-primary" id="uploadDocumentBtn">Upload Document</button>
    </form>
</div>
