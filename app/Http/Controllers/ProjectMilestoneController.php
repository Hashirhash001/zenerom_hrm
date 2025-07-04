<?php

namespace App\Http\Controllers;

use App\Models\ProjectMilestone;
use Illuminate\Http\Request;

class ProjectMilestoneController extends Controller
{
    /**
     * Store a new milestone for a project service.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id'           => 'required|integer|exists:projects,id',
            'project_service_id'   => 'required|numeric', // adjust type if needed
            'title'                => 'required|string|max:255',
            'description'          => 'nullable|string',
            'due_date'             => 'nullable|date',
            'status'               => 'required|in:pending,completed,delayed',
        ]);

        $milestone = ProjectMilestone::create($validated);

        return response()->json([
            'success'   => true,
            'message'   => 'Milestone added successfully!',
            'milestone' => $milestone
        ]);
    }

    /**
     * Show the form for editing a milestone.
     */
   public function edit(ProjectMilestone $projectMilestone)
    {
        $project = $projectMilestone->project;
        // Load project services so we can list them in the dropdown.
        $project->load('projectServices.service');
        return view('project_milestones.edit_modal', [
            'projectMilestone' => $projectMilestone,
            'project' => $project,
        ]);
    }



    /**
     * Update the specified milestone.
     */
    public function update(Request $request, ProjectMilestone $projectMilestone)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date'    => 'nullable|date',
            'status'      => 'required|in:pending,completed,delayed',
        ]);

        $projectMilestone->update($validated);

        return response()->json([
            'success'   => true,
            'message'   => 'Milestone updated successfully!',
            'milestone' => $projectMilestone
        ]);
    }

    /**
     * Remove the specified milestone.
     */
    public function destroy(ProjectMilestone $projectMilestone)
    {
        $projectMilestone->delete();

        return response()->json([
            'success' => true,
            'message' => 'Milestone deleted successfully!'
        ]);
    }
}
