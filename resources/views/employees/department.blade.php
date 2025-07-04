@extends('layouts.app')

@section('content')
<div class="nk-content ">

<div class="container">
    <h4>Employees in My Department</h4>
    <!-- You may add an optional filter for staff names if required -->
    <div class="table-responsive">
        <table class="table table-bordered datatable-init-export" data-export-title="Department Employees">
            <thead>
                <tr>
                    <th>Employee Name</th>
                    <th>Employee ID</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Email</th>
                    <th>Contact</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $employee)
                    <tr>
                        <td>{{ $employee->first_name }} {{ $employee->last_name }}</td>
                        <td>{{ $employee->employee_id }}</td>
                        <td>{{ optional($employee->department)->name }}</td>
                        <td>{{ optional($employee->role)->name }}</td>
                        <td>{{ $employee->email }}</td>
                        <td>{{ $employee->phone }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No employees found in your department.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>
@endsection
