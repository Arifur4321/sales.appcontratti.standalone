 <?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes(['verify' => true]);

//Route::get('/', [App\Http\Controllers\HomeController::class, 'root'])->name('root');

Route::get('/', [App\Http\Controllers\HomeController::class, 'contractList'])->name('root');

// You can still define the /Contract-List route if you want both URLs to be functional
Route::get('/Your-Lists', [App\Http\Controllers\HomeController::class, 'contractList'])->name('contract.list');

// customers route
Route::get('/customers', [App\Http\Controllers\CustomerController::class, 'index'])->name('customers.list');

//Update User Details
Route::post('/update-profile/{id}', [App\Http\Controllers\HomeController::class, 'updateProfile'])->name('updateProfile');
Route::post('/update-password/{id}', [App\Http\Controllers\HomeController::class, 'updatePassword'])->name('updatePassword');

//Language Translation
Route::get('index/{locale}', [App\Http\Controllers\HomeController::class, 'lang']);

// routes/web.php
 
Route::post('/save-project', [App\Http\Controllers\ProjectController::class, 'saveProject']);

Route::post('/edit-variable/{id}', [App\Http\Controllers\ProductController::class, 'update'])->name('edit-variable');


// routes/web.php
Route::get('/arifurtable', [App\Http\Controllers\ProjectController::class, 'showProjects']);

// for Contractlist page  in office -----------------


 
// Add this route to handle the signed PDF download as standalone------------------------

Route::get('sign-document/{id}', [App\Http\Controllers\SignatureController::class, 'showSignDocument'])->name('sign.document');
Route::post('sign-document/{id}', [App\Http\Controllers\SignatureController::class, 'submitSignature']);
Route::get('download-signed-pdf/{id}', [App\Http\Controllers\SignatureController::class, 'downloadSignedPdf'])->name('download.signed.pdf');
// Route for one-by-one signature submission
Route::post('/sign-document-onebyone/{id}', [App\Http\Controllers\SignatureController::class, 'submitSignatureOneByOne'])->name('sign.document.onebyone');
// Route to view the signed document
Route::get('/view-signed-document/{id}', [App\Http\Controllers\SignatureController::class, 'viewSignedDocument'])->name('view.signed.document');

//Route::get('view-signed-document/{id}', [App\Http\Controllers\SignatureController::class, 'showSignDocument'])->name('view.signed.document');

Route::get('/contract/get-signed-pdf-url/{id}', [App\Http\Controllers\ProductController::class, 'getSignedPdfUrl']);
Route::get('/contract/download-signed-pdf/{id}', [App\Http\Controllers\ProductController::class, 'downloadSignedPdf'])->name('download.signed.pdf');
Route::post('/sign-document/{id}/partially', [App\Http\Controllers\SignatureController::class, 'submitSignaturePartially'])->name('sign.document.partially');

Route::get('sign-document/{id}', [App\Http\Controllers\SignatureController::class, 'loadDocumentForSigning'])->name('sign.document');

Route::post('/submit-signature/{id}', [App\Http\Controllers\SignatureController::class, 'submitSignature'])->name('submit.signature');

Route::post('/send-confirmation-email/{id}', [App\Http\Controllers\SignatureController::class, 'sendConfirmationEmail'])->name('send.confirmation.email');

//-----------------------------

Route::post('/update-variable-json', [App\Http\Controllers\SalesListDraftController::class, 'updateVariableJson'])->name('update-variable-json');

 
Route::get('/get-variable-json', [App\Http\Controllers\SalesListDraftController::class, 'getVariableJson'])->name('get-variable-json');

Route::delete('/sales-list-draft/{id}', [App\Http\Controllers\SalesListDraftController::class, 'destroy']);
 
Route::get('/Edit-New-Contracts/{id}', [App\Http\Controllers\SalesListDraftController::class, 'edit']);

