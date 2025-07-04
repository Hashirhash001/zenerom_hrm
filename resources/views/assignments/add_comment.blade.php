<div class="modal-header">
    <h5 class="modal-title" id="addCommentModalLabel">Add Comment</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="addCommentForm">
        @csrf
        <input type="hidden" name="assignment_id" value="{{ $assignment->id }}">
        <input type="hidden" name="task_id" value="{{ $assignment->task_id }}">
        
        <div class="mb-3">
            <label for="commentText" class="form-label">Comment</label>
            <textarea class="form-control" id="commentText" name="comment" required></textarea>
        </div>
        <button type="button" class="btn btn-primary" id="addCommentBtn">Add Comment</button>
    </form>
</div>
