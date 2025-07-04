<div class="nk-block">
    <div class="card card-bordered card-stretch">
        <div class="card-inner-group">
            <div class="card-inner p-0">
                <div class="nk-tb-list nk-tb-ulist">
                    <div class="nk-tb-item nk-tb-head">
                        <div class="nk-tb-col"><span style="font-weight:bold;color: #455aba;">Service</span></div>
                        <div class="nk-tb-col"><span style="font-weight:bold;color: #455aba;">Department</span></div>
                        <div class="nk-tb-col"><span style="font-weight:bold;color: #455aba;">Status</span></div>
                        <div class="nk-tb-col"><span style="font-weight:bold;color: #455aba;">Created At</span></div>
                        <div class="nk-tb-col nk-tb-col-tools">
                            <span style="font-weight:bold;color: #455aba;">Actions</span>
                        </div>
                    </div>
                    @foreach($services as $service)
                    <div class="nk-tb-item">
                        <div class="nk-tb-col">
                            <div class="user-card">
                                <div class="user-info">
                                    <span class="tb-lead">{{ $service->name }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="nk-tb-col">
                            <span class="tb-amount">{{ $service->department ? $service->department->name : 'Not Assigned' }}</span>
                        </div>
                        <div class="nk-tb-col">
                            <span>{{ $service->status == 1 ? 'Active' : 'Inactive' }}</span>
                        </div>
                        <div class="nk-tb-col">
                            <span>{{ $service->created_at->format('d M Y') }}</span>
                        </div>
                        <div class="nk-tb-col nk-tb-col-tools">
                            <ul class="nk-tb-actions gx-1">
                                <li>
                                    <div class="drodown">
                                        <a  class="dropdown-toggle btn btn-icon btn-trigger" data-bs-toggle="dropdown">
                                            <em class="icon ni ni-more-h"></em>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <ul class="link-list-opt no-bdr">
                                               <!--  <li>
                                                    <a href="{{ route('service.show', $service->id) }}">
                                                        <em class="icon ni ni-eye"></em>
                                                        <span>View Details</span>
                                                    </a>
                                                </li> -->
                                                <li>
                                                    <a  class="editServiceBtn" data-id="{{ $service->id }}">
                                                        <em class="icon ni ni-edit"></em>
                                                        <span>Edit</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a  class="deleteServiceBtn" data-id="{{ $service->id }}">
                                                        <em class="icon ni ni-trash"></em>
                                                        <span>Delete</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    @endforeach
                </div><!-- nk-tb-list -->
            </div><!-- card-inner -->
        </div><!-- card-inner-group -->
    </div><!-- card -->
</div><!-- nk-block -->
