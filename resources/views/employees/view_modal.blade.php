<div class="modal-header">
    <h5 class="modal-title">Employee Details</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
<div class="card card-bordered">
    <div class="card-inner-group">
        <div class="card-inner">
            <div class="user-card user-card-s2">
                <div class="user-avatar lg bg-primary">
                    @if($employee->image)
                        <img src="{{ asset('uploads/employees/' . $employee->image) }}" alt="{{ $employee->first_name }}" style="object-fit:cover;border-radius:50%;">
                    @else
                        <div class="avatar avatar-lg bg-info text-white">{{ strtoupper(substr($employee->first_name,0,1)) }}</div>
                    @endif
                </div>
                <div class="user-info">
                    <div class="badge bg-light rounded-pill ucap">{{ $employee->employee_id }}</div>
                    <h5>{{ $employee->first_name }} {{ $employee->middle_name }} {{ $employee->last_name }}</h5>
                    <span class="sub-text">{{ $employee->company_email }}</span>
                </div>
            </div>
        </div>
       <!--  <div class="card-inner card-inner-sm">
            <ul class="btn-toolbar justify-center gx-1">
                <li><a href="#" class="btn btn-trigger btn-icon"><em class="icon ni ni-shield-off"></em></a></li>
                <li><a href="#" class="btn btn-trigger btn-icon"><em class="icon ni ni-mail"></em></a></li>
                <li><a href="#" class="btn btn-trigger btn-icon"><em class="icon ni ni-bookmark"></em></a></li>
                <li><a href="#" class="btn btn-trigger btn-icon text-danger"><em class="icon ni ni-na"></em></a></li>
            </ul>
        </div> -->
                
        <div class="card-inner row">
           
           <div class="card card-bordered card-preview">
                                            <div class="card-inner">
                                                <ul class="nav nav-tabs mt-n3" role="tablist">
                                                    <li class="nav-item" role="presentation">
                                                        <a class="nav-link active" data-bs-toggle="tab" href="#tabItem5" aria-selected="false" role="tab" tabindex="-1"><em class="icon ni ni-user"></em><span>Personal Information</span></a>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <a class="nav-link" data-bs-toggle="tab" href="#tabItem6" aria-selected="false" role="tab" tabindex="-1"><em class="icon ni ni-call"></em><span>Contacts</span></a>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <a class="nav-link" data-bs-toggle="tab" href="#tabItem7" aria-selected="false" role="tab" tabindex="-1"><em class="icon ni ni-lock-alt"></em><span>Account</span></a>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <a class="nav-link" data-bs-toggle="tab" href="#tabItem8" aria-selected="true" role="tab"><em class="icon ni ni-link"></em><span>Company Records</span></a>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <a class="nav-link" data-bs-toggle="tab" href="#tabItem9" aria-selected="true" role="tab"><em class="icon ni ni-list"></em><span>Access Control</span></a>
                                                    </li>
                                                </ul>
                                                <div class="tab-content">
                                                    <div class="tab-pane active show" id="tabItem5" role="tabpanel">
                                                        <div class="nk-data data-list">
                                                        
                                                        <div class="data-item" >
                                                            <div class="data-col">
                                                                <span class="data-label">Full Name</span>
                                                                <span class="data-value">{{ $employee->first_name }} {{ $employee->middle_name }} {{ $employee->last_name }}</span>
                                                            </div>
                                                        </div><!-- data-item -->
                                                        <div class="data-item" >
                                                            <div class="data-col">
                                                                <span class="data-label">Age | DOB</span>
                                                                <span class="data-value">{{ $employee->age }} | {{ $employee->dob }}</span>
                                                            </div>
                                                        </div><!-- data-item -->
                                                        <div class="data-item">
                                                            <div class="data-col">
                                                                <span class="data-label">Gender</span>
                                                                <span class="data-value">{{ $employee->gender }}</span>
                                                            </div>
                                                        </div><!-- data-item -->
                                                        <div class="data-item">
                                                            <div class="data-col">
                                                                <span class="data-label">Blood Group</span>
                                                                <span class="data-value">{{ $employee->blood_group }}</span>
                                                            </div>
                                                        </div><!-- data-item -->
                                                        <div class="data-item" >
                                                            <div class="data-col">
                                                                <span class="data-label">Department</span>
                                                                <span class="data-value text-soft">{{ $employee->department ? $employee->department->name : 'Not Assigned' }}</span>
                                                            </div>

                                                        </div><!-- data-item -->
                                                        <div class="data-item" >
                                                            <div class="data-col">
                                                                <span class="data-label">Role</span>
                                                                <span class="data-value">{{ $employee->role ? $employee->role->name : 'Not Assigned' }}</span>
                                                            </div>

                                                        </div><!-- data-item -->
                                                        
                                                    </div>
                                                    </div>
                                                    <div class="tab-pane" id="tabItem6" role="tabpanel">
                                                        <div class="nk-data data-list">
                                                        
                                                        <div class="data-item" >
                                                            <div class="data-col">
                                                                <span class="data-label">Personal Email</span>
                                                                <span class="data-value">{{ $employee->email }}</span>
                                                            </div>
                                                        </div><!-- data-item -->
                                                        <div class="data-item" >
                                                            <div class="data-col">
                                                                <span class="data-label">Official Email</span>
                                                                <span class="data-value">{{ $employee->company_email }}</span>
                                                            </div>
                                                        </div><!-- data-item -->
                                                        <div class="data-item">
                                                            <div class="data-col">
                                                                <span class="data-label">Phone</span>
                                                                <span class="data-value">{{ $employee->phone }}</span>
                                                            </div>
                                                        </div><!-- data-item -->
                                                        <div class="data-item" >
                                                            <div class="data-col">
                                                                <span class="data-label">WhatsApp</span>
                                                                <span class="data-value text-soft">{{ $employee->whatsapp }}</span>
                                                            </div>

                                                        </div><!-- data-item -->
                                                        <div class="data-item" >
                                                            <div class="data-col">
                                                                <span class="data-label">Emergency Conatct</span>
                                                                <span class="data-value">{{ $employee->emergency_contact_name }} | {{ $employee->emergency_contact }}</span>
                                                            </div>
                                                        </div><!-- data-item -->
                                                        <div class="data-item" >
                                                            <div class="data-col">
                                                                <span class="data-label">Permanent Address</span>
                                                                <span class="data-value">{{ $employee->permanent_address }} </span>
                                                            </div>
                                                        </div><!-- data-item -->
                                                        <div class="data-item" >
                                                            <div class="data-col">
                                                                <span class="data-label">Local Address</span>
                                                                <span class="data-value">{{ $employee->local_address }}</span>
                                                            </div>
                                                        </div><!-- data-item -->
                                                        
                                                    </div>
                                                    </div>
                                                    <div class="tab-pane" id="tabItem7" role="tabpanel">
                                                        <form id="accountForm">
                                                            @csrf
                                                            <!-- Hidden employee id -->
                                                            <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                                                            <div class="mb-3">
                                                                <label for="account_name" class="form-label">Account Name</label>
                                                                <input type="text" class="form-control" id="account_name" name="name"
                                                                       value="{{ $employee->user ? $employee->user->name : ($employee->first_name . ' ' . $employee->last_name) }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="account_email" class="form-label">Account Email</label>
                                                                <input type="email" class="form-control" id="account_email" name="email"
                                                                       value="{{ $employee->user ? $employee->user->email : $employee->company_email }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="account_password" class="form-label">Password</label>
                                                                <input type="password" class="form-control" id="account_password" name="password" minlength="6" placeholder="Leave blank if unchanged">
                                                                <small class="form-text text-muted">Password must be at least 6 characters long.</small>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="account_role" class="form-label">Role</label>
                                                                <select class="form-control" id="account_role" name="role_id" required>
                                                                    <option value="">Select Role</option>
                                                                    @foreach($roles as $role)
                                                                        <option value="{{ $role->id }}"
                                                                            @if(isset($employee->user) && $employee->user->role_id == $role->id) selected @endif>
                                                                            {{ $role->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="account_department" class="form-label">Department</label>
                                                                <select class="form-control" id="account_department" name="department_id">
                                                                    <option value="">Select Department</option>
                                                                    @foreach($departments as $dept)
                                                                        <option value="{{ $dept->id }}"
                                                                            @if(isset($employee->user) && $employee->user->department_id == $dept->id) selected @endif>
                                                                            {{ $dept->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <button type="button" class="btn btn-primary" id="saveAccountBtn">Save Account</button>
                                                        </form>
                                                    </div>
                                                    <div class="tab-pane" id="tabItem8" role="tabpanel">
                                                         <div class="nk-data data-list">
                                                        
                                                        <div class="data-item" >
                                                            <div class="data-col">
                                                                <span class="data-label">Joining Date</span>
                                                                <span class="data-value">{{ $employee->resignation }}</span>
                                                            </div>
                                                        </div><!-- data-item -->
                                                        <div class="data-item" >
                                                            <div class="data-col">
                                                                <span class="data-label">Resignation Date</span>
                                                                <span class="data-value">{{ $employee->resignation }}</span>
                                                            </div>
                                                        </div><!-- data-item -->
                                                        <div class="data-item">
                                                            <div class="data-col">
                                                                <span class="data-label">Resignation Notes</span>
                                                                <span class="data-value">{{ $employee->resignation_details }}</span>
                                                            </div>
                                                        </div><!-- data-item -->
                                                        <div class="data-item" >
                                                            <div class="data-col">
                                                                <span class="data-label">CV File</span>
                                                                <span class="data-value text-soft"><a href="{{ asset('uploads/employees/' . $employee->cv_file) }}" download>Download CV</a></span>
                                                            </div>

                                                        </div><!-- data-item -->
                                                        
                                                        
                                                    </div>
                                                    </div>

                                                    <div class="tab-pane" id="tabItem9" role="tabpanel">
                                                         <div class="nk-data data-list">
                                                        <input type="hidden" id="employee_id1234" value="{{ $employee->id }}">
                                                          <form id="accessControlForm">
                                                            @csrf
                                                            <!-- Hidden field for user ID -->
                                                            <!-- <input type="text" name="user_id" id="user_id" value="{{ $employee->id }}"> -->


                                                            <table class="table">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Menu Item</th>
                                                                        <th>View</th>
                                                                        <th>Edit</th>
                                                                        <th>Delete</th>
                                                                        <th>Create</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($menuItems->where('parent_id', null) as $parent)
                                                                        <!-- Parent row -->
                                                                        @php
                                                                            $parentAccess = $userAccess->firstWhere('menu_item_id', $parent->id);
                                                                        @endphp
                                                                        <tr style="background: #f5f5f5; font-weight:bold;">
                                                                            <td style="color: #4860ce;">{{ $parent->display_name }}</td>
                                                                            <td>
                                                                                <input type="checkbox" class="parent-view" data-parent-id="{{ $parent->id }}"
                                                                                    name="privileges[{{ $parent->id }}][can_view]" value="1"
                                                                                    {{ $parentAccess && $parentAccess->can_view ? 'checked' : '' }}>
                                                                            </td>
                                                                            <td></td>
                                                                            <td></td>
                                                                            <td></td>
                                                                        </tr>
                                                                        <!-- Child items -->
                                                                        @foreach($menuItems->where('parent_id', $parent->id) as $child)
                                                                            @php
                                                                                $childAccess = $userAccess->firstWhere('menu_item_id', $child->id);
                                                                                $disable = ($parentAccess && $parentAccess->can_view) ? '' : 'disabled';
                                                                            @endphp
                                                                            <tr>
                                                                                <td style="padding-left: 20px;">{{ $child->display_name }}</td>
                                                                                <td>
                                                                                    <input type="checkbox" class="child-checkbox child-{{ $parent->id }}" name="privileges[{{ $child->id }}][can_view]" value="1"
                                                                                        {{ $childAccess && $childAccess->can_view ? 'checked' : '' }} {{ $disable }}>
                                                                                </td>
                                                                                <td>
                                                                                    <input type="checkbox" class="child-checkbox child-{{ $parent->id }}" name="privileges[{{ $child->id }}][can_edit]" value="1"
                                                                                        {{ $childAccess && $childAccess->can_edit ? 'checked' : '' }} {{ $disable }}>
                                                                                </td>
                                                                                <td>
                                                                                    <input type="checkbox" class="child-checkbox child-{{ $parent->id }}" name="privileges[{{ $child->id }}][can_delete]" value="1"
                                                                                        {{ $childAccess && $childAccess->can_delete ? 'checked' : '' }} {{ $disable }}>
                                                                                </td>
                                                                                <td>
                                                                                    <input type="checkbox" class="child-checkbox child-{{ $parent->id }}" name="privileges[{{ $child->id }}][can_create]" value="1"
                                                                                        {{ $childAccess && $childAccess->can_create ? 'checked' : '' }} {{ $disable }}>
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                            <hr />
                                                            <button type="button" class="btn btn-primary" id="saveAccessControlBtn">Save Access Control</button>
                                                        </form>

                                                        
                                                        
                                                    </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>


        </div><!-- .card-inner -->


        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>



                                            