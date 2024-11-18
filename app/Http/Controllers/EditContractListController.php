<?php

namespace App\Http\Controllers;

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

class EditContractListController extends Controller
{

   // to get price id
    // public function getPriceId(Request $request)
    // {
    //     $contractID = $request->input('contractID');

    //     // Retrieve the contract by contractID
    //     $contract = Contract::find($contractID);

    //     if ($contract) {
    //         // If contract is found, get the price_id
    //         $price_id = $contract->price_id;

    //         // Retrieve the price details from the PriceList table using the price_id
    //         $price = PriceList::find($price_id);

    //         if ($price) {
    //             // If price is found, return the price_id and price name
    //             return response()->json([
    //                 'price_id' => $price_id,
    //                 'pricename' => $price->pricename
    //             ]);
    //         } else {
    //             // If price is not found, return an error message
    //             return response()->json(['error' => 'Price details not found.'], 404);
    //         }
    //     } else {
    //         // If contract is not found, return an error message
    //         return response()->json(['error' => 'Contract not found.'], 404);
    //     }
    // }


 
    public function insertMandatoryStatus(Request $request)
    {
        $contractId = $request->input('contractId');
        $variableId = $request->input('variable_id');
        $mandatory = $request->input('mandatory');

        // Find the record by contractId and variableId
        $record = ContractVariableCheckbox::where('ContractID', $contractId)
                                        ->where('VariableID', $variableId)
                                        ->first();

        // If the record exists, update the mandatory field, else create a new record
        if ($record) {
            $record->Mandatory = $mandatory;
            $record->save();
        } else {
            $newRecord = new ContractVariableCheckbox();
            $newRecord->ContractID = $contractId;
            $newRecord->VariableID = $variableId;
            $newRecord->Mandatory = $mandatory;
            $newRecord->save();
        }

        return response()->json(['success' => true]);
    }


       public function getPriceId(Request $request)
        {
            $contractID = $request->input('contractID');

            // Retrieve the contract by contractID
            $contract = Contract::find($contractID);

            if ($contract) {
                // If contract is found, return the price_id
                return response()->json(['price_id' => $contract->price_id] );
            } else {
                // If contract is not found, return an error message
                return response()->json(['error' => 'Contract not found.'], 404);
            }
        }

        public function getProductId(Request $request)
        {
            $contractID = $request->input('contractID');

            // Retrieve the contract by contractID
            $contract = Contract::find($contractID);

            if ($contract) {
                // If contract is found, return the price_id
                return response()->json(['product_id' => $contract->product_id] );
            } else {
                // If contract is not found, return an error message
                return response()->json(['error' => 'Contract not found.'], 404);
            }
        }

    //insert foreign key price id 
    public function insertPriceId(Request $request) {
        // Retrieve the contractId and productId from the request
        $contractId = $request->input('contractId');
        $productId = $request->input('productId');
    
        try {
            // Find the contract by its ID
            $contract = Contract::findOrFail($contractId);
    
            // Update the contract with the new price ID
            $contract->price_id = $productId; // Assuming $productId is the price ID
    
            // Save the changes to the database
            $contract->save();
    
            // Return a success response
            return response()->json(['message' => 'Price ID inserted successfully'], 200);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json(['error' => 'Failed to update contract.'], 500);
        }
    }


    public function saveSelectedProduct(Request $request)
    {
        // Get contract ID and product ID from the request
        $contractId = $request->input('contractId');
        $productId = $request->input('productId');
    
        try {
            // Find the contract by its ID
            $contract = Contract::findOrFail($contractId);
    
            // Update the contract with the new price ID
            $contract->product_id = $productId; // Assuming $productId is the price ID
    
            // Save the changes to the database
            $contract->save();
    
            // Return a success response
            return response()->json(['message' => 'Price ID inserted successfully'], 200);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json(['error' => 'Failed to update contract.'], 500);
        }

    }

    public function deleteSelectedProduct(Request $request)
    {
        // Get contract ID and product ID from the request
        $contractId = $request->input('contractId');
        $productId = $request->input('productId');

        try {
            $contract = Contract::findOrFail($contractId);
            $contract->product_id = null; // Assuming product_id is nullable
            $contract->save();
    
            return response()->json(['message' => 'Price ID deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete price ID.'], 500);
        }
    }

    
    //for delete foreign price id
    public function deletePriceId(Request $request) {
        $contractId = $request->input('contractId');
    
        try {
            $contract = Contract::findOrFail($contractId);
            $contract->price_id = null; // Assuming price_id is nullable
            $contract->save();
    
            return response()->json(['message' => 'Price ID deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete price ID.'], 500);
        }
    }
    

   // to insert contractvariablecheckbox when pop up variable is checked

