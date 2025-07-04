<?php

namespace App\Http\Controllers;

use App\Models\ProjectDescriptionFile;
use Illuminate\Http\Request;

class ProjectDescriptionFileController extends Controller
{
    public function destroy(ProjectDescriptionFile $projectDescriptionFile)
    {
        $filePath = public_path('uploads/project_descriptions/' . $projectDescriptionFile->file_name);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $projectDescriptionFile->delete();

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully!'
        ]);
    }
     public function store(Request $request)
    {
        $validated = $request->validate([
            'project_description_id' => 'required|integer|exists:project_descriptions,id',
            'document_file' => 'required|file', // Adjust rules as needed
        ]);

        // Save file to a directory (e.g., public/uploads/project_descriptions)
        $file = $request->file('document_file');
        $filename = 'projdesc_' . time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('uploads/project_descriptions'), $filename);

        // Create a new record in the project_description_documents table
        $document = ProjectDescriptionFile::create([
            'project_description_id' => $validated['project_description_id'],
            'file_name' => $filename,
            'status' => 'active',
            'entry_by' => auth()->id() ?? null,
            'entered_date' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document uploaded successfully!',
            'document' => $document,
        ]);
    }
}
