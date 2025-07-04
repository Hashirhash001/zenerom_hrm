<div class="nk-block">
    <div class="card card-bordered card-stretch">
        <div class="card-inner-group">
            <div class="card-inner p-0">
                <div class="nk-tb-list nk-tb-ulist">
                    <div class="nk-tb-item nk-tb-head">
                        <div class="nk-tb-col"><span style="font-weight:bold;color: #455aba;">Customer</span></div>
                        <div class="nk-tb-col"><span style="font-weight:bold;color: #455aba;">Contact Info</span></div>
                        <div class="nk-tb-col"><span style="font-weight:bold;color: #455aba;">Created At</span></div>
                        <div class="nk-tb-col nk-tb-col-tools">
                            <span style="font-weight:bold;color: #455aba;">Actions</span>
                        </div>
                    </div>
                    @foreach($customers as $customer)
                    <div class="nk-tb-item">
                        <div class="nk-tb-col">
                            <div class="user-card">
                                <div class="user-info">
                                    <span class="tb-lead">{{ $customer->name }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="nk-tb-col">
                            <span class="tb-amount">{{ $customer->contact_info }}</span>
                        </div>
                        <div class="nk-tb-col">
                            <span>{{ $customer->created_at->format('d M Y') }}</span>
                        </div>
                        <div class="nk-tb-col nk-tb-col-tools">
                            <ul class="nk-tb-actions gx-1">
                                <li>
                                    <div class="dropdown">
                                        <a class="dropdown-toggle btn btn-icon btn-trigger" data-bs-toggle="dropdown">
                                            <em class="icon ni ni-more-h"></em>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <ul class="link-list-opt no-bdr">
                                                <!-- View Details -->
                                                <li>
                                                    @if(session('user_privileges')->has(11) && session('user_privileges')->get(11)->can_view)
                                                    <a class="viewCustomerBtn" data-id="{{ $customer->id }}">
                                                        <em class="icon ni ni-eye"></em>
                                                        <span>View Details</span>
                                                    </a>
                                                    @else
                                                    <a href="#" class="disabled" onclick="return false;" title="Not Authorized">
                                                        <em class="icon ni ni-eye"></em>
                                                        <span>View Details</span>
                                                    </a>
                                                    @endif
                                                </li>
                                                <!-- Edit -->
                                                <li>
                                                    @if(session('user_privileges')->has(11) && session('user_privileges')->get(11)->can_edit)
                                                    <a class="editCustomerBtn" data-id="{{ $customer->id }}">
                                                        <em class="icon ni ni-edit"></em>
                                                        <span>Edit</span>
                                                    </a>
                                                    @else
                                                    <a href="#" class="disabled" onclick="return false;" title="Not Authorized">
                                                        <em class="icon ni ni-edit"></em>
                                                        <span>Edit</span>
                                                    </a>
                                                    @endif
                                                </li>
                                                <!-- Delete -->
                                                <li>
                                                    @if(session('user_privileges')->has(11) && session('user_privileges')->get(11)->can_delete)
                                                    <a class="deleteCustomerBtn" data-id="{{ $customer->id }}">
                                                        <em class="icon ni ni-trash"></em>
                                                        <span>Delete</span>
                                                    </a>
                                                    @else
                                                    <a href="#" class="disabled" onclick="return false;" title="Not Authorized">
                                                        <em class="icon ni ni-trash"></em>
                                                        <span>Delete</span>
                                                    </a>
                                                    @endif
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
