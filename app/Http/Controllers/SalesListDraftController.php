<?php

namespace App\Http\Controllers;

use App\Models\SalesListDraft;

use App\Models\Contract;
use App\Models\VariableList; 
use App\Models\Product;
use App\Models\HeaderAndFooter;
use App\Models\contractvariablecheckbox; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
//For the pricelist table
use App\Models\PriceList;
use App\Models\SalesDetails; 
use App\Models\ProductToSales;

class SalesListDraftController extends Controller
{

    public function showSalesList($newEntryId)
    {
        // Get the logged-in user
        $user = Auth::user();

        // Fetch data from the sales_list_draft table where company_id matches the logged-in user's company_id in sales_details table
        $salesData = SalesListDraft::select('sales_list_draft.id', 'product_name', 'contract_name', 'recipient_email', 'variable_json')
                                    ->join('sales_details', 'sales_details.company_id', '=', 'sales_list_draft.company_id')
                                    ->where('sales_list_draft.id', $newEntryId)
                                    ->where('sales_details.company_id', $user->company_id)
                                    ->get();
        
        return view('Send-New-Contracts', compact('salesData'));
    }

    // for check button  
    public function getSalesData()
    {
        // Get the logged-in user
        $user = Auth::user();
    
        // Fetch unique data from the sales_list_draft table
        $salesData = SalesListDraft::select('sales_list_draft.id', 'product_name', 'contract_name', 'recipient_email', 'variable_json')
                                    ->distinct()
                                    ->join('sales_details', 'sales_details.company_id', '=', 'sales_list_draft.company_id')
                                    ->where('sales_details.company_id', $user->company_id)
                                    ->get();
        
        return response()->json($salesData);
    }
    

    // public function showSalesList($newEntryId)
    // {
    //     // Get the logged-in user
    //     $user = Auth::user();

    //     // Fetch data from the sales_list_draft table where company_id matches the logged-in user's company_id
    //     $salesData = SalesListDraft::select('id', 'product_name', 'contract_name', 'recipient_email', 'variable_json')
    //                                 ->where('id', $newEntryId)
    //                                 ->where('company_id', $user->company_id)
    //                                 ->get();
        
    //     return view('Send-New-Contracts', compact('salesData'));
    // }

    // public function getSalesData()
    // {
    //     // Get the logged-in user
    //     $user = Auth::user();

    //     // Fetch data from the sales_list_draft table where company_id matches the logged-in user's company_id
    //     $salesData = SalesListDraft::select('id', 'product_name', 'contract_name', 'recipient_email', 'variable_json')
    //                                 ->where('company_id', $user->company_id)
    //                                 ->get();
        
    //     return response()->json($salesData);
    // }



    public function showSendNewContractsPage($id)
    {
        // Retrieve the SalesListDraft entry based on the provided ID
        $entry = SalesListDraft::find($id);

        if (!$entry) {
            // If the entry is not found, handle the error accordingly
            return redirect()->route('error.page')->with('error', 'Entry not found.');
        }

        // Pass the entry data to the view
        return view('Send-New-Contracts', ['entry' => $entry]);
    }

     

    public function updateVariableJson(Request $request)
    {
        // Retrieve the ID and variableData from the request
        $id = $request->input('id');
        $variableData = $request->input('variableData');

        // Find the record in the sales_list_draft table by ID
        $salesListDraft = SalesListDraft::find($id);

        if (!$salesListDraft) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        // Update the variable_json column with the new data
        $salesListDraft->variable_json = $variableData;
        $salesListDraft->save();

        return response()->json(['message' => 'Variable JSON data updated successfully']);
    }

 

    public function getVariableJson(Request $request)
    {
        // Get the ID from the request
        $id = $request->input('id');

        // Query the sales_list_draft table based on the ID
        $variableJsonData = SalesListDraft::find($id);

        if (!$variableJsonData) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        // Return both variable_json and contract_id
        return response()->json([
            'variable_json' => $variableJsonData->variable_json,
            'contract_id' => $variableJsonData->contract_id
        ]);
    }

