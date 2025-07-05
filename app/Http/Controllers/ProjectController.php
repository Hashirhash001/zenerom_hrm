<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Customer;  // assuming you want to show customer info
use App\Models\ProjectMilestone;
use App\Models\ProjectDocument;
use App\Models\ProjectStatusHistory;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role_id == 3) {
            // Get the user's department id from their employee record.
            $departmentId = optional($user->employee)->department_id;

            // Retrieve only projects that have a service assigned from the user's department.
            $projects = Project::join('project_services', 'projects.id', '=', 'project_services.project_id')
                ->join('services', 'services.id', '=', 'project_services.service_id')
                ->join('departments', 'departments.id', '=', 'services.department_id')
                ->where('departments.id', $departmentId)
                ->groupBy(
                    'projects.id',
                    'projects.customer_id',
                    'projects.name',
                    'projects.requirements',
                    'projects.status',
                    'projects.onboarded_time',
                    'projects.payment_status',
                    'projects.payment_type',
                    'projects.project_owner_id',
                    'projects.created_at',
                    'projects.updated_at'
                )
                ->orderByDesc('projects.onboarded_time')
                ->select(
                    'projects.id',
                    'projects.customer_id',
                    'projects.name',
                    'projects.requirements',
                    'projects.status',
                    'projects.onboarded_time',
                    'projects.payment_status',
                    'projects.payment_type',
                    'projects.project_owner_id',
                    'projects.created_at',
                    'projects.updated_at'
                )
                ->get();
        } else {
            // For other roles, retrieve all projects.
            $projects = Project::latest()->get();
        }

        $customers = \App\Models\Customer::all();
        $departments = \App\Models\Department::all();
        $employees = \App\Models\Employee::all();

        return view('projects.index', compact('projects', 'customers', 'departments', 'employees'));
    }




    /**
     * Show the form for creating a new project.
     */
    public function create()
    {
        $customers = \App\Models\Customer::all();
        $departments = \App\Models\Department::all();
        $employees = \App\Models\Employee::all(); // Fetch all employees for the project owner dropdown
        return view('projects.create_modal', compact('customers', 'departments', 'employees'));
    }
    /**
     * Store a newly created project.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'      => 'required|integer|exists:customers,id',
            'name'             => 'required|string|max:255',
            'requirements'     => 'nullable|string',
            'status'           => 'required|in:active,completed,delayed,canceled,hold',
            'onboarded_time'   => 'nullable|date',
            'payment_status'   => 'required|in:paid,unpaid,pending',
            'payment_type'     => 'required|in:monthly,one_time',
            'project_owner_id' => 'required|integer|exists:employees,id',


        ]);

        $project = Project::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Project created successfully!',
            'project' => $project
        ]);
    }

    /**
     * Display the specified project.
     */
    // public function show(Project $project)
    // {
    //     $project->load(['customer', 'owner', 'milestones', 'documents', 'statusHistory', 'projectServices', 'customer.contacts']);
    //     $customers = \App\Models\Customer::all();
    //     $departments = \App\Models\Department::all();
    //     $employees = \App\Models\Employee::all();
    //     $projectDescription = $project->projectDescription()->first();
    //     $services = \App\Models\Service::all();

    //     return view('projects.show', compact('project', 'projectDescription','customers', 'departments', 'employees', 'services'));
    // }
public function show(Project $project)
{
    $project->load([
        'customer',
        'owner',
        'milestones',
        'documents',
        'statusHistory',
        'projectServices',
        'projectDescriptions',
        'customer.contacts',
        'tasks.service',
        'tasks.assignments.staff'
    ]);

    $customers = \App\Models\Customer::all();
    $departments = \App\Models\Department::all();
    $employees = \App\Models\Employee::all();
    $services = \App\Models\Service::all();

    $projectDescription = $project->projectDescriptions()->first();

    return view('projects.show', compact('project', 'projectDescription', 'customers', 'departments', 'employees', 'services'));
}
/**
     * Display projects that have at least one service from the logged-in user's department.
     */