Route::get('/Send-New-Contracts/{id}', [App\Http\Controllers\SalesListDraftController::class, 'showSendNewContractsPage'])->name('send.new.contracts');



Route::get('/Your-Lists', [App\Http\Controllers\SalesListDraftController::class, 'showAll']);

Route::post('/create-new-entry', [App\Http\Controllers\SalesListDraftController::class, 'createNewEntry']);


Route::get('/get-all-priceLists', [App\Http\Controllers\ProductController::class, 'getAllPriceLists']);

Route::post('/save-pricejson-data', [App\Http\Controllers\ProductController::class, 'savePriceJsonData']);







Route::get('/generate-pdf-new', [App\Http\Controllers\ProductController::class, 'generateHtmlToPDF']); 

Route::post('/delete-pdf', [App\Http\Controllers\ProductController::class, 'deletePdf']);

Route::post('/get-pdf-sales', [App\Http\Controllers\ProductController::class, 'generatePdfforSales']);

// for Hello Sign route 

Route::post('/send-document-for-signature', [App\Http\Controllers\ProductController::class, 'sendDocumentForSignature']);

 
Route::post('/hellosign-webhook', [App\Http\Controllers\HelloSignWebhookController::class, 'handle']);

Route::get('/check-document-status/{envelopeId}', [App\Http\Controllers\SignatureController::class, 'checkDocumentStatus']);

Route::post('/check-signature-status', [ App\Http\Controllers\SignatureController::class, 'checkSignatureStatus']);

Route::get('docusign',[App\Http\Controllers\DocusignController::class, 'index'])->name('docusign');
Route::get('connect-docusign',[App\Http\Controllers\DocusignController::class, 'connectDocusign'])->name('connect.docusign');
Route::get('docusign/callback',[App\Http\Controllers\DocusignController::class,'callback'])->name('docusign.callback');
Route::get('sign-document',[App\Http\Controllers\DocusignController::class,'signDocument'])->name('docusign.sign');

Route::get('/get-priceLists-payment', [App\Http\Controllers\ProductController::class, 'getPriceListsPayment']);


Route::get('/fetch-price-json/{id}', [App\Http\Controllers\ProductController::class, 'fetchPriceJson'])->name('fetch.price.json');


Route::get('/get-editor-content', [App\Http\Controllers\ProductController::class, 'geteditorcontent']);

Route::get('/get-all-variables', [App\Http\Controllers\ProductController::class, 'getallvariables']);

Route::get('/get-all-edited-variables', [App\Http\Controllers\ProductController::class, 'getallEditvariables']);


Route::post('/fetch-mandatory-fields', [App\Http\Controllers\ProductController::class, 'fetchMandatoryFields']);



Route::get('/get-variables-for-Edit', [App\Http\Controllers\ProductController::class, 'getvariablesforEdit']);


Route::post('/save-variable-data', [App\Http\Controllers\ProductController::class, 'saveVariableData']);

Route::post('/save-edited-variable-data', [App\Http\Controllers\ProductController::class, 'saveEditedVariableData']);
 
//testing
Route::post('/update-variable-data', [App\Http\Controllers\ProductController::class, 'updateVariableData']);

Route::get('/sales-list-draft/{id}', [App\Http\Controllers\ProductController::class, 'show']);

Route::get('/get-contracts', [App\Http\Controllers\ProductController::class, 'getContracts']);

Route::get('/get-products', [App\Http\Controllers\ProductController::class, 'getProducts']);

Route::post('/saleslogin', [App\Http\Controllers\SalesDetailsAuthController::class, 'authenticate']);


Route::get('/insert-mandatory-status', [App\Http\Controllers\EditContractListController::class, 'insertMandatoryStatus']);


Route::get('/Send-New-Contracts/{newEntryId}', [App\Http\Controllers\SalesListDraftController::class, 'showSalesList'])->name('sales.list');


Route::get('/sales-data', [App\Http\Controllers\SalesListDraftController::class, 'getSalesData'])->name('sales.data');