    // for delete row in sales draft list table
    public function destroy($id)
    {
        $salesListDraft = SalesListDraft::find($id);

        if (!$salesListDraft) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        // Delete the record
        $salesListDraft->delete();

        return response()->json(['message' => 'Record deleted successfully']);
    }

    public function edit($id)
    {
        // Logic to retrieve and display data for editing
        $salesListDraft = SalesListDraft::find($id);
        $productName = $salesListDraft ? $salesListDraft->product_name : '';   
        $ContractName = $salesListDraft ? $salesListDraft->contract_name : ''; 
        return view('Edit-New-Contracts', ['id' => $id, 'productName' => $productName , 'ContractName' => $ContractName]);
    }

    
 

    // public function showAll()
    // {
    //     $sellerName = Auth::user()->name;

    //     $salesDetail = SalesDetails::where('name', $sellerName)->first();
    //     if (!$salesDetail) {
    //         return response()->json(['status' => 'error', 'message' => 'Sales details not found']);
    //     }

    //     $salesId = $salesDetail->id;
    //     $salesName = $salesDetail->name;

    //     if (  $salesId) {
    //         // Query SalesListDraft table based on $salesId
    //         $salesListDraft = SalesListDraft::where('sales_id', $salesId)->get();

    //         return view('Your-Lists', compact('salesListDraft'));
    //     }

    //     return response()->json(['status' => 'error', 'message' => 'Unauthorized access']);
        
    // }


    public function showAll()
    {
        // Retrieve the authenticated user's email
        $userEmail = Auth::user()->email;

        // Fetch the sales detail using the authenticated user's email
        $salesDetail = SalesDetails::where('email', $userEmail)->first();
        if (!$salesDetail) {
            return response()->json(['status' => 'error', 'message' => 'Sales details not found']);
        }

        $salesId = $salesDetail->id;

        if ($salesId) {
            // Query SalesListDraft table based on $salesId
            $salesListDraft = SalesListDraft::where('sales_id', $salesId)->get();

            return view('Your-Lists', compact('salesListDraft'));
        }

        return response()->json(['status' => 'error', 'message' => 'Unauthorized access']);
    }



     
    // public function createNewEntry(Request $request)
    // {
    //     // Get the name of the authenticated user
    //     $sellerName = Auth::user()->name;

    //     // Retrieve sales details based on the seller's name
    //     $salesDetail = SalesDetails::where('name', $sellerName)->first();

    //     // Check if sales details are found
    //     if (!$salesDetail) {
    //         return response()->json(['status' => 'error', 'message' => 'Sales details not found']);
    //     }

    //     // Retrieve the sales ID
    //     $salesId = $salesDetail->id;  

    //     $salesCompanyID = $salesDetail->company_id  ;

    //     // Create a new entry in the SalesListDraft table with sales_id filled
    //     $newEntry = SalesListDraft::create([
    //         'sales_id' => $salesId,
    //         'company_id' => $salesCompanyID ,
    //     ]);

    //     // Return a success response
    //     return response()->json(['message' => 'New entry created successfully.', 'entry' => $newEntry]);
    // }


    public function createNewEntry(Request $request)
    {
        // Get the email of the authenticated user
        $userEmail = Auth::user()->email;

        // Retrieve sales details based on the user's email
        $salesDetail = SalesDetails::where('email', $userEmail)->first();

        // Check if sales details are found
        if (!$salesDetail) {
            return response()->json(['status' => 'error', 'message' => 'Sales details not found']);
        }

        // Retrieve the sales ID and company ID
        $salesId = $salesDetail->id;
        $salesCompanyID = $salesDetail->company_id;

        // Create a new entry in the SalesListDraft table with sales_id and company_id
        $newEntry = SalesListDraft::create([
            'sales_id' => $salesId,
            'company_id' => $salesCompanyID,
        ]);

        // Return a success response
        return response()->json(['message' => 'New entry created successfully.', 'entry' => $newEntry]);
    }

   
}