<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees.
     */
    public function index()
    {
        $employees = Employee::all();
        $departments = Department::all();
        $roles = Role::all();
        return view('employees.index', compact('employees', 'departments', 'roles'));
    }

    /**
     * Show the form for creating a new employee.
     * (In our case, the create form is loaded via a modal.)
     */
    public function create()
    {
        $departments = Department::all();
        $roles = Role::all();
        return view('employees.create_modal', compact('departments', 'roles'));
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id'        => 'required|string|max:50|unique:employees,employee_id',
            'first_name'         => 'required|string|max:100',
            'middle_name'        => 'nullable|string|max:100',
            'last_name'          => 'required|string|max:100',
            'email'              => 'required|email|max:255',
            'company_email'      => 'nullable|email|max:255',
            'phone'              => 'nullable|string|max:50',
            'emergency_contact'  => 'nullable|string|max:50',
            'emergency_contact_name' => 'nullable|string|max:100',
            'age'                => 'nullable|integer',
            'gender'             => 'nullable|string|max:20',
            'dob'                => 'nullable|date',
            'permanent_address'  => 'nullable|string',
            'local_address'      => 'nullable|string',
            'blood_group'        => 'nullable|string|max:10',
            'whatsapp'           => 'nullable|string|max:50',
            'status'             => 'required|integer',
            'department_id'      => 'nullable|integer',
            'role_id'            => 'nullable|integer',
        ]);

        // Handle file uploads if provided.
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'empimg' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/employees'), $filename);
            $validated['image'] = $filename;
        }

        if ($request->hasFile('cv_file')) {
            $file = $request->file('cv_file');
            $filename = 'empcv' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/employees'), $filename);
            $validated['cv_file'] = $filename;
        }

        $employee = Employee::create($validated);

        return response()->json([
            'success'   => true,
            'message'   => 'Employee created successfully!',
            'employee'  => $employee,
        ]);
    }
    public function departmentEmployees(Request $request)
    {
        $techhead = Auth::user();
        $departmentId = optional($techhead->employee)->department_id;

        $query = \App\Models\Employee::where('department_id', $departmentId);

        // Optional filter: staff name or id if needed.
        $staffId = $request->input('staff_id', null);
        if ($staffId) {
            $query->where('id', $staffId);
        }

        $records = $query->get();

        // For filter dropdown, get all employees in the same department.
        $employees = \App\Models\Employee::where('department_id', $departmentId)->get();

        return view('employees.department', compact('records', 'employees'));
    }


    /**
     * Display the specified employee.
     * (This method is used if you navigate to a separate page.)
     */
    public function show(Employee $employee)
    {
        return view('employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified employee.
     * (This returns a partial view to be loaded via Ajax in a modal.)
     */
    public function edit(Employee $employee)
    {
        $departments = Department::all();
        $roles = Role::all();
        return view('employees.edit_modal', compact('employee', 'departments', 'roles'));
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'employee_id'        => 'required|string|max:50|unique:employees,employee_id,' . $employee->id,
            'first_name'         => 'required|string|max:100',
            'middle_name'        => 'nullable|string|max:100',
            'last_name'          => 'required|string|max:100',
            'email'              => 'required|email|max:255',
            'company_email'      => 'nullable|email|max:255',
            'phone'              => 'nullable|string|max:50',
            'emergency_contact'  => 'nullable|string|max:50',
            'emergency_contact_name' => 'nullable|string|max:100',
            'age'                => 'nullable|integer',
            'gender'             => 'nullable|string|max:20',
            'dob'                => 'nullable|date',
            'permanent_address'  => 'nullable|string',
            'local_address'      => 'nullable|string',
            'blood_group'        => 'nullable|string|max:10',
            'whatsapp'           => 'nullable|string|max:50',
            'status'             => 'required|integer',
            'department_id'      => 'nullable|integer',
            'role_id'            => 'nullable|integer',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'empimg' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/employees'), $filename);
            $validated['image'] = $filename;
        }

        if ($request->hasFile('cv_file')) {
            $file = $request->file('cv_file');
            $filename = 'empcv' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/employees'), $filename);
            $validated['cv_file'] = $filename;
        }

        $employee->update($validated);

        return response()->json([
            'success'   => true,
            'message'   => 'Employee updated successfully!',
            'employee'  => $employee,
        ]);
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroy(Employee $employee)
    {
        $employee->delete();
        return response()->json([
            'success' => true,
            'message' => 'Employee deleted successfully!',
        ]);
    }

    /**
     * Search employees by first name, last name or email.
     */
    public function search(Request $request)
    {
        $query = $request->input('query');

        if (empty($query)) {
            $employees = Employee::with(['department', 'role'])->get();
        } else {
            $employees = Employee::with(['department', 'role'])
                ->where(function($q) use ($query) {
                    $q->where('first_name', 'LIKE', "%{$query}%")
                      ->orWhere('last_name', 'LIKE', "%{$query}%")
                      ->orWhere('email', 'LIKE', "%{$query}%");
                })
                ->get();
        }

        $html = view('employees._list', compact('employees'))->render();
        return response()->json(['html' => $html]);
    }

    /**
     * Load full employee details (for modal view).
     */
    public function viewDetails(Employee $employee)
    {
        $employee->load(['department', 'role']);
        $departments = Department::all();
        $roles = Role::all();
        $menuItems = \App\Models\MenuItem::orderBy('menu_order')->get();
        $userAccess = $employee->user ? $employee->user->accessPrivileges()->get() : collect();

        return view('employees.view_modal', compact('employee', 'departments', 'roles', 'menuItems', 'userAccess'));
    }

    /**
     * Load the resignation form (for modal view).
     */
    public function resignForm(Employee $employee)
    {
        return view('employees.resign_modal', compact('employee'));
    }

    /**
     * Update the resignation details for an employee.
     */
    public function updateResignation(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'resignation'         => 'required|date',
            'resignation_details' => 'nullable|string',
        ]);

        $employee->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Resignation details updated successfully!'
        ]);
    }

    /**
     * Toggle employee activation status.
     */
    public function toggleActivation(Employee $employee)
    {
        $newStatus = ($employee->status == 1) ? 0 : 1;
        $employee->update(['status' => $newStatus]);

        $message = $newStatus == 1 ? 'Employee activated successfully!' : 'Employee deactivated successfully!';
        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Save or update the user account associated with an employee.
     */
    public function saveAccount(Request $request)
    {
        $validated = $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'password'      => 'nullable|string|min:6',
            'role_id'       => 'required|integer',
            'department_id' => 'nullable|integer',
        ]);

        $employee = Employee::find($validated['employee_id']);
        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found.'], 404);
        }

        if ($employee->user) {
            $user = $employee->user;
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->role_id = $validated['role_id'];
            $user->department_id = $validated['department_id'] ?? null;
            if (!empty($validated['password'])) {
                $user->password = bcrypt($validated['password']);
            }
            $user->save();
            $message = "Account updated successfully.";
        } else {
            $user = \App\Models\User::create([
                'id'       => $employee->id,
                'user_id'       => $employee->id,
                'name'          => $validated['name'],
                'email'         => $validated['email'],
                'password'      => bcrypt($validated['password'] ?? 'defaultpassword'),
                'role_id'       => $validated['role_id'],
                'department_id' => $validated['department_id'] ?? null,
            ]);
            $message = "Account created successfully.";
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    /**
     * Save access control privileges for the employee's user account.
     */
   
    public function saveAccessControl(Request $request, Employee $employee)
    {
        // Validate the request.
        $validated = $request->validate([
            'privileges' => 'sometimes|array',
        ]);

        // Ensure the employee has an associated user account.
        if (!$employee->user) {
            return response()->json([
                'success' => false, 
                'message' => 'Employee does not have an associated user account.'
            ], 422);
        }
        
        $userId = $employee->user->id;
        
        // Remove all existing access privilege records for this user.
        \App\Models\UserAccessPrivilege::where('user_id', $userId)->delete();

        $privileges = $validated['privileges'] ?? [];

        // Create new privilege records.
        foreach ($privileges as $menu_item_id => $perms) {
            $record = new \App\Models\UserAccessPrivilege();
            $record->user_id = $userId;
            $record->menu_item_id = $menu_item_id;
            $record->can_view   = isset($perms['can_view']) ? 1 : 0;
            $record->can_edit   = isset($perms['can_edit']) ? 1 : 0;
            $record->can_delete = isset($perms['can_delete']) ? 1 : 0;
            $record->can_create = isset($perms['can_create']) ? 1 : 0;
            $record->save();
        }

        return response()->json(['success' => true, 'message' => 'Access privileges updated successfully.']);
    }



}
