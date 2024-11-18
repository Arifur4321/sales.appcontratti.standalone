<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MemberController extends Controller
{
   
    public function show()
    {
        $projects = Project::all();

        // Return the projects as JSON response
        return response()->json($projects);
 
    }
}
