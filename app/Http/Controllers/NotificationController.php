<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Service;



class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   // In NotificationController.php
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',  // The user to notify
            'title'   => 'required|string|max:255',
            'message' => 'required|string',
            'type'    => 'nullable|string|max:50',
        ]);

        $notification = \App\Models\Notification::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Notification saved successfully!',
            'notification' => $notification,
        ]);
    }


     public function markAsRead(Request $request)
    {
        $user = Auth::user();
        // Update all notifications with null read_at for this user
        $updated = Notification::where('user_id', $user->id)
                    ->whereNull('read_at')
                    ->update(['read_at' => now()]);
        
        return response()->json(['success' => true, 'updated' => $updated]);
    }
    public function allNotifications(Request $request)
{
    $user = Auth::user();

    // Mark all unread notifications for this user as read.
    \App\Models\Notification::where('user_id', $user->id)
        ->whereNull('read_at')
        ->update(['read_at' => now()]);

    // Start building the query for the logged in user.
    $query = \App\Models\Notification::where('user_id', $user->id);

    // Filter by date range if provided.
    if ($request->filled('start_date')) {
        $query->whereDate('created_at', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
        $query->whereDate('created_at', '<=', $request->end_date);
    }

    // Filter by project if provided.
    if ($request->filled('project_id')) {
        $project = \App\Models\Project::find($request->project_id);
        if ($project) {
            $projectName = $project->name;
            // We assume the message contains the project name in the pattern: "(Project Name |"
            $query->where('message', 'like', '%(' . $projectName . ' |%');
        }
    }

    // Filter by service if provided.
    if ($request->filled('service_id')) {
        $service = \App\Models\Service::find($request->service_id);
        if ($service) {
            $serviceName = $service->name;
            // We assume the message contains the service name in the pattern: "| Service Name"
            $query->where('message', 'like', '%| ' . $serviceName . '%');
        }
    }

    // Get notifications ordered by created_at descending.
    $notifications = $query->orderBy('created_at', 'desc')->get();

    // Retrieve all projects and services for filter dropdowns.
    $projects = \App\Models\Project::all();
    $services = \App\Models\Service::all();

    return view('notifications.index', compact('notifications', 'projects', 'services'));
}

}
