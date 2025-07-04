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

        // HR and Team Leads can view all leave requests and filter by employee
        if (in_array($user->role_id, [3, 7])) {
            $leaveRequests = LeaveRequest::with(['user', 'approver'])
                ->when(request('start_date'), fn($q) => $q->where('start_date', '>=', request('start_date')))
                ->when(request('end_date'), fn($q) => $q->where('end_date', '<=', request('end_date')))
                ->when(request('status'), fn($q) => $q->where('status', request('status')))
                ->when(request('employee_id'), fn($q) => $q->where('user_id', request('employee_id')))
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            $employees = \App\Models\User::whereIn('role_id', [1, 2, 3, 4, 5, 6, 7])->get();
        } else {
            // Other users can only view their own leave requests
            $leaveRequests = LeaveRequest::with(['user', 'approver'])
                ->where('user_id', $user->id)
                ->when(request('start_date'), fn($q) => $q->where('start_date', '>=', request('start_date')))
                ->when(request('end_date'), fn($q) => $q->where('end_date', '<=', request('end_date')))
                ->when(request('status'), fn($q) => $q->where('status', request('status')))
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }

        Log::info('Leave Requests Loaded: ' . $leaveRequests->toJson());
        return view('leave_requests.index', compact('leaveRequests', 'employees'));
    }

    public function create()
    {
        return view('leave_requests.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'leave_type' => 'required|in:Annual,Sick,Maternity,Unpaid',
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
                'status' => 'Submitted',
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
        if ($leaveRequest->user_id !== $user->id && !in_array($user->role_id, [3, 7])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this leave request.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'employee' => optional($leaveRequest->user)->name ?? 'N/A',
                'leave_type' => $leaveRequest->leave_type,
                'start_date' => $leaveRequest->start_date->format('Y-m-d'),
                'end_date' => $leaveRequest->end_date->format('Y-m-d'),
                'duration' => $leaveRequest->duration,
                'reason' => $leaveRequest->reason ?? 'N/A',
                'status' => $leaveRequest->status,
                'approver' => optional($leaveRequest->approver)->name ?? 'N/A',
                'approved_at' => $leaveRequest->approved_at ? $leaveRequest->approved_at->format('Y-m-d H:i:s') : 'N/A',
                'approver_comments' => $leaveRequest->approver_comments ?? 'N/A',
            ],
        ]);
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        if (!in_array($user->role_id, [3, 7])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to approve leave requests.',
            ], 403);
        }

        $validated = $request->validate([
            'approver_comments' => 'nullable|string|max:1000',
        ]);

        try {
            DB::transaction(function () use ($leaveRequest, $user, $validated) {
                $leaveRequest->update([
                    'status' => 'Approved',
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                    'approver_comments' => $validated['approver_comments'],
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
        if (!in_array($user->role_id, [3, 7])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to reject leave requests.',
            ], 403);
        }

        $validated = $request->validate([
            'approver_comments' => 'required|string|max:1000',
        ]);

        try {
            DB::transaction(function () use ($leaveRequest, $user, $validated) {
                $leaveRequest->update([
                    'status' => 'Rejected',
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                    'approver_comments' => $validated['approver_comments'],
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
}
