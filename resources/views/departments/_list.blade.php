<div class="row g-gs">
    @forelse ($departments as $department)
        <div class="col-sm-6 col-lg-4 col-xxl-3">
            <div class="card card-bordered h-100">
                <div class="card-inner">
                    <div class="project">
                        <div class="project-head">
                            <a href="{{ route('department.show', $department->id) }}" class="project-title">
                                <div class="user-avatar sq bg-purple">
                                    @if($department->image)
                                        <img src="{{ asset('uploads/departments/' . $department->image) }}" alt="{{ $department->name }}" style="width:100%; height:100%; object-fit:cover;">
                                    @else
                                        <span>D</span>
                                    @endif
                                </div>
                                <div class="project-info">
                                    <h6 class="title">{{ $department->name }}</h6>
                                    <!-- <span class="sub-text">Akhil Alex</span> -->
                                </div>
                            </a>
                            <div class="dropdown">
                                <a href="#" class="dropdown-toggle btn btn-sm btn-icon btn-trigger mt-n1 me-n1" data-bs-toggle="dropdown">
                                    <em class="icon ni ni-more-h"></em>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <ul class="link-list-opt no-bdr">
                                        <li>
                                            <a href="{{ route('department.show', $department->id) }}">
                                                <em class="icon ni ni-eye"></em><span>View Department</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" onclick="editDepartment({{ $department->id }}); return false;">
                                                <em class="icon ni ni-edit"></em><span>Edit Department</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" onclick="deleteDepartment({{ $department->id }}); return false;">
                                                <em class="icon ni ni-trash"></em><span>Delete Department</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="project-details">
                            <p>{{ $department->description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-md-12">
            <div class="example-alert">
                <div class="alert alert-fill alert-primary alert-icon">
                    <em class="icon ni ni-alert-circle"></em>No <strong>Departments</strong> Found!
                </div>
            </div>
        </div>
    @endforelse
</div>
