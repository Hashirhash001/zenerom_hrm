<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\ActivityLog;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $leaveRequests = [];
        $employees = [];

        if (in_array($user->role_id, [1, 2, 7])) {
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
                ->when(request('user_id'), fn($q) => $q->where('user_id', request('user_id')))
                ->when(request('sort_by'), fn($q) => $q->orderBy(request('sort_by'), request('sort_direction', 'desc')));
            $employees = User::whereIn('role_id', [1, 2, 3, 4, 5, 6, 7, 12, 13])
                ->with('employee')
                ->get();
        } elseif ($user->role_id === 3) {
            $departmentId = optional($user->employee)->department_id;
            if ($departmentId) {
                $leaveRequests = LeaveRequest::with(['user', 'teamLeadApprover', 'hrApprover'])
                    ->whereHas('user', function ($q) use ($departmentId) {
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
                    ->when(request('user_id'), fn($q) => $q->where('user_id', request('user_id')))
                    ->when(request('sort_by'), fn($q) => $q->orderBy(request('sort_by'), request('sort_direction', 'desc')));
                $employees = User::whereIn('role_id', [1, 2, 3, 4, 5, 6, 7, 12, 13])
                    ->whereHas('employee', function ($q) use ($departmentId) {
                        $q->where('department_id', $departmentId);
                    })
                    ->with('employee')
                    ->get();
            } else {
                $leaveRequests = collect();
                $employees = collect();
            }
        } else {
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
                ->when(request('sort_by'), fn($q) => $q->orderBy(request('sort_by'), request('sort_direction', 'desc')));
            $employees = collect();
        }

        // Handle AJAX request
        if (request('ajax')) {
            $leaveRequests = $leaveRequests->paginate(10);
            return response()->json([
                'success' => true,
                'leave_requests' => collect($leaveRequests->items())->map(function ($request) {
                    $employeeId = optional($request->user->employee)->employee_id;
                    return [
                        'id' => $request->id,
                        'user_id' => $request->user_id,
                        'employee' => $request->user->name . ($employeeId ? ' (' . $employeeId . ')' : ''),
                        'leave_type' => $request->leave_type,
                        'start_date' => $request->start_date->format('Y-m-d'),
                        'end_date' => $request->end_date->format('Y-m-d'),
                        'duration' => $request->duration,
                        'reason' => $request->reason,
                        'team_lead_status' => $request->team_lead_status,
                        'hr_status' => $request->hr_status,
                    ];
                })->toArray(),
                'pagination' => [
                    'current_page' => $leaveRequests->currentPage(),
                    'last_page' => $leaveRequests->lastPage(),
                    'total' => $leaveRequests->total(),
                ],
            ]);
        }

        $leaveRequests = $leaveRequests->paginate(10);

        // Log the view action
        ActivityLog::create([
            'user_id' => $user->id,
            'activity' => 'Viewed leave requests index',
            'activity_date' => now(),
            'ip_address' => request()->ip(),
        ]);

        Log::info('Leave Requests Loaded: ' . $leaveRequests->toJson());
        return view('leave_requests.index', compact('leaveRequests', 'employees'));
    }

    // In EmployeeController or LeaveRequestController
    public function listEmployees()
    {
        $user = Auth::user();

        // Only allow authorized users (role_id 1, 2, 3, 7) to access the endpoint
        if (!in_array($user->role_id, [1, 2, 3, 7])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], 403);
        }

        // Initialize query for employees
        $query = Employee::whereNull('resignation')
            ->where('status', 1)
            ->whereNotNull('first_name')
            ->whereNotNull('last_name')
            ->whereNotNull('employee_id');

        // For team leads (role_id = 3), restrict to their department
        if ($user->role_id === 3) {
            $departmentId = optional($user->employee)->department_id;
            if ($departmentId) {
                $query->where('department_id', $departmentId);
            } else {
                // If team lead has no department, return empty result
                $query->whereRaw('1 = 0'); // No results
            }
        }

        // Fetch employees and map to required format
        $employees = $query->get()->map(function ($employee) {
            return [
                'id' => $employee->user_id,
                'first_name' => $employee->first_name,
                'middle_name' => $employee->middle_name, // Can be null
                'last_name' => $employee->last_name,
                'employee_id' => $employee->employee_id,
            ];
        });

        return response()->json([
            'success' => true,
            'employees' => $employees->isEmpty() ? [] : $employees,
        ]);
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

            // Log the creation action
            ActivityLog::create([
                'user_id' => Auth::id(),
                'activity' => "Created leave request ID {$leaveRequest->id} of type {$validated['leave_type']}",
                'activity_date' => now(),
                'ip_address' => $request->ip(),
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

        // Ensure relationships are loaded
        $leaveRequest->load(['user.employee', 'teamLeadApprover.employee', 'hrApprover.employee']);

        // Admins (1), Tech Heads (2), and HR (7) can view all leave requests
        if (in_array($user->role_id, [1, 2, 7])) {
            // No additional restrictions
        } elseif ($user->role_id === 3) {
            // Team Leads can only view leave requests from their department
            $departmentId = optional($user->employee)->department_id;
            if (!$departmentId || !$leaveRequest->user || $leaveRequest->user->department_id !== $departmentId) {
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

        // Log the view action
        ActivityLog::create([
            'user_id' => $user->id,
            'activity' => "Viewed leave request ID {$leaveRequest->id}",
            'activity_date' => now(),
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'employee' => $leaveRequest->user ? ($leaveRequest->user->name . (optional($leaveRequest->user->employee)->employee_id ? ' (' . $leaveRequest->user->employee->employee_id . ')' : '')) : 'N/A',
                'leave_type' => $leaveRequest->leave_type,
                'start_date' => $leaveRequest->start_date->format('Y-m-d'),
                'end_date' => $leaveRequest->end_date->format('Y-m-d'),
                'duration' => $leaveRequest->duration,
                'reason' => $leaveRequest->reason ?? 'N/A',
                'team_lead_status' => $leaveRequest->team_lead_status ?? 'Submitted',
                'hr_status' => $leaveRequest->hr_status ?? 'Submitted',
                'team_lead_approver' => $leaveRequest->teamLeadApprover ? ($leaveRequest->teamLeadApprover->name . (optional($leaveRequest->teamLeadApprover->employee)->employee_id ? ' (' . $leaveRequest->teamLeadApprover->employee->employee_id . ')' : '')) : 'N/A',
                'hr_approver' => $leaveRequest->hrApprover ? ($leaveRequest->hrApprover->name . (optional($leaveRequest->hrApprover->employee)->employee_id ? ' (' . $leaveRequest->hrApprover->employee->employee_id . ')' : '')) : 'N/A',
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

                // Log the approval action
                ActivityLog::create([
                    'user_id' => $user->id,
                    'activity' => ($isTeamLead ? 'Team Lead' : 'HR') . " approved leave request ID {$leaveRequest->id}",
                    'activity_date' => now(),
                    'ip_address' => request()->ip(),
                ]);
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

                // Log the rejection action
                ActivityLog::create([
                    'user_id' => $user->id,
                    'activity' => ($isTeamLead ? 'Team Lead' : 'HR') . " rejected leave request ID {$leaveRequest->id}",
                    'activity_date' => now(),
                    'ip_address' => request()->ip(),
                ]);
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

    public function destroy(LeaveRequest $leaveRequest)
    {
        $user = Auth::user();

        // Check if the user is authorized (admin or request creator)
        if ($user->role_id !== 1 && $leaveRequest->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this leave request.',
            ], 403);
        }

        try {
            $leaveRequest->delete();

            ActivityLog::create([
                'user_id' => $user->id,
                'activity' => 'Deleted leave request #' . $leaveRequest->id,
                'activity_date' => now(),
                'ip_address' => request()->ip(),
            ]);

            Log::info('Leave Request Deleted: ID ' . $leaveRequest->id . ' by User ID ' . $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Leave request deleted successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting leave request #' . $leaveRequest->id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting leave request: ' . $e->getMessage(),
            ], 500);
        }
    }
}
