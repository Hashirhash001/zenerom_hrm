@extends('layouts.app')

@section('content')
<div class="container">
    <h1>All Notifications</h1>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('notifications.all') }}" class="row g-3 mb-4">
        <div class="col-md-3">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" name="start_date" id="start_date" 
                   value="{{ request('start_date', \Carbon\Carbon::today()->toDateString()) }}" 
                   class="form-control">
        </div>
        <div class="col-md-3">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" name="end_date" id="end_date" 
                   value="{{ request('end_date', \Carbon\Carbon::today()->toDateString()) }}" 
                   class="form-control">
        </div>
        <div class="col-md-3">
            <label for="project_id" class="form-label">Project</label>
            <select name="project_id" id="project_id" class="form-control">
                <option value="">All</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" 
                        {{ request('project_id') == $project->id ? 'selected' : '' }}>
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="service_id" class="form-label">Service</label>
            <select name="service_id" id="service_id" class="form-control">
                <option value="">All</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}" 
                        {{ request('service_id') == $service->id ? 'selected' : '' }}>
                        {{ $service->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <a href="{{ route('notifications.all') }}" class="btn btn-secondary">Clear</a>
        </div>
    </form>

    <!-- Notifications Table -->
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Project</th>
                    <th>Service</th>
                    <th>Title</th>
                    <th>Message</th>
                    <th>Read At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notifications as $notification)
                   @php
                    // Example notification message; in real use, replace this with your actual notification message.
                    $message = $notification->message; 
                    // $message might be: "test data driven (Circuitil - Mobile App Development | Web designing )"
                    
                    $projectName = '';
                    $serviceName = '';

                    // Use regex to get the text within parentheses.
                    if (preg_match('/\((.*?)\)/', $message, $matches)) {
                        // $matches[1] contains the content inside the parentheses.
                        $inner = $matches[1];
                        // Split the inner text by the pipe character.
                        $parts = explode('|', $inner);
                        // Trim both parts to remove extra spaces.
                        $projectName = trim($parts[0] ?? '');
                        $serviceName = trim($parts[1] ?? '');
                    }
                @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($notification->created_at)->format('d M Y H:i') }}</td>
                        <td>{{ $projectName }}</td>
                        <td>{{ $serviceName }}</td>
                        <td>{{ $notification->title }}</td>
                        <td style="white-space: pre-line;">{{ $notification->message }}</td>
                        <td>
                            @if($notification->read_at)
                                {{ \Carbon\Carbon::parse($notification->read_at)->format('d M Y H:i') }}
                            @else
                                Unread
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No notifications found for the selected criteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