Route::get('/check-unique', [App\Http\Controllers\SalesDetailsController::class, 'checkUnique']);

Route::get('/display-checked-products', [App\Http\Controllers\SalesDetailsController::class, 'displayChecked']);
 
Route::get('/sales.details.displayChecked', [App\Http\Controllers\SalesDetailsController::class, 'displayChecked']);

//-------
Route::post('/update-product-status', [App\Http\Controllers\SalesDetailsController::class, 'updateProductStatus']);

Route::get('/Sales-Details', [App\Http\Controllers\SalesDetailsController::class, 'Productshow']);

Route::get('/Sales-Lists', [App\Http\Controllers\SalesDetailsController::class, 'show']);

Route::post('/save-product-to-sales', [App\Http\Controllers\SalesDetailsController::class, 'saveProductToSales']);

Route::post('/save-sales-details/{id?}', [App\Http\Controllers\SalesDetailsController::class, 'save']);


Route::get('/Sales-Details/{id}', [App\Http\Controllers\SalesDetailsController::class, 'editSales']);

Route::delete('/delete-sales/{id}', [App\Http\Controllers\SalesDetailsController::class,'destroy']);


Route::get('/createpricewithupdate', [App\Http\Controllers\PriceListController::class, 'createpricewithupdate'])->name('createpricewithupdate');

Route::post('/getMandatoryFieldValues', [App\Http\Controllers\EditContractListController::class, 'getMandatoryFieldValues']);
 
Route::post('/delete-selected-product', [App\Http\Controllers\EditContractListController::class, 'deleteSelectedProduct']);

Route::post('/save-selected-product', [App\Http\Controllers\EditContractListController::class, 'saveSelectedProduct']);

Route::get('/get-product-id', [App\Http\Controllers\EditContractListController::class, 'getProductId']);

Route::get('/get-price-id', [App\Http\Controllers\EditContractListController::class, 'getPriceId'])->name('get.price.id');

Route::post('/insert-price-id', [App\Http\Controllers\EditContractListController::class, 'insertPriceId'])->name('insert.price.id');
Route::post('/delete-price-id', [App\Http\Controllers\EditContractListController::class, 'deletePriceId'])->name('delete.price.id');

Route::delete('/price-lists/{id}', [App\Http\Controllers\PriceListController::class,'destroy'])->name('price-lists.destroy');

Route::get('/edit-price/{id}', [App\Http\Controllers\PriceListController::class, 'editPrice'])->name('edit.price');

Route::post('/update-price/{id}', [App\Http\Controllers\PriceListController::class, 'updatePrice'])->name('update.price');

Route::get('/Price-List', [App\Http\Controllers\PriceListController::class, 'getpricedata'])->name('get.data');

Route::post('/save-price-list', [App\Http\Controllers\PriceListController::class, 'savePriceList'])->name('save.price.list');
 
//land on new update page when create new contract
Route::get('/createcontractwithupdatepage', [App\Http\Controllers\ContractController::class, 'createContractWithUpdatePage'])->name('createcontractwithupdatepage');

//to see checked variable from database
Route::post('/checkedVariable', [App\Http\Controllers\EditContractListController::class, 'checkedVariable']);

Route::post('/HowmanyVariable', [App\Http\Controllers\VariableListController::class,'countVariableIDs']);

// to insert into contractvariablecheckbox when variable pop up is checked 
Route::post('/insert-contract-variable', [App\Http\Controllers\EditContractListController::class, 'insertContractVariable']);

//to delete the row from contractvariablecheckbox when unchecked
Route::post('/delete-contract-variable', [App\Http\Controllers\EditContractListController::class,'deleteContractVariable']);

//use App\Http\Controllers\HeaderAndFooterController;

Route::post('/header-and-footer/save', [App\Http\Controllers\HeaderAndFooterController::class, 'save'])->name('header-and-footer.save');

