<div class="modal-header">
    <h5 class="modal-title" id="editTaskModalLabel">Edit Task</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="editTaskForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="id" value="{{ $task->id }}">
        <!-- Project Selection -->
        <div class="mb-3">
            <label for="taskProjectEdit" class="form-label">Project</label>
            <select class="form-control" id="taskProjectEdit" name="project_id" required>
                <option value="">Select Project</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ $task->project_id == $project->id ? 'selected' : '' }}>
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <!-- Service Selection -->
        <div class="mb-3">
            <label for="taskServiceEdit" class="form-label">Service</label>
            <select class="form-control" id="taskServiceEdit" name="service_id">
                <option value="">Select Service</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}" {{ $task->service_id == $service->id ? 'selected' : '' }}>
                        {{ $service->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <!-- Title -->
        <div class="mb-3">
            <label for="taskTitleEdit" class="form-label">Title</label>
            <input type="text" class="form-control" id="taskTitleEdit" name="title" value="{{ $task->title }}" required>
        </div>
        <!-- Description -->
        <div class="mb-3">
            <label for="taskDescriptionEdit" class="form-label">Description</label>
            <textarea class="form-control" id="taskDescriptionEdit" name="description">{{ $task->description }}</textarea>
        </div>
        <!-- Deadline -->
        <div class="mb-3">
            <label for="taskDeadlineEdit" class="form-label">Deadline</label>
            <input type="date" class="form-control" id="taskDeadlineEdit" name="deadline" value="{{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('Y-m-d') : '' }}">
        </div>
        <!-- Status -->
        <div class="mb-3">
            <label for="taskStatusEdit" class="form-label">Status</label>
            <select class="form-control" id="taskStatusEdit" name="status">
                <option value="pending" {{ $task->status == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="hold" {{ $task->status == 'hold' ? 'selected' : '' }}>Hold</option>
            </select>
        </div>
        <button type="button" class="btn btn-primary" id="updateTaskBtn">Update Task</button>
    </form>
</div>
