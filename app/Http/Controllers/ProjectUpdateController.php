<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProjectUpdate;
use App\Models\Project;
use App\Models\Service;
use App\Models\Notification;

class ProjectUpdateController extends Controller
{
    // Store a new project update and create a notification if assigned_to is provided.
   public function store(Request $request)
{
    // Validate only the fields that come from the form.
    $validated = $request->validate([
        'project_id'         => 'required|integer|exists:projects,id',
        'project_service_id' => 'nullable|integer',
        'title'              => 'required|string|max:255',
        'date'               => 'required|date',
        'note'               => 'nullable|string',
        'assigned_to'        => 'nullable|integer',
        'status'             => 'required|in:active,inactive',
        'received_status'    => 'nullable|string',
    ]);

    // Automatically set the current logged in user as entry_by.
    $validated['entry_by'] = auth()->id();
    // Set entry_time to null if not used.
    $validated['entry_time'] = null;

    // Create the project update record.
    $update = ProjectUpdate::create($validated);

    // If assigned_to is provided, create a notification.
    if (!empty($validated['assigned_to'])) {
        // Retrieve the project to get its name.
        $project = Project::find($validated['project_id']);
        $projectName = $project ? $project->name : '';

        $service = Service::find($validated['project_service_id']);
        $serviceName = $service ? $service->name : '';

        // Build the notification message.
        // Note: "task name" is assumed to be the project update title.
        $note = $validated['note'] ?? '';
        $notificationMessage = $note . "\n (" . $projectName . " | " . $serviceName." )";

        Notification::create([
            'user_id' => $validated['assigned_to'],
            'title'   => $validated['title'], // same as project update title
            'message' => $notificationMessage,
            'type'    => 'project_update',
        ]);
    }

    // Load the staff relationship so the view can display the staff name.
    if ($update->assigned_to) {
        $update->load('employee');
    }

    // Render the HTML snippet using the Blade partial.
    $updateHtml = view('project_updates.update_item', compact('update'))->render();

    return response()->json([
        'success'    => true,
        'message'    => 'Project update saved successfully!',
        'updateHtml' => $updateHtml,
        'update'     => $update,
    ]);
}




    // Update an existing update
  public function update(Request $request, $id)
{
    $update = ProjectUpdate::findOrFail($id);

    $validated = $request->validate([
        'project_service_id' => 'nullable|integer',
        'title'              => 'required|string|max:255',
        'note'               => 'nullable|string',
        'date'               => 'nullable|date',
        'assigned_to'        => 'nullable|integer',
        'status'             => 'required|in:active,inactive',
        'received_status'    => 'nullable|string',
    ]);

    // Update the record with the validated values.
    $update->update($validated);

    // If assigned_to is provided, create or update a notification.
    // (You might want to adjust this logic if you need to update an existing notification instead.)
    if (!empty($validated['assigned_to'])) {
        Notification::create([
            'user_id' => $validated['assigned_to'],
            'title'   => 'Project Update Modified',
            'message' => 'An update on your project has been modified.',
            'type'    => 'project_update',
        ]);
    }

    // Reload the staff relationship if assigned_to is set.
    if ($update->assigned_to) {
        $update->load('employee');
    }

    // Render updated update item HTML using a Blade partial.
    $updateHtml = view('project_updates.update_item', compact('update'))->render();

    return response()->json([
        'success'    => true,
        'message'    => 'Project update updated successfully!',
        'updateHtml' => $updateHtml,
        'update'     => $update,
    ]);
}


    public function edit($id)
    {
        $update = ProjectUpdate::findOrFail($id);
        
        // Retrieve any additional data required by the edit form
        $services = \App\Models\Service::all();

        // $departments = \App\Models\Department::all();
        $employees = \App\Models\Employee::all();
        $project = Project::find($update->project_id);

        // Optionally, you can use dd() to debug the variables:
        // dd($update, $services, $employees, $project);

        return view('project_updates.edit_modal', compact('update', 'services', 'employees', 'project'));
    }

    // Delete an update
    public function destroy(ProjectUpdate $projectUpdate)
    {
        $projectUpdate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Project update deleted successfully!'
        ]);
    }

    // (Optionally add an index method for AJAX search/filtering)
    public function index(Request $request)
    {
        // Implement search/filtering based on query parameters, e.g.:
        $query = ProjectUpdate::query();
        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%");
        }
        $updates = $query->orderBy('date', 'desc')->get();
        $html = view('project_updates._update_list', compact('updates'))->render();
        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }
}
