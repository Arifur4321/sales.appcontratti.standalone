<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\HeaderAndFooter;
use App\Models\createcontract;
use App\Models\Product;
use App\Models\VariableList; 
use App\Models\PriceList;
use Illuminate\Http\RedirectResponse;
use Dompdf\Dompdf;
use Dompdf\Options;
 

class createcontractController extends Controller
{    

      
    public function show()
    {
        $headerEntries = HeaderAndFooter::where('type', 'Header')->pluck('name', 'id')->toArray();
        $footerEntries = HeaderAndFooter::where('type', 'Footer')->pluck('name', 'id')->toArray();
        
        $variables = VariableList::all();

        $products = Product::all(); 
        return view('createcontract', compact('variables','products','headerEntries','footerEntries'));
    }


    // testing method
    public function HeaderOrFooter()
    {
        $headerEntries = HeaderAndFooter::where('type', 'Header')->pluck('name', 'id')->toArray();
        $footerEntries = HeaderAndFooter::where('type', 'Footer')->pluck('name', 'id')->toArray();
        
        return view('HeaderAndFooter', compact('headerEntries', 'footerEntries'));
    }

    //working one     
    // public function save(Request $request)
    // {
    //     // Validate the request
    //     $request->validate([
    //         'title' => 'required|string',
    //         'content' => 'required|string',
    //     ]);

    //     // Create a new createcontract instance and save it to the database
    //     $contract = new createcontract();
    //     $contract->title = $request->title;
    //     $contract->content = $request->content;
    //     $contract->save();

    //     return response()->json(['success' => true]);
    // }

    public function save(Request $request)
    {
        // Validate the request
        $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Validation for image upload
        ]);

        // Check if an image is uploaded
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            $imageUrl = asset('images/' . $imageName);
        }

        // Create a new Contract instance
        $contract = new Contract();
        $contract->title = $request->title;
        $contract->content = $request->content;
        $contract->image_url = $imageUrl ?? null; // Assign image URL if uploaded, null otherwise
        $contract->save();

        return response()->json(['success' => true]);
    }

    public function productforcreatepage()
    {
        $products = Product::all(); // Retrieve all products from the database to pass to createcontract
        return view('createcontract', compact('products'));
    }

   
    public function store(Request $request)
    {
      

        return response()->json(['success' => true]);
    }
    




    public function editmodal(Request $request)
    {
        // Validate the request
        $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
        ]);

        // Create a new createcontract instance and save it to the database
        $contract = new createcontract();
        $contract->title = $request->title;
        $contract->content = $request->content;
        $contract->save();

        return response()->json(['success' => true]);
    }

    public function generatePDF(Request $request)
    {
        // Get the content from the request
        $content = $request->input('content');

        // Set up Dompdf options
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        // Instantiate Dompdf with options
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($content);

        // Render the PDF
        $dompdf->render();

        // Output the PDF
        return $dompdf->stream('preview.pdf');
    }

  

  

}