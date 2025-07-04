<table class="table table-bordered table-striped datatable-init-export table" data-export-title="Export">
    <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Description</th>
            <th>Deadline</th>
            <th>Status</th>
            <th>Project</th>
            <th>Service</th>
            <th>Created By</th>
            <th>Created Date</th>
            <th>Description</th>
            <th>Assigned Staff</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @if($tasks->count())
            @foreach($tasks as $task)
                <tr id="taskRow-{{ $task->id }}">
                    <td>{{ $task->id }}</td>
                    <td>{{ $task->title }}</td>
                    <td>{{ $task->description }}</td>
                    <td>{{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('d M Y') : 'N/A' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $task->status)) }}</td>
                    <td>{{ optional($task->project)->name }}</td>
                    <td>{{ optional($task->service)->name }}</td>
                    <td>{{ optional($task->creator)->first_name }} {{ optional($task->creator)->middle_name }} {{ optional($task->creator)->last_name }}</td>
                    <td>{{ $task->created_at }}</td>
                    <td>{{ $task->description }}</td>


                    <td>
                        @if($task->assignments->count())
                            @php
                                // Group assignments by staff_id to avoid duplicates.
                                $grouped = $task->assignments->groupBy('staff_id');
                            @endphp
                            <ul class="list-unstyled mb-0">
                                @foreach($grouped as $staffId => $assignments)
                                    <li>
                                        {{ optional($assignments->first()->staff)->first_name }} {{ optional($assignments->first()->staff)->last_name }}
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <span>No assignments</span>
                        @endif
                    </td>
                    <td>
                        <button class="btn btn-warning btn-sm editTaskBtn" data-id="{{ $task->id }}">Edit</button>
                        <button class="btn btn-danger btn-sm deleteTaskBtn" data-id="{{ $task->id }}">Delete</button>
                        <button class="btn btn-info btn-sm assignStaffBtn" data-id="{{ $task->id }}">Assign Staff</button>
                        <button class="btn btn-secondary btn-sm" onclick="window.location.href='{{ route('tasks.details', $task->id) }}'">
                            View Details
                        </button>
                    </td>
                </tr>
            @endforeach
        @else

        @endif
    </tbody>
</table>

<script src="{{ asset('assets1/jquery.min.js') }}"></script>