public function departmentProjects(Request $request)
{
    $user = Auth::user();
    $departmentId = optional($user->employee)->department_id;

    // Retrieve projects that have at least one service from the user's department.
    // Eager load 'client' and 'assignedStaff' (the employee corresponding to project_owner_id).
    $projects = \App\Models\Project::whereHas('services', function ($query) use ($departmentId) {
            $query->where('department_id', $departmentId);
        })
        ->with(['client', 'assignedStaff'])
        ->orderBy('created_at', 'desc')
        ->get();

    return view('projects.department_projects', compact('projects'));
}








    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project)
    {
        $customers = Customer::all();
        $departments = Department::all();
        return view('projects.edit_modal', compact('project', 'customers', 'departments'));
    }

    /**
     * Update the specified project.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'customer_id'      => 'required|integer|exists:customers,id',
            'name'             => 'required|string|max:255',
            'requirements'     => 'nullable|string',
            'status'           => 'required|in:active,completed,delayed,canceled,hold',
            'onboarded_time'   => 'nullable|date',
            'payment_status'   => 'required|in:paid,unpaid,pending',
            'payment_type'     => 'required|in:monthly,one_time',
            'project_owner_id' => 'required|integer|exists:users,id',
        ]);

        $project->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Project updated successfully!',
            'project' => $project
        ]);
    }

    /**
     * Remove the specified project.
     */
    public function destroy(Project $project)
    {
        $project->delete();
        return response()->json([
            'success' => true,
            'message' => 'Project deleted successfully!'
        ]);
    }

    /**
     * Search projects by name or requirements.
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        if (empty($query)) {
            $projects = Project::latest()->get();
        } else {
            $projects = Project::where('name', 'LIKE', "%{$query}%")
                ->orWhere('requirements', 'LIKE', "%{$query}%")
                ->latest()
                ->get();
        }
        $html = view('projects._list', compact('projects'))->render();
        return response()->json(['html' => $html]);
    }

    // --- Additional functions for managing milestones, documents, and status history ---
    // You might create separate controllers for these, but here are example functions integrated into the ProjectController.

    /**
     * Add a new milestone to the project.
     */
    public function addMilestone(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date'    => 'nullable|date',
            'status'      => 'required|in:pending,completed,delayed',
        ]);
        $validated['project_id'] = $project->id;
        $milestone = ProjectMilestone::create($validated);
        return response()->json([
            'success'   => true,
            'message'   => 'Milestone added successfully!',
            'milestone' => $milestone,
        ]);
    }

    /**
     * Add a new document to the project.
     */
    public function addDocument(Request $request, Project $project)
    {
        $validated = $request->validate([
            'document_name' => 'required|string|max:255',
            'file'          => 'required|file',
            'description'   => 'nullable|string'
        ]);

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = 'projdoc' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/project_documents'), $filename);
            $validated['file_path'] = $filename;
        }
        $validated['project_id'] = $project->id;
        $document = \App\Models\ProjectDocument::create([
            'project_id'    => $project->id,
            'document_name' => $validated['document_name'],
            'file_path'     => $validated['file_path'],
            'description'   => $validated['description'] ?? null,
        ]);
        return response()->json([
            'success'   => true,
            'message'   => 'Document added successfully!',
            'document'  => $document,
        ]);
    }

    /**
     * Add a new status history record.
     */
    public function addStatusHistory(Request $request, Project $project)
    {
        $validated = $request->validate([
            'old_status' => 'required|in:active,completed,delayed,canceled,hold',
            'new_status' => 'required|in:active,completed,delayed,canceled,hold',
            'changed_by' => 'required|integer|exists:users,id'
        ]);
        $validated['project_id'] = $project->id;
        $history = \App\Models\ProjectStatusHistory::create($validated);
        return response()->json([
            'success' => true,
            'message' => 'Project status history added successfully!',
            'history' => $history
        ]);
    }
}
