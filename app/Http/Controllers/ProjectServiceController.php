<?php

namespace App\Http\Controllers;

use App\Models\ProjectService;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectServiceController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id'         => 'required|integer|exists:projects,id',
            'service_id'         => 'required|integer|exists:services,id',
            'assigned_to_staff'  => 'nullable|integer|exists:employees,id',
            'status'             => 'required|in:pending,in_progress,completed,canceled,hold',
            'notes'              => 'nullable|string',
        ]);

        $projectService = ProjectService::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Project service added successfully!',
            'projectService' => $projectService,
        ]);
    }

   public function edit(ProjectService $projectService)
{
    // dd($projectService); // temporary debug – remove after verifying
    $project = $projectService->project;
    $services = \App\Models\Service::all();
    $employees = \App\Models\Employee::all();
    
    return view('projects._project_service_edit_modal', compact('projectService', 'project', 'services', 'employees'));
}


    public function update(Request $request, ProjectService $projectService)
    {
        $validated = $request->validate([
            'project_id'         => 'required|integer|exists:projects,id',
            'service_id'         => 'required|integer|exists:services,id',
            'assigned_to_staff'  => 'nullable|integer|exists:employees,id',
            'status'             => 'required|in:pending,in_progress,completed,canceled,hold',
            'notes'              => 'nullable|string',
        ]);

        $projectService->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Project service updated successfully!',
            'projectService' => $projectService,
        ]);
    }

    public function destroy(ProjectService $projectService)
    {
        $projectService->delete();

        return response()->json([
            'success' => true,
            'message' => 'Project service deleted successfully!'
        ]);
    }
}
