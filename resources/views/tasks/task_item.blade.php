<div class="col-md-6" id="taskRow-{{ $task->id }}">
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">{{ $task->title }}</h5>
            <p class="card-text">{{ $task->description }}</p>
            <p class="card-text">
                <small>Deadline: {{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('d M Y') : 'N/A' }}</small>
            </p>
            <p class="card-text">
                <small>Status: {{ ucfirst(str_replace('_', ' ', $task->status)) }}</small>
            </p>
            <button class="btn btn-warning editTaskBtn" data-id="{{ $task->id }}">Edit</button>
            <button class="btn btn-danger deleteTaskBtn" data-id="{{ $task->id }}">Delete</button>
        </div>
    </div>
</div>