       public function insertContractVariable(Request $request)
       {
           // Retrieve contract_id and variable_id from the request
           $contractId = $request->input('contract_id');
           $variableId = $request->input('variable_id');
           $Mandatory = $request->input('Mandatory');
   
           // Insert data into the contractvariablecheckbox table
           $contractVariable = new contractvariablecheckbox();
           $contractVariable->LoggedinUser = Auth::user()->name;
           $contractVariable->ContractID = $contractId; // Update to match the actual column name in the table
           $contractVariable->VariableID = $variableId;
         //  $contractVariable->Mandatory = $Mandatory; // Update to match the actual column name in the table
           $contractVariable->save();
   
           // You can return a response if needed
           return response()->json(['message' => 'Data inserted successfully']);
       }

       //delete the row from contractvariablecheckbox when unchecked
       public function deleteContractVariable(Request $request)
       {
           // Retrieve the ContractID and VariableID from the request
           $contractId = $request->input('contract_id');
           $variableId = $request->input('variable_id');
       
           // Check if the checkbox is unchecked
           if (!$request->has('checked') || $request->input('checked') !== 'true') {
               // Delete rows from the database table where ContractID and VariableID match
               ContractVariableCheckbox::where('ContractID', $contractId)
                   ->where('VariableID', $variableId)
                   ->delete();
       
               // Respond with a success message or any necessary data
               return response()->json(['message' => 'Contract variable deleted successfully']);
           }
       
           // Respond with a message indicating that the checkbox is checked
           return response()->json(['message' => 'Checkbox is checked, no action taken']);
       }
       
    //    public function deleteContractVariable(Request $request)
    //    {
    //        // Retrieve the ContractID and VariableID from the request
    //        $contractId = $request->input('ContractID');
    //        $variableId = $request->input('VariableID');
   
    //        // Delete rows from the database table where ContractID and VariableID match
    //        ContractVariableCheckbox::where('ContractID', $contractId)
    //            ->where('VariableID', $variableId)
    //            ->delete();
   
    //        // Respond with a success message or any necessary data
    //        return response()->json(['message' => 'Contract variable deleted successfully']);
    //    }

       // checked the variableID corresponding to contractID
       public function checkedVariable(Request $request)
        {
            $contractID = $request->input('contract_id');

            // Assuming ContractVariableCheckbox is the correct model for the contractvariablecheckbox table
            $variableIDs = ContractVariableCheckbox::where('ContractID', $contractID)->pluck('VariableID')->toArray();

          //  $MandatoryIDs = ContractVariableCheckbox::where   do the query based on ContractID and  VariableID to get Mandatory value true or false 

            return response()->json($variableIDs  );
        }

      

        public function getMandatoryFieldValues(Request $request)
        {
            $contractID = $request->input('contract_id');

            // Get mandatory field values for the given contract ID
            $mandatoryFieldValues = ContractVariableCheckbox::where('ContractID', $contractID)
                ->pluck('Mandatory', 'VariableID')
                ->toArray();

            return response()->json($mandatoryFieldValues);
        }

   // ----------------
 
    // testing method 
    public function showvariable()
    {  
        $loggedInUserName = auth::user()->name ;
        $headerEntries = HeaderAndFooter::where('type', 'Header')->pluck('name', 'id')->toArray();
        $footerEntries = HeaderAndFooter::where('type', 'Footer')->pluck('name', 'id')->toArray();
        $variables = VariableList::all();
        return view('Edit-ContractList', compact('variables','headerEntries', 'footerEntries', 'loggedInUserName' ));
    }

     
    public function edit($id)
    {
       
        $contract = Contract::findOrFail($id);
        $variables = VariableList::all();
        $products = Product::all(); 
        $headerEntries = HeaderAndFooter::where('type', 'Header')->pluck('name', 'id')->toArray();
        $footerEntries = HeaderAndFooter::where('type', 'Footer')->pluck('name', 'id')->toArray();
        $priceLists = PriceList::all();
        return view('Edit-ContractList', compact('contract', 'variables', 'products','headerEntries', 'footerEntries','priceLists'));
    }
    public function updateContract(Request $request)
    {
        // Validate the request data
        $request->validate([
            'id' => 'required|exists:contracts,id',
            'contract_name' => 'required|string',
            'editor_content' => 'required|string',
            // Add validation rules for other fields if needed
        ]);
        
        // Find the contract by its ID
        $contract = Contract::findOrFail($request->id);
        
        // Update contract details
        $contract->contract_name = $request->contract_name;
        $contract->editor_content = $request->editor_content;
        // Update other fields as needed
        
        // Save the updated contract
        $contract->save();
        
        // Redirect back with a success message or return a response
        return response()->json(['message' => 'Contract updated successfully']);
    }
}
