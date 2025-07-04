<?php

namespace App\Http\Controllers;

use App\Models\ProjectDescription;
use App\Models\ProjectDescriptionFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectDescriptionController extends Controller
{
    // public function index()
    // {
    //     // For example, you might retrieve all descriptions or those related to a particular project.
    //     $descriptions = ProjectDescription::all();
    //     return view('project_descriptions.index', compact('descriptions'));
    // }
    // Store a new description along with file uploads (if any)
    public function store(Request $request)
{
    $validated = $request->validate([
        'project_description_id' => 'required|integer|exists:project_descriptions,id',
        'document_file' => 'required|file',
    ]);

    $file = $request->file('document_file');
    $filename = 'projdesc_' . time() . '_' . $file->getClientOriginalName();
    $file->move(public_path('uploads/project_descriptions'), $filename);

    $document = ProjectDescriptionDocument::create([
        'project_description_id' => $validated['project_description_id'],
        'file_name'              => $filename,
        'status'                 => 'active',
        'entry_by'               => auth()->id() ?? null,
        'entered_date'           => now(),
    ]);

    // Render a partial view for the new document item.
    $documentHtml = view('project_descriptions._document_item', compact('document'))->render();

    return response()->json([
        'success' => true,
        'message' => 'Document uploaded successfully!',
        'project_description_id' => $validated['project_description_id'],
        'documentHtml' => $documentHtml,
    ]);
}
public function storedata(Request $request)
    {
        $validated = $request->validate([
            'project_id'         => 'required|integer|exists:projects,id',
            'project_service_id' => 'nullable|integer|exists:project_services,id',
            'title'              => 'required|string|max:255',
            'details'            => 'nullable|string',
            'status'             => 'required|in:active,inactive',
            'entered_date'       => 'nullable|date',
        ]);

        $description = ProjectDescription::create($validated);

        // Handle multiple file uploads if provided
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $filename = 'projdesc_' . time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/project_descriptions'), $filename);

                ProjectDescriptionFile::create([
                    'project_description_id' => $description->id,
                    'file_name'              => $filename,
                    'status'                 => 'active',
                    'entry_by'               => auth()->id() ?? null,
                    'entered_date'           => now(),
                ]);
            }
        }

        $html = view('project_descriptions._description_item', compact('description'))->render();

        return response()->json([
            'success' => true,
            'message' => 'Project description added successfully!',
            'descriptionHtml' => $html,  // Rendered HTML snippet from a partial view
            'project_service_id' => $validated['project_service_id'],
        ]);

    }




  public function edit(ProjectDescription $projectDescription)
{
    $project = $projectDescription->project;
    $projectServices = $project->projectServices;
    $services = \App\Models\Service::all();
    $employees = \App\Models\Employee::all();
    
    return view('project_descriptions.edit_modal', compact('projectDescription', 'project', 'projectServices', 'services', 'employees'));
}



    // Update an existing description (and update file uploads if needed)
    // ProjectDescriptionController.php

public function update(Request $request, ProjectDescription $projectDescription)
{
    $validated = $request->validate([
        'project_id'         => 'required|integer|exists:projects,id',
        'project_service_id' => 'nullable|integer|exists:project_services,id',
        'title'              => 'required|string|max:255',
        'details'            => 'nullable|string',
        'status'             => 'required|in:active,inactive',
        'entered_date'       => 'nullable|date',
    ]);

    $projectDescription->update($validated);

    // Eager-load the files relationship (if not already loaded)
    $projectDescription->load('files');

    // Render the updated partial view for this description
    $descriptionHtml = view('project_descriptions._description_item', ['description' => $projectDescription])->render();

    return response()->json([
        'success'         => true,
        'message'         => 'Project description updated successfully!',
        'descriptionHtml' => $descriptionHtml,
    ]);
}
public function storeDocument(Request $request)
{
    $validated = $request->validate([
        'project_description_id' => 'required|integer|exists:project_descriptions,id',
        'document_file'          => 'required|file|max:20480', // max 20MB
    ]);

    // Process the uploaded file.
    $file = $request->file('document_file');
    // Create a unique file name.
    $filename = 'projdesc_' . time() . '_' . $file->getClientOriginalName();
    // Move the file to the public uploads directory.
    $destinationPath = public_path('uploads/project_description_files');
    $file->move($destinationPath, $filename);

    // Create the file record.
    $document = ProjectDescriptionFile::create([
        'project_description_id' => $validated['project_description_id'],
        'file_name'              => $file->getClientOriginalName(), // you can store original name or $filename as needed
        'status'                 => 'active',
        'entry_by'               => auth()->id() ?? null,
        'entered_date'           => now(),
    ]);

    // Render a partial view snippet so that the file can be appended without reloading.
    $documentHtml = view('project_descriptions._document_item', compact('document'))->render();

    return response()->json([
        'success'      => true,
        'message'      => 'Document uploaded successfully!',
        'documentHtml' => $documentHtml,
    ]);
}



    // Delete a description and its associated files
    public function destroy(ProjectDescription $projectDescription)
    {
        // Delete associated files from disk and DB
        foreach ($projectDescription->files as $file) {
            $filePath = public_path('uploads/project_descriptions/' . $file->file_name);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $file->delete();
        }
        $projectDescription->delete();

        return response()->json([
            'success' => true,
            'message' => 'Project description and associated files deleted successfully!'
        ]);
    }
}
