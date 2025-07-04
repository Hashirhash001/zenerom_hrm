<div class="nk-block">
    <div class="card card-bordered card-stretch">
        <div class="card-inner-group">
            <!-- Header Row -->
            <div class="card-inner p-0">
                <div class="nk-tb-list nk-tb-ulist">
                    <div class="nk-tb-item nk-tb-head">
                        <div class="nk-tb-col">
                            <span style="font-weight:bold;color: #455aba;">Employee</span>
                        </div>
                        <div class="nk-tb-col tb-col-mb">
                            <span style="font-weight:bold;color: #455aba;">Department</span>
                        </div>
                        <div class="nk-tb-col tb-col-md">
                            <span style="font-weight:bold;color: #455aba;">Role</span>
                        </div>
                        <div class="nk-tb-col tb-col-lg">
                            <span style="font-weight:bold;color: #455aba;">Contact</span>
                        </div>
                        <div class="nk-tb-col tb-col-lg">
                            <span style="font-weight:bold;color: #455aba;">Last Login</span>
                        </div>
                    </div><!-- .nk-tb-item -->
                    
                    @forelse ($employees as $employee)
                     @if($employee->status ==0)
                        <div class="nk-tb-item" style="background: #e0a8a0;">
                     @else
                        <div class="nk-tb-item">
                     @endif
                        <!-- Employee Info Column -->
                        <div class="nk-tb-col">
                            <a  class="viewEmployeeBtn" data-id="{{ $employee->id }}">
                                <div class="user-card">
                                    <div class="user-avatar bg-primary">
                                        @if($employee->image)
                                            <img src="{{ asset('uploads/employees/' . $employee->image) }}" alt="{{ $employee->first_name }}" style="width:100%; height:100%; object-fit:cover;">
                                        @else
                                            <span>{{ strtoupper(substr($employee->first_name,0,1)) }}</span>
                                        @endif
                                    </div>
                                    <div class="user-info">
                                        <span class="tb-lead">
                                            {{ $employee->first_name }} {{ $employee->middle_name }} {{ $employee->last_name }}
                                            <span class="dot dot-success d-md-none ms-1"></span>
                                        </span>
                                        <span>{{ $employee->employee_id }}</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <!-- Department Column -->
                        <div class="nk-tb-col tb-col-mb">
                            <span class="tb-amount">{{ $employee->department ? $employee->department->name : 'Not Assigned' }}</span>
                        </div>
                        <!-- Role Column -->
                        <div class="nk-tb-col tb-col-md">
                            <span>{{ $employee->role ? $employee->role->name : 'Not Assigned' }}</span>
                        </div>
                        <!-- Contact Column -->
                        <div class="nk-tb-col tb-col-lg">
                            <span>{{ $employee->company_email }}</span><br />
                            <span>{{ $employee->email }}</span><br />
                            <span>{{ $employee->phone }}</span><br />
                            <span>{{ $employee->whatsapp }}</span>
                        </div>
                        <!-- Last Login Column (static example) -->
                        <div class="nk-tb-col tb-col-lg">
                            <span>10 Feb 2020</span>
                        </div>
                        <!-- Tools Column -->
                        <div class="nk-tb-col nk-tb-col-tools">
                            <ul class="nk-tb-actions gx-1">
                                <li>
                                    <div class="drodown">
                                        <a  class="dropdown-toggle btn btn-icon btn-trigger" data-bs-toggle="dropdown">
                                            <em class="icon ni ni-more-h"></em>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <ul class="link-list-opt no-bdr">
                                                <!-- View Details (opens view modal via Ajax) -->
                                                <li>
                                                    <a  class="viewEmployeeBtn" data-id="{{ $employee->id }}">
                                                        <em class="icon ni ni-eye"></em>
                                                        <span>View Details</span>
                                                    </a>
                                                </li>
                                                <!-- Edit Employee (opens edit modal via Ajax) -->
                                                <li>
                                                    <a  class="editEmployeeBtn" data-id="{{ $employee->id }}">
                                                        <em class="icon ni ni-edit"></em>
                                                        <span>Edit</span>
                                                    </a>
                                                </li>
                                                <!-- Delete Employee -->
                                                <li>
                                                    <a  class="deleteEmployeeBtn" data-id="{{ $employee->id }}">
                                                        <em class="icon ni ni-trash"></em>
                                                        <span>Delete</span>
                                                    </a>
                                                </li>
                                                <li class="divider"></li>
                                                <!-- Toggle Activation: Show "Deactivate" if active, "Activate" if inactive -->
                                                <li>
                                                    <a  class="toggleEmployeeBtn" data-id="{{ $employee->id }}">
                                                        <em class="icon ni ni-user-remove"></em>
                                                        <span>
                                                            @if($employee->status == 1)
                                                                Deactivate User
                                                            @else
                                                                Activate User
                                                            @endif
                                                        </span>
                                                    </a>
                                                </li>
                                                <!-- Resign Employee (opens resign modal via Ajax) -->
                                                <li>
                                                    <a class="resignEmployeeBtn" data-id="{{ $employee->id }}">
                                                        <em class="icon ni ni-user-cross-fill"></em>
                                                        <span>Resign</span>
                                                    </a>
                                                </li>
                                                
                                                
                                            </ul>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div><!-- .nk-tb-item -->
                    @empty
                    <div class="col-md-12">
                        <div class="alert alert-info">No Employees Found!</div>
                    </div>
                    @endforelse
                </div><!-- .nk-tb-list -->
            </div><!-- .card-inner -->
        </div><!-- .card-inner-group -->
    </div><!-- .card -->
</div><!-- .nk-block -->
