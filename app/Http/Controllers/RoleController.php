<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    // Display a listing of roles.
    public function index()
    {
        $roles = Role::all();
        return view('roles.index', compact('roles'));
    }

    // Show the form for creating a new role.
    // (If you’re using a modal, you can include the form in the index view.)
    public function create()
    {
        return view('roles.create'); // Optional: use a separate view if desired.
    }

    // Store a newly created role in storage.
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $role = Role::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully!',
            'role'    => $role,
        ]);
    }

    // Display the specified role.
    public function show(Role $role)
    {
        return view('roles.show', compact('role'));
    }

    // Show the form for editing the specified role.
    // We'll return a partial view for loading in a modal.
    public function edit(Role $role)
    {
        return view('roles.edit_modal', compact('role'));
    }

    // Update the specified role in storage.
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $role->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully!',
            'role'    => $role,
        ]);
    }

    // Remove the specified role from storage.
    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully!',
        ]);
    }

    // Search method for Ajax live search.
    public function search(Request $request)
    {
        $query = $request->input('query');
        $roles = Role::where('name', 'LIKE', '%' . $query . '%')->get();
        $html  = view('roles._list', compact('roles'))->render();
        return response()->json(['html' => $html]);
    }
}
