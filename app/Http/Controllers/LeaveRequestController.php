<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeaveRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $leaveRequests = [];
        $employees = [];

        if (in_array($user->role_id, [1, 2, 7])) {
            // Admins (role_id 1), Tech Heads (role_id 2), and HR (role_id 7) can view all leave requests
            $leaveRequests = LeaveRequest::with(['user', 'teamLeadApprover', 'hrApprover'])
                ->when(request('start_date'), fn($q) => $q->where('start_date', '>=', request('start_date')))
                ->when(request('end_date'), fn($q) => $q->where('end_date', '<=', request('end_date')))
                ->when(request('status'), function ($q, $status) {
                    if ($status === 'Approved') {
                        $q->where('team_lead_status', 'Approved')->where('hr_status', 'Approved');
                    } elseif ($status === 'Rejected') {
                        $q->where(function ($q) {
                            $q->where('team_lead_status', 'Rejected')->orWhere('hr_status', 'Rejected');
                        });
                    } elseif ($status === 'Submitted') {
                        $q->where(function ($q) {
                            $q->where('team_lead_status', 'Submitted')->orWhere('hr_status', 'Submitted');
                        });
                    } elseif ($status === 'Draft') {
                        $q->where('team_lead_status', 'Draft')->where('hr_status', 'Draft');
                    }
                })
                ->when(request('employee_id'), fn($q) => $q->where('user_id', request('employee_id')))
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            $employees = \App\Models\User::whereIn('role_id', [1, 2, 3, 4, 5, 6, 7])
                ->with('employee')
                ->get();
        } elseif ($user->role_id === 3) {
            // Team Leads can only see leave requests from their department
            $departmentId = optional($user->employee)->department_id;
            if ($departmentId) {
                $leaveRequests = LeaveRequest::with(['user', 'teamLeadApprover', 'hrApprover'])
                    ->whereHas('user.employee', function ($q) use ($departmentId) {
                        $q->where('department_id', $departmentId);
                    })
                    ->when(request('start_date'), fn($q) => $q->where('start_date', '>=', request('start_date')))
                    ->when(request('end_date'), fn($q) => $q->where('end_date', '<=', request('end_date')))
                    ->when(request('status'), function ($q, $status) {
                        if ($status === 'Approved') {
                            $q->where('team_lead_status', 'Approved')->where('hr_status', 'Approved');
                        } elseif ($status === 'Rejected') {
                            $q->where(function ($q) {
                                $q->where('team_lead_status', 'Rejected')->orWhere('hr_status', 'Rejected');
                            });
                        } elseif ($status === 'Submitted') {
                            $q->where(function ($q) {
                                $q->where('team_lead_status', 'Submitted')->orWhere('hr_status', 'Submitted');
                            });
                        } elseif ($status === 'Draft') {
                            $q->where('team_lead_status', 'Draft')->where('hr_status', 'Draft');
                        }
                    })
                    ->when(request('employee_id'), fn($q) => $q->where('user_id', request('employee_id')))
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
                $employees = \App\Models\User::whereIn('role_id', [1, 2, 3, 4, 5, 6, 7])
                    ->whereHas('employee', function ($q) use ($departmentId) {
                        $q->where('department_id', $departmentId);
                    })
                    ->with('employee')
                    ->get();
            } else {
                $leaveRequests = collect()->paginate(10); // Empty paginated collection
                $employees = collect();
            }
        } else {
            // Other users can only view their own leave requests
            $leaveRequests = LeaveRequest::with(['user', 'teamLeadApprover', 'hrApprover'])
                ->where('user_id', $user->id)
                ->when(request('start_date'), fn($q) => $q->where('start_date', '>=', request('start_date')))
                ->when(request('end_date'), fn($q) => $q->where('end_date', '<=', request('end_date')))
                ->when(request('status'), function ($q, $status) {
                    if ($status === 'Approved') {
                        $q->where('team_lead_status', 'Approved')->where('hr_status', 'Approved');
                    } elseif ($status === 'Rejected') {
                        $q->where(function ($q) {
                            $q->where('team_lead_status', 'Rejected')->orWhere('hr_status', 'Rejected');
                        });
                    } elseif ($status === 'Submitted') {
                        $q->where(function ($q) {
                            $q->where('team_lead_status', 'Submitted')->orWhere('hr_status', 'Submitted');
                        });
                    } elseif ($status === 'Draft') {
                        $q->where('team_lead_status', 'Draft')->where('hr_status', 'Draft');
                    }
                })
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            $employees = collect(); // Non-privileged users don't need the employee filter
        }

        Log::info('Leave Requests Loaded: ' . $leaveRequests->toJson());
        return view('leave_requests.index', compact('leaveRequests', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'leave_type' => 'required|in:Sick,Maternity,Unpaid,Paid,half_day_first,half_day_second',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            $leaveRequest = LeaveRequest::create([
                'user_id' => Auth::id(),
                'leave_type' => $validated['leave_type'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'reason' => $validated['reason'],
                'team_lead_status' => 'Submitted',
                'hr_status' => 'Submitted',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Leave request submitted successfully.',
                'data' => $leaveRequest,
            ]);
        } catch (\Exception $e) {
            Log::error('Leave Request Creation Failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating leave request: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(LeaveRequest $leaveRequest)
    {
        $user = Auth::user();

        // Admins (1), Tech Heads (2), and HR (7) can view all leave requests
        if (in_array($user->role_id, [1, 2, 7])) {
            // No additional restrictions
        } elseif ($user->role_id === 3) {
            // Team Leads can only view leave requests from their department
            $departmentId = optional($user->employee)->department_id;
            if (!$departmentId || $leaveRequest->user->employee->department_id !== $departmentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view this leave request.',
                ], 403);
            }
        } else {
            // Other users can only view their own leave requests
            if ($leaveRequest->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view this leave request.',
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'employee' => optional($leaveRequest->user)->employee ?
                    (optional($leaveRequest->user->employee)->first_name . ' ' . optional($leaveRequest->user->employee)->last_name) : 'N/A',
                'leave_type' => $leaveRequest->leave_type,
                'start_date' => $leaveRequest->start_date->format('Y-m-d'),
                'end_date' => $leaveRequest->end_date->format('Y-m-d'),
                'duration' => $leaveRequest->duration,
                'reason' => $leaveRequest->reason ?? 'N/A',
                'team_lead_status' => $leaveRequest->team_lead_status ?? 'Submitted',
                'hr_status' => $leaveRequest->hr_status ?? 'Submitted',
                'team_lead_approver' => optional($leaveRequest->teamLeadApprover)->employee ?
                    (optional($leaveRequest->teamLeadApprover->employee)->first_name . ' ' . optional($leaveRequest->teamLeadApprover->employee)->last_name) : 'N/A',
                'hr_approver' => optional($leaveRequest->hrApprover)->employee ?
                    (optional($leaveRequest->hrApprover->employee)->first_name . ' ' . optional($leaveRequest->hrApprover->employee)->last_name) : 'N/A',
                'team_lead_approved_at' => $leaveRequest->team_lead_approved_at ? $leaveRequest->team_lead_approved_at->format('Y-m-d H:i:s') : 'N/A',
                'hr_approved_at' => $leaveRequest->hr_approved_at ? $leaveRequest->hr_approved_at->format('Y-m-d H:i:s') : 'N/A',
                'team_lead_comments' => $leaveRequest->team_lead_comments ?? 'N/A',
                'hr_comments' => $leaveRequest->hr_comments ?? 'N/A',
            ],
        ]);
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        $isTeamLead = $user->role_id === 3;
        $isHR = $user->role_id === 7;

        if (!$isTeamLead && !$isHR) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to approve leave requests.',
            ], 403);
        }

        $validated = $request->validate([
            'approver_comments' => 'nullable|string|max:1000',
        ]);

        try {
            DB::transaction(function () use ($leaveRequest, $user, $isTeamLead, $isHR, $validated) {
                $updateData = [];
                if ($isTeamLead) {
                    $updateData = [
                        'team_lead_status' => 'Approved',
                        'team_lead_approved_by' => $user->id,
                        'team_lead_approved_at' => now(),
                        'team_lead_comments' => $validated['approver_comments'],
                    ];
                } elseif ($isHR) {
                    $updateData = [
                        'hr_status' => 'Approved',
                        'hr_approved_by' => $user->id,
                        'hr_approved_at' => now(),
                        'hr_comments' => $validated['approver_comments'],
                    ];
                }
                $leaveRequest->update($updateData);
            });

            return response()->json([
                'success' => true,
                'message' => 'Leave request approved successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Leave Request Approval Failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error approving leave request: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        $isTeamLead = $user->role_id === 3;
        $isHR = $user->role_id === 7;

        if (!$isTeamLead && !$isHR) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to reject leave requests.',
            ], 403);
        }

        $validated = $request->validate([
            'approver_comments' => 'required|string|max:1000',
        ]);

        try {
            DB::transaction(function () use ($leaveRequest, $user, $isTeamLead, $isHR, $validated) {
                $updateData = [];
                if ($isTeamLead) {
                    $updateData = [
                        'team_lead_status' => 'Rejected',
                        'team_lead_approved_by' => $user->id,
                        'team_lead_approved_at' => now(),
                        'team_lead_comments' => $validated['approver_comments'],
                    ];
                } elseif ($isHR) {
                    $updateData = [
                        'hr_status' => 'Rejected',
                        'hr_approved_by' => $user->id,
                        'hr_approved_at' => now(),
                        'hr_comments' => $validated['approver_comments'],
                    ];
                }
                $leaveRequest->update($updateData);
            });

            return response()->json([
                'success' => true,
                'message' => 'Leave request rejected successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Leave Request Rejection Failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting leave request: ' . $e->getMessage(),
            ], 500);
        }
    }
}
