<div class="modal-header">
    <h5 class="modal-title" id="addDocumentModalLabel">Upload New Document</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="addDocumentForm">
        @csrf
        <!-- Hidden field: we'll update its value dynamically -->
        <input type="hidden" name="project_description_id" id="project_description_id" value="">
        
        <div class="mb-3">
            <label for="document_file" class="form-label">Select File</label>
            <input type="file" class="form-control" id="document_file" name="document_file" required>
        </div>
        <button type="button" class="btn btn-primary" id="saveDocumentBtn">Upload Document</button>
    </form>
</div>
