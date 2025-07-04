@extends('layouts.app')

@section('content')
<div class="nk-content">
  <div class="container-fluid">
    <div class="nk-content-inner">
      <div class="nk-content-body">
        <!-- Header Section -->
        <div class="nk-block-head nk-block-head-sm">
          <div class="nk-block-between">
            <div class="nk-block-head-content">
              <h3 class="nk-block-title page-title">Tech Head Dashboard</h3>
              <div class="nk-block-des text-soft">
                <p>Welcome, {{ $uname }} (ID: {{ $uid }})</p>
              </div>
            </div>
            <div class="nk-block-head-content">
              <!-- Optional tools -->
            </div>
          </div>
        </div>
        
        <div class="nk-block">
          <div class="row g-gs">
          
            <!-- Total Projects Card -->
            <div class="col-sm-6 col-lg-4">
              <a href="{{ route('project.index') }}" class="text-decoration-none">
                <div class="card card-bordered">
                  <div class="card-inner">
                    <div class="card-title-group align-start mb-2">
                      <div class="card-title">
                        <h6 class="title">Total Projects</h6>
                      </div>
                    </div>
                    <div class="nk-sale-data">
                      <span class="amount">{{ $total_projects }}</span>
                    </div>
                  </div>
                </div>
              </a>
            </div>
            <!-- Total Services Card -->
            <div class="col-sm-6 col-lg-4">
              <a href="{{ route('service.index') }}" class="text-decoration-none">
                <div class="card card-bordered">
                  <div class="card-inner">
                    <div class="card-title-group align-start mb-2">
                      <div class="card-title">
                        <h6 class="title">Total Services</h6>
                      </div>
                    </div>
                    <div class="nk-sale-data">
                      <span class="amount">{{ $total_services }}</span>
                    </div>
                  </div>
                </div>
              </a>
            </div>
            <!-- Total Clients Card -->
            <div class="col-sm-6 col-lg-4">
              <a href="{{ route('customer.index') }}" class="text-decoration-none">
                <div class="card card-bordered">
                  <div class="card-inner">
                    <div class="card-title-group align-start mb-2">
                      <div class="card-title">
                        <h6 class="title">Total Clients</h6>
                      </div>
                    </div>
                    <div class="nk-sale-data">
                      <span class="amount">{{ $total_clients }}</span>
                    </div>
                  </div>
                </div>
              </a>
            </div>
            
            <!-- Attendance Cards -->
              <!-- Total Employees Card (Present / Total) -->
            <div class="col-sm-6 col-lg-3">
              <a href="{{ route('employee.index') }}" class="text-decoration-none">
                <div class="card card-bordered">
                  <div class="card-inner">
                    <div class="card-title-group align-start mb-2">
                      <div class="card-title">
                        <h6 class="title">Total Employees</h6>
                      </div>
                    </div>
                    <div class="nk-sale-data">
                      <span class="amount">{{ $attendance_total }} / {{ $total_employees }}</span>
                    </div>
                  </div>
                </div>
              </a>
            </div>
            <!-- Work From Office Card: Show employees with "Work from Home" mode attendance -->
            <div class="col-sm-6 col-lg-3">
              <a href="{{ route('attendance.workFromOffice') }}" class="text-decoration-none">
                <div class="card card-bordered">
                  <div class="card-inner">
                    <div class="card-title-group align-start mb-2">
                      <div class="card-title">
                        <h6 class="title">Work From Office</h6>
                      </div>
                    </div>
                    <div class="nk-sale-data">
                      <span class="amount">{{ $work_from_office }}</span>
                    </div>
                  </div>
                </div>
              </a>
            </div>
            <!-- Work From Home Card: Show employees with "Work from office" mode attendance -->
            <div class="col-sm-6 col-lg-3">
              <a href="{{ route('attendance.workFromHome') }}" class="text-decoration-none">
                <div class="card card-bordered">
                  <div class="card-inner">
                    <div class="card-title-group align-start mb-2">
                      <div class="card-title">
                        <h6 class="title">Work From Home</h6>
                      </div>
                    </div>
                    <div class="nk-sale-data">
                      <span class="amount">{{ $work_from_home }}</span>
                    </div>
                  </div>
                </div>
              </a>
            </div>
            <!-- Leave Count Card -->
            <div class="col-sm-6 col-lg-3">
              <a href="{{ route('attendance.leaveReport') }}" class="text-decoration-none">
                <div class="card card-bordered">
                  <div class="card-inner">
                    <div class="card-title-group align-start mb-2">
                      <div class="card-title">
                        <h6 class="title">Leave Count</h6>
                      </div>
                    </div>
                    <div class="nk-sale-data">
                      <span class="amount">{{ $leave_count }}</span>
                    </div>
                  </div>
                </div>
              </a>
            </div>
            
            <!-- New Cards -->
            <!-- Staff in Department Card -->
            <div class="col-sm-6 col-lg-3">
              <a href="{{ route('employees.department') }}" class="text-decoration-none">
                <div class="card card-bordered">
                  <div class="card-inner">
                    <div class="card-title-group align-start mb-2">
                      <div class="card-title">
                        <h6 class="title">Staff in Department</h6>
                      </div>
                    </div>
                    <div class="nk-sale-data">
                      <span class="amount">{{ $dept_attendance_total }} / {{ $staff_count_in_dept }}</span>
                    </div>
                  </div>
                </div>
              </a>
            </div>
            <!-- Today's My Tasks Card -->
            <div class="col-sm-6 col-lg-3">
              <a href="{{ route('my_tasks.report') }}" class="text-decoration-none">
                <div class="card card-bordered">
                  <div class="card-inner">
                    <div class="card-title-group align-start mb-2">
                      <div class="card-title">
                        <h6 class="title">Today's My Tasks</h6>
                      </div>
                    </div>
                    <div class="nk-sale-data">
                      <span class="amount">{{ $my_tasks_completed }} / {{ $my_tasks_total }}</span>
                    </div>
                  </div>
                </div>
              </a>
            </div>
            <!-- Today's Team Tasks Card -->
            <div class="col-sm-6 col-lg-3">
              <a href="{{ route('team_tasks.report') }}" class="text-decoration-none">
                <div class="card card-bordered">
                  <div class="card-inner">
                    <div class="card-title-group align-start mb-2">
                      <div class="card-title">
                        <h6 class="title">Today's Team Tasks</h6>
                      </div>
                    </div>
                    <div class="nk-sale-data">
                      <span class="amount">{{ $dept_tasks_completed }} / {{ $dept_tasks_total }}</span>
                    </div>
                  </div>
                </div>
              </a>
            </div>
            <!-- Projects in Department Card -->
            <div class="col-sm-6 col-lg-3">
              <a href="{{ route('projects.department') }}" class="text-decoration-none">
                <div class="card card-bordered">
                  <div class="card-inner">
                    <div class="card-title-group align-start mb-2">
                      <div class="card-title">
                        <h6 class="title">Projects in Dept.</h6>
                      </div>
                    </div>
                    <div class="nk-sale-data">
                      <span class="amount">{{ $dept_project_count }}</span>
                    </div>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-sm-12 col-lg-12">

            <div class="nk-block">
              <h6 class="nk-block-title">Active Notifications</h6>
              <div class="table-responsive">
                <table class="table table-bordered datatable-init-export" data-export-title="Active Notifications">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Title</th>
                      <th>Message</th>
                      <th>Created At</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($notifications as $notification)
                      <tr>
                        <td>{{ $notification->id }}</td>
                        <td>{{ $notification->title }}</td>
                        <td style="white-space: pre-line;">{{ $notification->message }}</td>
                        <td>{{ \Carbon\Carbon::parse($notification->created_at)->format('d M Y H:i') }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
            </div>

          </div><!-- .row -->
        </div><!-- .nk-block -->
      </div>
    </div>
  </div>
</div>
@endsection
