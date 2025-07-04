<div class="nk-block">
    <div class="card card-bordered card-stretch">
        <div class="card-inner-group">
            <div class="card-inner p-0">
                <div class="nk-tb-list nk-tb-ulist">
                    <div class="nk-tb-item nk-tb-head">
                        <div class="nk-tb-col"><span style="font-weight:bold;color: #455aba;">Project</span></div>
                        <div class="nk-tb-col"><span style="font-weight:bold;color: #455aba;">Client</span></div>
                        <div class="nk-tb-col"><span style="font-weight:bold;color: #455aba;">Assigned Staff</span></div>
                        <div class="nk-tb-col"><span style="font-weight:bold;color: #455aba;">Status</span></div>
                        <div class="nk-tb-col"><span style="font-weight:bold;color: #455aba;">Payment Status</span></div>
                        <div class="nk-tb-col"><span style="font-weight:bold;color: #455aba;">Created At</span></div>
                        
                    </div>
                    @foreach($projects as $project)
                    <div class="nk-tb-item">
                        <!-- Project Name -->
                        <div class="nk-tb-col">
                            <div class="user-card">
                                <div class="user-info">
                                    <span class="tb-lead">{{ $project->name }}</span>
                                </div>
                            </div>
                        </div>
                        <!-- Client Name -->
                        <div class="nk-tb-col">
                            <span class="tb-amount">
                                {{ $project->customer ? $project->customer->name : 'Unknown Client' }}
                                
                            </span>
                        </div>
                     
                        <!-- Assigned Staff (Project Owner) -->
                        <div class="nk-tb-col">
                            <span>
                                @if($project->owner)
                                    {{ $project->owner->employee_id }} - {{ $project->owner->first_name }} {{ $project->owner->last_name }}
                                @else
                                    Not Assigned
                                @endif
                            </span>
                        </div>
                        <!-- Status -->
                        <div class="nk-tb-col">
                            <span>{{ ucfirst($project->status) }}</span>
                        </div>
                        <!-- Payment Status -->
                        <div class="nk-tb-col">
                            <span>{{ ucfirst($project->payment_status) }}</span>
                        </div>
                        <!-- Created At -->
                        <div class="nk-tb-col">
                            <span>{{ $project->created_at->format('d M Y') }}</span>
                        </div>
                        <!-- Actions -->
                        <div class="nk-tb-col nk-tb-col-tools">
                            <ul class="nk-tb-actions gx-1">
                                <li>
                                    <div class="drodown">
                                        <a href="#" class="dropdown-toggle btn btn-icon btn-trigger" data-bs-toggle="dropdown">
                                            <em class="icon ni ni-more-h"></em>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <ul class="link-list-opt no-bdr">
                                                <li>
                                                    <a href="{{ route('project.show', $project->id) }}">
                                                        <em class="icon ni ni-eye"></em>
                                                        <span>View Details</span>
                                                    </a>
                                                </li>

                                                <li>
                                                    <a href="#" class="editProjectBtn" data-id="{{ $project->id }}">
                                                        <em class="icon ni ni-edit"></em>
                                                        <span>Edit</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" class="deleteProjectBtn" data-id="{{ $project->id }}">
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
