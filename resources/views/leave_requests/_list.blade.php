<table class="table table-bordered w-full text-xs mb-0" id="leaveRequestsTable">
    <thead class="bg-gray-500">
        <tr>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="id">ID</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="user_id">Employee</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="leave_type">Leave Type</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="start_date">Start Date</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="end_date">End Date</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="duration">Duration</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="team_lead_status">Team Lead Status</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="hr_status">HR Status</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider" style="min-width: 240px; text-align: center;">Operations</th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @foreach($leaveRequests as $request)
            <tr id="leaveRequest_{{ $request->id }}" class="hover:bg-gray-50 transition duration-150" data-leave-request-id="{{ $request->id }}" data-user-id="{{ $request->user_id }}">
                <td class="px-4 py-2 whitespace-nowrap">{{ $request->id }}</td>
                <td class="px-4 py-2 whitespace-nowrap">
                    {{ $request->user ? ($request->user->name . (optional($request->user->employee)->employee_id ? ' (' . $request->user->employee->employee_id . ')' : '')) : 'N/A' }}
                </td>
                <td class="px-4 py-2 whitespace-nowrap">{{ formatLeaveType($request->leave_type) }}</td>
                <td class="px-4 py-2 whitespace-nowrap">{{ $request->start_date->format('d M Y') }}</td>
                <td class="px-4 py-2 whitespace-nowrap">{{ $request->end_date->format('d M Y') }}</td>
                <td class="px-4 py-2 whitespace-nowrap">{{ $request->duration ? $request->duration . ' days' : 'N/A' }}</td>
                <td class="px-4 py-2 whitespace-nowrap">
                    <span class="badge {{ $request->team_lead_status === 'Draft' ? 'badge-draft' : ($request->team_lead_status === 'Submitted' ? 'badge-pending' : ($request->team_lead_status === 'Approved' ? 'badge-completed' : ($request->team_lead_status === 'Rejected' ? 'badge-hold' : 'badge-no-tasks'))) }} px-2 py-1 rounded">
                        {{ $request->team_lead_status ?? 'Submitted' }}
                    </span>
                </td>
                <td class="px-4 py-2 whitespace-nowrap">
                    <span class="badge {{ $request->hr_status === 'Draft' ? 'badge-draft' : ($request->hr_status === 'Submitted' ? 'badge-pending' : ($request->hr_status === 'Approved' ? 'badge-completed' : ($request->hr_status === 'Rejected' ? 'badge-hold' : 'badge-no-tasks'))) }} px-2 py-1 rounded">
                        {{ $request->hr_status ?? 'Submitted' }}
                    </span>
                </td>
                <td class="px-4 py-2 whitespace-nowrap">
                    <div class="flex flex-wrap gap-1 justify-content-center">
                        <button class="btn btn-sm btn-secondary viewLeaveBtn" data-id="{{ $request->id }}" title="View Details"><i class="fas fa-eye"></i></button>
                        @if(Auth::user()->role_id === 3 && $request->team_lead_status === 'Submitted')
                            <button class="btn btn-sm btn-primary approveLeaveBtn" data-id="{{ $request->id }}" title="Approve"><i class="fas fa-check"></i></button>
                            <button class="btn btn-sm btn-danger rejectLeaveBtn" data-id="{{ $request->id }}" title="Reject"><i class="fas fa-times"></i></button>
                        @elseif(Auth::user()->role_id === 7)
                            <button class="btn btn-sm btn-primary approveLeaveBtn" data-id="{{ $request->id }}" title="Approve"><i class="fas fa-check"></i></button>
                            <button class="btn btn-sm btn-danger rejectLeaveBtn" data-id="{{ $request->id }}" title="Reject"><i class="fas fa-times"></i></button>
                        @endif
                        @if(Auth::user()->role_id === 1 || $request->user_id === Auth::user()->id)
                            <button class="btn btn-sm btn-danger deleteLeaveBtn" data-id="{{ $request->id }}" title="Delete"><i class="fas fa-trash"></i></button>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
