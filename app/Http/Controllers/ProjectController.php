<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB; // Add this line

class ProjectController extends Controller
{
    public function saveProject(Request $request)
    {
        // Validate the request data
        $request->validate([
            'projectName' => 'required|string',
            'dueDate' => 'required|date',
            'status' => 'required|string',
            'team' => 'required|string',
            'action' => 'required|string',
        ]);

        // Save the project data
        Project::create($request->all());
        // Return a JSON response indicating success
        return response()->json(['message' => 'Project saved successfully'], 200);
    }

    public function editProject(Request $request, $id)
    {
        // Validate the request data

        $request->validate([
            'projectName' => 'required|string',
            'dueDate' => 'required|date',
            'status' => 'required|string',
            'team' => 'required|string',
        ]);

        // Retrieve the project by ID
        $project=Project::find($id);

        // Update the project data
        $project->update($request->all());

        // Return a JSON response indicating success
        return response()->json(['message' => 'Project updated successfully'], 200);
    }
 
    public function showProjects()
    {
        
        //$projects=Project::all();
       // $projects = DB::table('projects')->get();
       
        // Dump and die to print the contents of $projects and stop execution
        //dd($projects);
    
        // The following line will not be executed after dd()
      
        $projects = Project::paginate(9); // Adjust the number based on your preference
        return view('arifurtable', ['projects' => $projects]);
    }

    
    public function updateProject(Request $request, $id)
    {
        // Validation (you may customize the validation rules based on your needs)
        $request->validate([
            'projectName' => 'required|string',
            'dueDate' => 'required|date',
            'status' => 'required|string',
            'team' => 'required|string',
        ]);

        // Find the project by ID
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        // Update project details
        $project->update([
            'projectName' => $request->input('projectName'),
            'dueDate' => $request->input('dueDate'),
            'status' => $request->input('status'),
            'team' => $request->input('team'),
            // Include other fields if needed
        ]);

        // Return a JSON response indicating success
        return response()->json(['message' => 'Project updated successfully'], 200);
    }

    public function deleteProject($id)
    {
        // Find the project by ID and delete it
            $project = Project::find($id);
    
            $project->delete();
            return response()->json(['message' => 'deleted successfully'], 200);
    
    }

 
}