Route::post('/header-and-footer/{id}', [App\Http\Controllers\HeaderAndFooterController::class, 'deleteContract'])->name('entry.delete');
 
Route::post('/header-and-footer/update/{id}', [App\Http\Controllers\HeaderAndFooterController::class, 'update'])->name('header-and-footer.update');

Route::get('/HeaderAndFooter', [App\Http\Controllers\HeaderAndFooterController::class, 'show']);

//for generate preview pdf 

Route::post('/generate-pdf', [App\Http\Controllers\createContractController::class, 'generatePDF']);

// for delete contract list 
 Route::delete('contracts/{id}', [ App\Http\Controllers\ContractController::class, 'destroy'])->name('contracts.destroy');
 
// for the edit-contract-page
Route::get('/edit-contract-list/{id}', [App\Http\Controllers\EditContractListController::class, 'edit']);
 
Route::get('/edit-contract-list', [App\Http\Controllers\EditContractListController::class, 'showvariable']);
 
Route::post('/edit-contract-list/update', [App\Http\Controllers\EditContractListController::class, 'updateContract'])->name('edit-contract-list.update');
 
Route::get('/Contract-List', [App\Http\Controllers\ContractController::class, 'index'])->name('contracts.index');

Route::post('/update-variable/{id}', [App\Http\Controllers\VariableListController::class, 'updateVariable']);
 
// for Product list page  in office 
Route::get('/Product-List', [App\Http\Controllers\ProductController::class, 'index'])->name('product.index');

Route::get('/products', [App\Http\Controllers\ProductController::class, 'productforcreatepage'])->name('product.index');

Route::post('/save-product', [App\Http\Controllers\ProductController::class, 'saveProduct']);

//Variable-List page  to show all variable
Route::get('/Variable-List', [ App\Http\Controllers\VariableListController::class, 'index'])->name('variable.index');

// for delete variable list row
 
Route::post('/delete-contract/{id}', [App\Http\Controllers\VariableListController::class, 'deleteContract'])->name('contract.delete');

Route::post('/product-contract/{id}', [App\Http\Controllers\ProductController::class, 'deleteproduct'])->name('product.delete');

Route::post('/save-variable', [App\Http\Controllers\VariableListController::class, 'saveVariable']);
//Route::post('/save-variable', [App\Http\Controllers\VariableListController::class, 'saveProduct']);
Route::get('/fetch-variables', [App\Http\Controllers\VariableListController::class, 'fetchVariables']);

//to pass variables to createcontract.blade.php
Route::get('/createcontract', [App\Http\Controllers\createContractController::class, 'show'])->name('createcontract.show');

 //header footer entries

Route::get('/createvariablecontract', [App\Http\Controllers\ContractController::class, 'show']);

Route::get('/products', [App\Http\Controllers\createContractController::class, 'productforcreatepage'])->name('createcontract.productforcreatepage');
 
 
Route::post('/createcontract', [App\Http\Controllers\CreateContractController::class, 'store'])->name('createcontract.store');

//main one for save contract
Route::post('/savecontract', [App\Http\Controllers\ContractController::class, 'savecontract']);

//for image 
Route::post('/upload', [App\Http\Controllers\ContractController::class, 'upload'])->name('ckeditor.upload');

 
Route::get('/contracts/{id}/history', [App\Http\Controllers\ContractController::class, 'history'])->name('contracts.history');

 

Route::post('/save', [App\Http\Controllers\createcontractController::class, 'save']);

Route::post('/updatecontract', [App\Http\Controllers\ContractController::class, 'updatecontract']);
 
Route::get('list',[App\Http\Controllers\MemberController::class,'show']);
 

Route::post('/update-project/{id}', [App\Http\Controllers\ProjectController::class, 'updateProject']);

// for delete
Route::get('/delete/{id}', 'App\Http\Controllers\ProjectController@deleteProject');
 
Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])->name('index');
 
