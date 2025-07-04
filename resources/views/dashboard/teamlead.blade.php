@extends('layouts.app')

@section('content')
<div class="nk-content">
  <div class="container-fluid">
    <div class="nk-content-inner">
      <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
          <div class="nk-block-between">
            <div class="nk-block-head-content">
              <h3 class="nk-block-title page-title">Team Lead Dashboard</h3>
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
            <!-- Department Staff Count -->
            <div class="col-sm-6 col-lg-6">
              <div class="card card-bordered">
                <div class="card-inner">
                  <div class="card-title-group align-start mb-2">
                    <div class="card-title">
                      <h6 class="title">Department Staff Count</h6>
                    </div>
                  </div>
                  <div class="nk-sale-data">
                    <span class="amount">{{ $department_staff_count }}</span>
                  </div>
                </div>
              </div>
            </div>
            <!-- Department Attendance Today -->
            <div class="col-sm-6 col-lg-6">
              <div class="card card-bordered">
                <div class="card-inner">
                  <div class="card-title-group align-start mb-2">
                    <div class="card-title">
                      <h6 class="title">Department Attendance Today</h6>
                    </div>
                  </div>
                  <div class="nk-sale-data">
                    <span class="amount">{{ $department_attendance_count }}</span>
                  </div>
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
