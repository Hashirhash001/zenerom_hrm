<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the departments.
     */
    public function index()
    {
        // Retrieve all departments
        $departments = Department::all();
        return view('departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new department.
     */
    public function create()
    {
        return view('departments.create');
    }

    /**
     * Store a newly created department in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status'      => 'required|integer',
        ]);

        // Handle file upload if provided
       if ($request->hasFile('image')) {
            $file = $request->file('image');
            // Generate a filename: "depimg" + current timestamp + extension
            $filename = 'depimg' . time() . '.' . $file->getClientOriginalExtension();
            // Save the file in the public/uploads/departments folder
            $file->move(public_path('uploads/departments'), $filename);
            $validated['image'] = $filename;
        }

        // Debug output: check the validated data before creating the record
        $department = Department::create($validated);

        // Return a JSON response
        return response()->json([
            'success'   => true,
            'message'   => 'Department created successfully!',
            'department'=> $department
        ]);
    }


    public function search(Request $request)
    {
        $query = $request->input('query');

        // Filter departments whose name matches the query (case-insensitive)
        $departments = Department::where('name', 'LIKE', "%{$query}%")->get();

        // Render a partial view (_list) that displays the matching departments.
        $html = view('departments._list', compact('departments'))->render();

        return response()->json(['html' => $html]);
    }




    /**
     * Display the specified department.
     */
    public function show(Department $department)
    {
        return view('departments.show', compact('department'));
    }

    /**
     * Show the form for editing the specified department.
     */
    public function edit(Department $department)
    {
        return view('departments.edit_modal', compact('department'));
    }


    /**
     * Update the specified department in storage.
     */
    public function update(Request $request, Department $department)
    {
        // Validate the request data
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status'      => 'required|integer',
        ]);

        // Handle file upload if provided
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            // Generate a filename: "depimg" + current timestamp + extension
            $filename = 'depimg' . time() . '.' . $file->getClientOriginalExtension();
            // Save the file in the public/uploads/departments folder
            $file->move(public_path('uploads/departments'), $filename);
            $validated['image'] = $filename;
        }

        // Update the department record
        $department->update($validated);

        // Return a JSON response
        return response()->json([
            'success'   => true,
            'message'   => 'Department updated successfully!',
            'department'=> $department
        ]);
    }

    /**
     * Remove the specified department from storage.
     */
    public function destroy(Request $request, Department $department)
    {
        try {
            $department->delete();
            return response()->json([
                'success' => true,
                'message' => 'Department deleted successfully!'
            ]);
        } catch (\Exception $e) {
            // Log the exception if needed: \Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Error deleting department.'
            ], 500);
        }
    }
}
