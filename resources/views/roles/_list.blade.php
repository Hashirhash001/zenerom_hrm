<div class="row g-gs">
    @forelse ($roles as $role)
    <div class="col-sm-6 col-lg-4 col-xxl-3">
        <div class="card card-bordered h-100">
            <div class="card-inner">
                <div class="project">
                    <div class="project-head">
                        <a href="{{ route('role.show', $role->id) }}" class="project-title">
                            <div class="user-avatar sq bg-blue">
                                <span>{{ strtoupper(substr($role->name,0,1)) }}</span>
                            </div>
                            <div class="project-info">
                                <h6 class="title">{{ $role->name }}</h6>
                                <span class="sub-text">{{ $role->description }}</span>
                            </div>
                        </a>
                        <div class="dropdown">
                            <a class="dropdown-toggle btn btn-sm btn-icon btn-trigger mt-n1 me-n1" data-bs-toggle="dropdown">
                                <em class="icon ni ni-more-h"></em>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <ul class="link-list-opt no-bdr">
                                    <!-- <li>
                                        <a href="{{ route('role.show', $role->id) }}">
                                            <em class="icon ni ni-eye"></em><span>View Role</span>
                                        </a>
                                    </li> -->
                                    <li>
                                        <a class="editRoleBtn" data-id="{{ $role->id }}">
                                            <em class="icon ni ni-edit"></em><span>Edit Role</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="deleteRoleBtn" data-id="{{ $role->id }}">
                                            <em class="icon ni ni-trash"></em><span>Delete Role</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- Additional content can be added here -->
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-md-12">
        <div class="alert alert-info">No Roles Found!</div>
    </div>
    @endforelse
</div>
