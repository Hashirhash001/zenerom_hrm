<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Models\PublicHoliday;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PublicHolidayController extends Controller
{
    private function isAuthorized()
    {
        $user = Auth::user();
        return $user && in_array($user->role_id, [1, 2, 7]);
    }

    public function index(Request $request)
    {
        if (!$this->isAuthorized()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 403);
            }
            return redirect()->route('dashboard.index');
        }

        $year = $request->input('year', date('Y'));
        $month = $request->input('month');
        $search = $request->input('search');
        $sortBy = $request->input('sort_by', 'id');
        $sortDirection = $request->input('sort_direction', 'asc');

        $user = Auth::user();

        if (in_array($user->role_id, [1, 2, 7])){
            response()->json(['error' => 'Unauthorized'], 401);
        }

        $query = PublicHoliday::query();

        if ($year) {
            $query->where('year', $year);
        }

        if ($month) {
            // MariaDB-compatible query using JSON_SEARCH for month filtering
            $query->whereRaw('JSON_SEARCH(dates, "one", ?) IS NOT NULL', ["{$year}-{$month}%"]);
        }

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $query->orderBy($sortBy, $sortDirection);

        if ($request->ajax()) {
            $holidays = $query->paginate(10);
            return response()->json([
                'success' => true,
                'holidays' => $holidays->items(),
                'pagination' => [
                    'current_page' => $holidays->currentPage(),
                    'last_page' => $holidays->lastPage(),
                    'total' => $holidays->total(),
                ],
            ]);
        }

        $holidays = $query->paginate(10);
        $roles = Role::all();

        return view('public_holidays.index', compact('holidays', 'roles', 'year'));
    }

    public function store(Request $request)
    {
        if (!$this->isAuthorized()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 403);
            }
            return view('errors.unauthorized', ['message' => 'Unauthorized access.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'dates' => 'required|array',
            'dates.*' => 'date_format:Y-m-d',
            'year' => 'required|integer|min:2000|max:2100',
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        try {
            $holiday = PublicHoliday::create($validated);
            return response()->json([
                'success' => true,
                'message' => 'Holiday created successfully.',
                'data' => $holiday
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating holiday: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating holiday.',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    public function show($id = null, Request $request)
    {
        if (!$this->isAuthorized()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 403);
            }
            return view('errors.unauthorized', ['message' => 'Unauthorized access.']);
        }

        try {
            $holiday = PublicHoliday::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $holiday
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching holiday: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Holiday not found.'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        if (!$this->isAuthorized()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 403);
            }
            return view('errors.unauthorized', ['message' => 'Unauthorized access.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'dates' => 'required|array',
            'dates.*' => 'date_format:Y-m-d',
            'year' => 'required|integer|min:2000|max:2100',
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        try {
            $holiday = PublicHoliday::findOrFail($id);
            $holiday->update($validated);
            return response()->json([
                'success' => true,
                'message' => 'Holiday updated successfully.',
                'data' => $holiday
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating holiday: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating holiday.',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    public function destroy($id = null, Request $request)
    {
        if (!$this->isAuthorized()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 403);
            }
            return view('errors.unauthorized', ['message' => 'Unauthorized access.']);
        }

        try {
            $holiday = PublicHoliday::findOrFail($id);
            $holiday->delete();
            return response()->json([
                'success' => true,
                'message' => 'Holiday deleted successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting holiday: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting holiday.'
            ], 500);
        }
    }
}
