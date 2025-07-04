@extends('layouts.app')

@section('content')
<div class="nk-content">
  <div class="container-fluid">
    <div class="nk-content-inner">
      <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
          <div class="nk-block-between">
            <div class="nk-block-head-content">
              <h3 class="nk-block-title page-title">Intern Dashboard</h3>
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
          <div class="row">
            <div class="col-12">
              <div class="card card-bordered">
                <div class="card-inner">
                  <div class="card-title-group">
                    <div class="card-title">
                      <h5 class="title">Intern Dashboard</h5>
                    </div>
                  </div>
                  <p>This dashboard is for interns. No aggregate data is available.</p>
                </div>
              </div>
            </div>
          </div>
        </div><!-- .nk-block -->
      </div>
    </div>
  </div>
</div>
@endsection
