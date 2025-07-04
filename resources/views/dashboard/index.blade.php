@extends('layouts.app')

@section('content')
<div class="nk-content">
  <div class="container-fluid">
    <div class="nk-content-inner">
      <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
          <div class="nk-block-between">
            <div class="nk-block-head-content">
              <h3 class="nk-block-title page-title">Admin Dashboard</h3>
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
            <!-- Global Counts -->
            <div class="col-sm-6 col-lg-3">
              <div class="card card-bordered">
                <div class="card-inner">
                  <div class="card-title-group align-start mb-2">
                    <div class="card-title">
                      <h6 class="title">Total Employees</h6>
                    </div>
                  </div>
                  <div class="nk-sale-data">
                    <span class="amount">{{ $total_employees }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-lg-3">
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
            </div>
            <div class="col-sm-6 col-lg-3">
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
            </div>
            <div class="col-sm-6 col-lg-3">
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
            </div>
            <!-- Attendance Cards -->
            <div class="col-sm-6 col-lg-4">
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
            </div>
            <div class="col-sm-6 col-lg-4">
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
            </div>
            <div class="col-sm-6 col-lg-4">
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
            </div>
          </div><!-- .row -->
        </div><!-- .nk-block -->
      </div>
    </div>
  </div>
</div>
@endsection
