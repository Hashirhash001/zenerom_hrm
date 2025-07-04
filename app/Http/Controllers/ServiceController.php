<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Department; // if you want to list departments in the form
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Display a listing of the services.
     */
    public function index()
    {
        $services = Service::latest()->get();
        $departments = \App\Models\Department::all(); // load departments
        return view('services.index', compact('services', 'departments'));
    }

    /**
     * Show the form for creating a new service.
     * (Loaded in a modal)
     */
    public function create()
    {
        // If you want a dropdown for departments:
        $departments = Department::all();
        return view('services.create_modal', compact('departments'));
    }

    /**
     * Store a newly created service in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'details'       => 'required|string',
            'department_id' => 'required|integer|exists:departments,id',
            'status'        => 'nullable|integer'
        ]);

        $service = Service::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Service created successfully!',
            'service' => $service
        ]);
    }

    /**
     * Display the specified service.
     */
    public function show(Service $service)
    {
        // If you want to show department details, the model relationship will be used.
        $service->load('department');
        return view('services.show', compact('service'));
    }

    /**
     * Show the form for editing the specified service.
     * (Loaded in a modal)
     */
    public function edit(Service $service)
    {
        $departments = \App\Models\Department::all();
        return view('services.edit_modal', compact('service', 'departments'));
    }

    /**
     * Update the specified service in storage.
     */
    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'details'       => 'required|string',
            'department_id' => 'required|integer|exists:departments,id',
            'status'        => 'nullable|integer'
        ]);

        $service->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully!',
            'service' => $service
        ]);
    }

    /**
     * Remove the specified service from storage.
     */
    public function destroy(Service $service)
    {
        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service deleted successfully!'
        ]);
    }

    /**
     * Search services by name or details.
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        if (empty($query)) {
            $services = Service::latest()->get();
        } else {
            $services = Service::where('name', 'LIKE', "%{$query}%")
                ->orWhere('details', 'LIKE', "%{$query}%")
                ->latest()
                ->get();
        }
        $html = view('services._list', compact('services'))->render();
        return response()->json(['html' => $html]);
    }
}
