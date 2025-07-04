@extends('layouts.app')

@section('content')
<div class="nk-content">

<div class="container">
    <h4 class="mb-4">Projects in Your Department</h4>
    
    @if($projects->count())
    <div class="table-responsive">
        <table class="table table-bordered datatable-init-export" data-export-title="Department Projects">
            <thead>
                <tr>
                   <th>Project Name</th>
                    <th>Client</th>
                    <th>Assigned Staff</th>
                    <th>Status</th>
                    <th>Operations</th>
                </tr>
            </thead>
            <tbody>
                @foreach($projects as $project)
                <tr>
                    <td>{{ $project->name }}</td>
                    <td>{{ optional($project->customer)->name ?? 'N/A' }}</td>
                    <td>
                        {{ optional($project->assignedStaff)->first_name }} {{ optional($project->assignedStaff)->last_name }} 
                        ({{ optional($project->assignedStaff)->employee_id }})
                    </td>
                    <td>{{ ucfirst($project->status) }}</td>
                    <td>
                        <a href="{{ url('projects/'.$project->id) }}" class="btn btn-sm btn-primary">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
        <p>No projects found for your department.</p>
    @endif
</div>
</div>

@endsection
