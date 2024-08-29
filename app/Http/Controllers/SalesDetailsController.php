<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\SalesDetails; 
use App\Models\ProductToSales;

class SalesDetailsController extends Controller

{
        public function checkUnique(Request $request)
        {
            // Retrieve field and value from the request
            $field = $request->input('field');
            $value = $request->input('value');

            // Check uniqueness based on the field
            switch ($field) {
                case 'nickname':
                    $exists = SalesDetails::where('nickname', $value)->exists();
                    break;
               // case 'password':
      
               //     $exists = false; // Passwords should not be checked for uniqueness
               //     break;
                case 'phone':
                    $exists = SalesDetails::where('phone', $value)->exists();
                    break;
                case 'email':
                    $exists = SalesDetails::where('email', $value)->exists();
                    break;
                default:
                    $exists = false;
                    break;
            }

            // Return JSON response indicating uniqueness
            return response()->json(['unique' => !$exists]);
        }


        public function displayChecked(Request $request)
        {
            // Fetch all products
            $products = Product::all();
    
            // Get the sales ID from the request
            $salesId = $request->input('salesDetailsId');
    
            // Loop through each product and check if it's selected
            foreach ($products as $product) {
                $product->isSelected = ProductToSales::where('product_id', $product->id)
                                                    ->where('sales_id', $salesId)
                                                    ->exists();
            }
    
            // Return JSON data
            return response()->json(['products' => $products]);
        }
     
        public function updateProductStatus(Request $request)
        {
            // Validate incoming request data if needed
    
            $productId = $request->input('product_id');
            $salesDetailsId = $request->input('sales_details_id');
            $isChecked = $request->input('is_checked');
    
            // If checkbox is checked, add the product to the database
            if ($isChecked) {
                ProductToSales::create([
                    'product_id' => $productId,
                    'sales_id' => $salesDetailsId,
                ]);
            } else { // If checkbox is unchecked, remove the product from the database
                ProductToSales::where('product_id', $productId)
                              ->where('sales_id', $salesDetailsId)
                              ->delete();
            }
    
            // You can return a response to indicate success or failure
            return response()->json(['message' => 'Product status updated successfully']);
        }
    
    //for manytomany table
    public function saveProductToSales(Request $request)
    {
        // Retrieve sales ID from the request or session, assuming you have it available
        $salesId = $request->input('sales_details_id');

        // Retrieve product IDs from the request
        $productIds = $request->input('product_ids');

        // Loop through each product ID and save it to product_to_sales table
        foreach ($productIds as $productId) {
            ProductToSales::create([
                'product_id' => $productId,
                'sales_id' => $salesId,
            ]);
        }

        // You can return a response to indicate success or failure
        return response()->json(['message' => 'Products saved to sales successfully']);
    }
    
    public function editSales($id)
    {
       
        $products = Product::all(); // Fetch all products from the database
        $salesDetails = SalesDetails::findOrFail($id);  //  fetch all sales details data from database  
        return view('Sales-Details', compact('products','salesDetails'));
    }
 
 
  
    
    
    public function Productshow()
    {
        $products = Product::all(); // Fetch all products from the database
       // $salesDetails = SalesDetails::all();  //  fetch all sales details data from database  

        $salesDetails = new  salesDetails();
        $salesDetails -> name = "Write your Name";
        $salesDetails -> surname = "Write your SurName";
        $salesDetails -> save();
        $salesDetailsid =  $salesDetails->id;
        
       // return view('Sales-Details', compact('products','salesDetails')); 
       echo '<script>window.location.href = "/Sales-Details/' . $salesDetailsid . '";</script>';
    }
 
        public function show()
        {
            $salesDetails = SalesDetails::all(); // Fetch all sales details from the database
            return view('Sales-Lists', compact('salesDetails'));
        }
   
        // public function save(Request $request, $id = null)
        // {
        //     // If $id is provided, it's an edit operation
        //     if ($id) {
        //         // Find the SalesDetails record to edit
        //         $salesDetails = SalesDetails::findOrFail($id);
    
        //         // Update the record with the provided data
        //         $salesDetails->update([
        //             'name' => $request->name,
        //             'surname' => $request->surname,
        //             'nickname' => $request->nickname,
        //             'phone' => $request->phone,
        //             'email' => $request->email,
        //             'password' => $request->password,
        //             'description' => $request->description,
        //         ]);
        //     } else {
        //         // It's a new entry creation
        //         // Validate the incoming request data
        //         $validatedData = $request->validate([
        //             'name' => 'required|string|max:255',
        //             'surname' => 'required|string|max:255',
        //             'nickname' => 'required|string|max:255',
        //             'phone' => 'required|string|max:255',
        //             'email' => 'required|string|email|max:255',
        //             'password' => 'required|string|max:255',
        //             'description' => 'required|string',
        //         ]);
    
        //         // Create a new sales details instance and save it to the database
        //         SalesDetails::create($validatedData);
        //     }
    
        //     // Flash success message to session
        //     $request->session()->flash('success', 'Sales details saved successfully.');
    
        //     // Redirect back
        //     return redirect()->back();
        // }


        public function save(Request $request, $id = null)
        {
            // If $id is provided, it's an edit operation
            if ($id) {
                // Find the SalesDetails record to edit
                $salesDetails = SalesDetails::findOrFail($id);

                 // Check uniqueness of nickname, phone, and email before updating

                if ($request->email !== $salesDetails->email) {
                    if (SalesDetails::where('email', $request->email)->exists()) {
                        return redirect()->back()->withErrors(['email' => 'Email already exists.'])->withInput();
                   }
               }

                if ($request->nickname !== $salesDetails->nickname) {
                    if (SalesDetails::where('nickname', $request->nickname)->exists()) {
                        return redirect()->back()->withErrors(['nickname' => 'Nickname already exists.'])->withInput();
                    }
                }

                // if ($request->password !== $salesDetails->password) {
                //     if (SalesDetails::where('password', $request->password)->exists()) {
                //         return redirect()->back()->withErrors(['password' => 'password number already exists.'])->withInput();
                //     }
                // }

                 
                // Update the record with the provided data
                $salesDetails->update([
                    'name' => $request->name,
                    'surname' => $request->surname,
                    'nickname' => $request->nickname,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'password or email value
                    password' => $request->newpassword,
                    'description' => $request->description,
                ]);
            } else {
                // It's a new entry creation
                // Validate the incoming request data
                $validatedData = $request->validate([
                    'name' => 'required|string|max:255',
                    'surname' => 'required|string|max:255',
                    'nickname' => 'required|string|max:255|unique:sales_details',
                    'phone' => 'required|string|max:255|unique:sales_details',
                    'email' => 'required|string|email|max:255|unique:sales_details',
                    'password' => 'required|string|max:255',
                    'description' => 'required|string',
                ]);

                // Create a new sales details instance and save it to the database
                SalesDetails::create($validatedData);
            }

            // Flash success message to session
            $request->session()->flash('success', 'Sales details saved successfully.');

            // Redirect back
            return redirect()->back();
        }


        public function destroy($id)
        {
            $SalesDetails = SalesDetails::findOrFail($id);
            $SalesDetails->delete();
    
            return response()->json(['message' => 'Price list deleted successfully']);
        }

 
         
}
