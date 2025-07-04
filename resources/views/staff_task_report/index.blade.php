@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Staff Task Report</h1>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('staff-task.report') }}" class="row g-3 mb-4">
        <div class="col-md-3">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" name="start_date" id="start_date" 
                   value="{{ request('start_date', $startDate) }}" 
                   class="form-control">
        </div>
        <div class="col-md-3">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" name="end_date" id="end_date" 
                   value="{{ request('end_date', $endDate) }}" 
                   class="form-control">
        </div>
        <div class="col-md-3">
            <label for="staff_id" class="form-label">Staff Name</label>
            <select name="staff_id" id="staff_id" class="form-control">
                <option value="">All</option>
                @foreach($staffs as $staff)
                    <option value="{{ $staff->id }}" {{ request('staff_id') == $staff->id ? 'selected' : '' }}>
                        {{ $staff->first_name }} {{ $staff->middle_name }} {{ $staff->last_name }} ({{ $staff->employee_id }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 align-self-end">
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <a href="{{ route('staff-task.report') }}" class="btn btn-secondary">Clear</a>
        </div>
    </form>

    <!-- Staff Task Report Table -->
    <div class="table-responsive">
        <table class="table table-bordered datatable-init-export" data-export-title="Staff Task Report">
            <thead>
                <tr>
                    <th>Assignment Date</th>
                    <th>Task Title</th>
                    <th>Task Description</th>
                    <th>Deadline</th>
                    <th>Assignment Status</th>
                    <th>Document Name</th>
                    <th>Document Description</th>
                    <th>Staff (Employee ID)</th>
                    <th>Assigned By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($report->assignment_date)->format('d M Y') }}</td>
                        <td>{{ $report->task_title }}</td>
                        <td>{{ $report->task_description }}</td>
                        <td>
                            @if($report->task_deadline)
                                {{ \Carbon\Carbon::parse($report->task_deadline)->format('d M Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ ucfirst($report->assignment_status) }}</td>
                        <td>{{ $report->document_name ?? '-' }}</td>
                        <td>{{ $report->document_description ?? '-' }}</td>
                        <td>
                            {{ $report->staff_first_name }} {{ $report->staff_middle_name }} {{ $report->staff_last_name }}
                            ({{ $report->staff_employee_id }})
                        </td>
                        <td>
                            @if($report->assigned_by_first_name)
                                {{ $report->assigned_by_first_name }} {{ $report->assigned_by_middle_name }} {{ $report->assigned_by_last_name }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty

                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
