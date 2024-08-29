<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Contract;  
use App\Models\SalesDetails; 
use App\Models\ProductToSales;
use App\Models\contractvariablecheckbox;
use App\Models\VariableList;
use App\Models\PriceList;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Storage;
 
use League\OAuth2\Client\Provider\GenericProvider;
use Illuminate\Support\Facades\Cache;
 
use HelloSign\Client;
use HelloSign\SignatureRequest;
use HelloSign\Signer;
use App\Models\SalesListDraft; // Import your model

use HelloSign\Client as HelloSignClient;
 
use HelloSign\SignatureRequestSigner;
use Twilio\Rest\Client as TwilioClient;
 
use HelloSign\SignerField;
//use Dropbox\Sign\Model\SignerField;
 
use PDF;
use Dompdf\Dompdf;
 
use Dompdf\Options;

use DateTime; 

use TCPDF;
use phpQuery; 
use App\Models\HeaderAndFooter;
use Illuminate\Support\Facades\Log;
use App\Models\HeaderAndFooterContractpage;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\PdfParser\Type\PdfArray;
use setasign\Fpdi\PdfParser\Type\PdfDictionary;
use setasign\Fpdi\PdfParser\Type\PdfName;
use setasign\Fpdi\PdfParser\Type\PdfString; 
use setasign\Fpdf\Fpdf;
use Smalot\PdfParser\Parser;
use setasign\Fpdi\PdfParser\PdfParser;
use Mpdf\MpdfException;
use Mpdf\Mpdf;
use Dropbox\Sign\Api\SignatureRequestApi;
use Dropbox\Sign\ApiException;
use Dropbox\Sign\Configuration;
use DB;
use App\Models\AppConnection;

use GuzzleHttp\Client as CloseClient;

use Illuminate\Support\Facades\Mail;

 
class ProductController extends Controller
{

    public function getAllEditVariables(Request $request)
    {
       
        $id = $request->input('id');

 
        $draft = SalesListDraft::find($id);

        if (!$draft) {
        
            return response()->json(['success' => false, 'message' => 'Draft not found'], 404);
        }

       
        $contractID = $draft->contract_id;  

        
        $variableData = ContractVariableCheckbox::where('ContractID', $contractID)
            ->join('variable_lists', 'contractvariablecheckbox.VariableID', '=', 'variable_lists.VariableID')
            ->select('variable_lists.*', 'contractvariablecheckbox.Order')
            ->orderByRaw('contractvariablecheckbox.Order IS NULL, contractvariablecheckbox.Order')
            ->get();

        // Return the variable data along with the contract ID
        return response()->json([
            'success' => true,
            'contractID' => $contractID,
            'variableData' => $variableData
        ]);
    }
 
    public function getallvariables(Request $request)
    {
        $contractID = $request->input('selectedContractId');
        $id = $request->input('id');

        $contract = Contract::find($contractID);
        $contractName = $contract->contract_name;

        if ($id) {
            $existingContract = SalesListDraft::find($id);

            if ($existingContract) {
                $existingContract->update([
                    'contract_id' => $contractID,
                    'contract_name' => $contractName
                ]);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Contract not found']);
            }
        } else {
            $lastRow = SalesListDraft::latest()->first();
            $lastRow->update([
                'contract_id' => $contractID,
                'contract_name' => $contractName
            ]);
        }

        $variableData = DB::table('variable_lists')
            ->join('contractvariablecheckbox', 'variable_lists.VariableID', '=', 'contractvariablecheckbox.VariableID')
            ->where('contractvariablecheckbox.ContractID', $contractID)
            ->orderByRaw('CASE WHEN `contractvariablecheckbox`.`Order` IS NULL THEN 1 ELSE 0 END, `contractvariablecheckbox`.`Order` ASC')
            ->select('variable_lists.*', 'contractvariablecheckbox.Order')
            ->get();

        return response()->json(['variableData' => $variableData]);
    }
     
   
    protected $signatureRequestApi;

    private $closeioApiKey;
    private $closeClient;
    private $helloSignClient;

    public function __construct()
    {   
        $this->closeioApiKey = env('CLOSEIO_API_KEY');
        $this->closeClient = new CloseClient();

        $config = Configuration::getDefaultConfiguration();
        $config->setUsername(env('HELLOSIGN_API_KEY'));
        $this->signatureRequestApi = new SignatureRequestApi($config);
    }

    // public function getSignedPdfUrl($id)
    // {
    //     $item = SalesListDraft::findOrFail($id);

    //     if ($item->status == 'signed') {
    //         try {
    //             $result = $this->signatureRequestApi->signatureRequestFilesAsFileUrl($item->envelope_id);
    //             $fileUrl = $result->getFileUrl();

    //             if ($fileUrl) {
    //                 return response()->json(['success' => true, 'file_url' => $fileUrl]);
    //             } else {
    //                 return response()->json(['success' => false, 'message' => 'Failed to retrieve the signed PDF URL.']);
    //             }
    //         } catch (ApiException $e) {
    //             $error = $e->getResponseObject();
    //             return response()->json(['success' => false, 'message' => 'Failed to retrieve the signed PDF URL: ' . print_r($error->getError(), true)]);
    //         }
    //     }

    //     return response()->json(['success' => false, 'message' => 'Item is not signed or could not fetch link']);
    // }
     

    public function downloadSignedPdf($id)
    {
        // Retrieve the record from SalesListDraft using the provided ID
        $pdfSignature = SalesListDraft::findOrFail($id);

        // Fetch the path of the stored signed PDF file
        $pdfFilePath = storage_path('app/public/pdf/' . $pdfSignature->selected_pdf_name);
  
        if (!file_exists($pdfFilePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Signed PDF file not found. Please contact support.',
            ], 404);
        }
  
        // Return the file as a response for download
        return response()->download($pdfFilePath)->deleteFileAfterSend(false); // Ensure the file is not deleted
    }
    

    //------------------------------

 
    // new one 
    private function processImageTags($htmlContent)
    {
        // Define regex patterns for different image tags
        $patterns = [
            'right' => '/<figure class="image image-style-side"><img[^>]*><\/figure>/i',
            'left' => '/<p><img[^>]*><\/p>/i',
            'middle' => '/<figure class="image"><img[^>]*><\/figure>/i'
        ];
    
        foreach ($patterns as $position => $pattern) {
            preg_match_all($pattern, $htmlContent, $matches);
    
            foreach ($matches[0] as $imgTag) {
                // Extract src, width, and height attributes
                preg_match('/src="([^"]*)"/i', $imgTag, $srcMatch);
                preg_match('/width="([^"]*)"/i', $imgTag, $widthMatch);
                preg_match('/height="([^"]*)"/i', $imgTag, $heightMatch);
    
                $src = $srcMatch[1] ?? '';
                $width = $widthMatch[1] ?? '';
                $height = $heightMatch[1] ?? '';
    
                // Generate the new image tag based on the position
                switch ($position) {
                    case 'right':
                        $newImgTag = "<img src=\"{$src}\" width=\"{$width}\" height=\"{$height}\" style=\"float: right; margin: 10px;\">";
                        break;
                    case 'left':
                        $newImgTag = "<img src=\"{$src}\" width=\"{$width}\" height=\"{$height}\" style=\"float: left; margin: 10px;\">";
                        break;
                    case 'middle':
                    default:
                        $newImgTag = "<div style=\"text-align: center;\"><img src=\"{$src}\" width=\"{$width}\" height=\"{$height}\" style=\"display: inline-block;\"></div>";
                        break;
                }
    
                // Replace the old image tag with the new one in the HTML content
                $htmlContent = str_replace($imgTag, $newImgTag, $htmlContent);
            }
        }
    
        return $htmlContent;
    }
   

 
  public  function formatDateToDDMMYYYY($dateString) {
        // Check if the date string is valid
        if (!$dateString) {
            return '';
        }
        
        // Convert the date string to a DateTime object
        $date = DateTime::createFromFormat('Y-m-d', $dateString);
        if (!$date) {
            // If the date format is not Y-m-d, try other common formats
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $dateString);
        }

        // If still no valid DateTime object, return original string
        if (!$date) {
            return htmlspecialchars($dateString);
        }
        
        // Format the date to dd/mm/yyyy
        return $date->format('d/m/Y');
    }


    public function generatePdfforSales(Request $request)
    {
        // Validate the request input
        $request->validate([
            'selectedContractId' => 'required',
            'variableValues' => 'array',
            'priceValues' => 'array',
            'id' => 'integer|nullable',
        ]);
    
        $contractIdentifier = $request->input('selectedContractId');
    
        // Fetch the contract by ID or name
        if (is_numeric($contractIdentifier)) {
            $contract = Contract::select('editor_content')->find($contractIdentifier);
        } else {
            $contract = Contract::where('contract_name', $contractIdentifier)->first();
        }
    
        if (!$contract) {
            return response()->json(['error' => 'Contract not found'], 404);
        }
    
        $contractID = is_numeric($contractIdentifier) ? $contractIdentifier : $contract->id;
        $htmlContent = $contract->editor_content;
    
        $variableValues = $request->input('variableValues', []);
    
        // Replace placeholders with actual values from variableValues
        foreach ($variableValues as $name => $variable) {
            $placeholder = '%' . $name . '%';
    
            // Handle different types
            if (isset($variable['type'])) {
                if ($variable['type'] === 'Multiple Box') {
                    // Handle Multiple Box type
                    $formattedValue = '<ul>';
                    if (is_array($variable['value'])) {
                        foreach ($variable['value'] as $item) {
                            if (is_array($item)) {
                                $inputValue = htmlspecialchars($item['inputValue'] ?? '');
                                $ckEditorContent = $item['ckEditorContent'] ?? '';
                            } else {
                                $inputValue = htmlspecialchars($item);
                                $ckEditorContent = '';
                            }
                            $formattedValue .= '<li>' . $inputValue;
                            if (!empty($ckEditorContent)) {
                                $formattedValue .= ' ' . $ckEditorContent;
                            }
                            $formattedValue .= '</li>';
                        }
                    } else {
                        $valueArray = explode(',', $variable['value']);
                        foreach ($valueArray as $item) {
                            $subItems = explode(';', $item);
                            foreach ($subItems as $subItem) {
                                $inputValue = htmlspecialchars(trim($subItem));
                                $formattedValue .= '<li>' . $inputValue . '</li>';
                            }
                        }
                    }
                    $formattedValue .= '</ul>';
                    $htmlContent = str_replace($placeholder, $formattedValue, $htmlContent);
                } elseif ($variable['type'] === 'Single Box') {
                    // Handle Single Box type
                    if (is_array($variable['value'])) {
                        $inputValue = htmlspecialchars($variable['value']['inputValue'] ?? '');
                        $ckEditorContent = $variable['value']['ckEditorContent'] ?? '';
                    } else {
                        $inputValue = htmlspecialchars($variable['value']);
                        $ckEditorContent = '';
                    }
                    $formattedValue = $inputValue;
                    if (!empty($ckEditorContent)) {
                        $formattedValue .= ' ' . $ckEditorContent;
                    }
                    $htmlContent = str_replace($placeholder, $formattedValue, $htmlContent);
                } elseif ($variable['type'] === 'Dates') {
                    // Handle Dates type
                    $dueDate = DateTime::createFromFormat('Y-m-d', $variable['value']);
                    $formattedDate = $dueDate ? $dueDate->format('d/m/Y') : htmlspecialchars($variable['value']);
                    $htmlContent = str_replace($placeholder, $formattedDate, $htmlContent);
                } else {
                    // Handle Single Line Text and other types
                    $value = htmlspecialchars($variable['value'] ?? '');
                    $htmlContent = str_replace($placeholder, $value, $htmlContent);
                }
            } else {
                $value = htmlspecialchars($variable['value'] ?? '');
                $htmlContent = str_replace($placeholder, $value, $htmlContent);
            }
        }
    
        // Replace $PRICE$ placeholder with price details
        $priceValues = $request->input('priceValues', []);
        $price = '$PRICE$';
        $formattedPrices = '';
    
        if (is_array($priceValues)) {
            $dynamicminRange = htmlspecialchars($priceValues['dynamicminRange'] ?? $priceValues['fixedvalue'] ?? '');
            $currency = htmlspecialchars($priceValues['currency']);
            $paymentMaxRange = htmlspecialchars($priceValues['paymentMaxRange']);
            $frequency = htmlspecialchars($priceValues['frequency']);
            $payments = $priceValues['payments'];
    
            $amountValues = $priceValues['amountValues'] ?? [];
            $dueDateValues = $priceValues['dueDateValues'] ?? [];
    
            $includeonprice = filter_var($priceValues['includeonprice'], FILTER_VALIDATE_BOOLEAN);
            $vatpercentage = $priceValues['vatpercentage'];
    
            $formattedPrices .= '<ul>';
    
            if ($includeonprice) {
                $priceWithVat = (floatval($vatpercentage) * floatval($dynamicminRange) / 100) + floatval($dynamicminRange);
                $priceWithVatFormatted = number_format($priceWithVat, 2, ',', '.');
                $formattedPrices .= '<li>Il prezzo totale di ' . $priceWithVatFormatted . ' ' . $currency . ' (IVA Compresa) sarà corrisposto con le seguenti modalità:</li>';
            } else {
                $formattedPrices .= '<li>Il prezzo totale di ' . $dynamicminRange . ' ' . $currency . ' + IVA sarà corrisposto con le seguenti modalità:</li>';
            }
    
            $totalCheck = 0;
    
            for ($i = 0; $i < $paymentMaxRange; $i++) {
                $formattedPaymentAmount = number_format(floatval($amountValues[$i]), 2, ',', '.');
    
                $dueDate = DateTime::createFromFormat('Y-m-d', $dueDateValues[$i]);
                $formattedDueDate = $dueDate ? $dueDate->format('d/m/Y') : htmlspecialchars($dueDateValues[$i]);
    
                $paymentAmount = floatval($amountValues[$i]);
                $totalCheck += $paymentAmount;
    
                $formattedPrices .= '<li>Pagamento ' . ($i + 1) . ' di ' . $currency . ' ' . $formattedPaymentAmount . ' + IVA entro il ' . $formattedDueDate . '</li>';
            }
    
            $expectedTotal = $includeonprice ? $priceWithVat : floatval($dynamicminRange);
            $acceptableDifference = 0.80;
    
            if (abs($totalCheck - $expectedTotal) > $acceptableDifference) {
                return response()->json([
                    'error' => 'PDF generation failed because the total installment is not the same as the full price',
                    'totalCheck' => $totalCheck,
                    'expectedTotal' => $expectedTotal,
                    'difference' => abs($totalCheck - $expectedTotal)
                ], 500);
            }
    
            $formattedPrices .= '</ul>';
        } else {
            $formattedPrices = htmlspecialchars($priceValues);
        }
    
        $htmlContent = str_replace($price, $formattedPrices, $htmlContent);
    
        $htmlContent = $this->processImageTags($htmlContent);
    
        $config = HeaderAndFooterContractpage::where('contractID', $contractID)->first();
    
        $headerContent = '';
        $footerContent = '';
    
        if ($config) {
            if ($config->HeaderID) {
                $headerData = HeaderAndFooter::find($config->HeaderID);
                if ($headerData) {
                    $headerContent = $headerData->editor_content;
                }
            }
    
            if ($config->FooterID) {
                $footerData = HeaderAndFooter::find($config->FooterID);
                if ($footerData) {
                    $footerContent = $footerData->editor_content;
                }
            }
        }
    
        if (empty($headerContent)) {
            $headerContent = '<div style="text-align: center; font-weight: bold;"></div>';
        } else {
            $headerContent = '<div style="text-align: center; font-weight: bold;">' . $headerContent . '</div>';
        }
        if (empty($footerContent)) {
            $footerContent = '<div style="text-align: center;"></div>';
        } else {
            $footerContent = '<div style="text-align: center;">' . $footerContent . '</div>';
        }
    
        try {
            $mpdf = new \Mpdf\Mpdf([
                'format' => 'A4',
                'margin_top' => $headerContent ? 40 : 5,
                'margin_bottom' => $footerContent ? 40 : 5,
                'margin_left' => 15,
                'margin_right' => 15,
            ]);
    
            $mpdf->SetTitle('Contract PDF');
            $mpdf->SetAuthor('Your Company');
    
            $mpdf->SetWatermarkText('Giacomo Freddi');
            $mpdf->showWatermarkText = true;
            $mpdf->watermark_font = 'DejaVuSansCondensed';
            $mpdf->watermarkTextAlpha = 0.1;
    
            $mpdf->SetHTMLHeader($headerContent);
    
            $italianTimezone = new \DateTimeZone('Europe/Rome');
            $dateTime = new \DateTime('now', $italianTimezone);
            $formattedDateTime = $dateTime->format('d-m-Y H:i:s');
    
            $footerHTML = '
                <div style="width: 100%; font-size: 10px; display: flex; justify-content: space-between; align-items: center; position: relative;">
                    <div style="flex: 1; text-align: center;">' . $footerContent . '</div>
                    <div style="flex: 1; text-align: right;">Page {PAGENO}/{nbpg}</div>
                </div>
            ';
    
            $mpdf->SetHTMLFooter($footerHTML);
    
            $contentHTML = '
                <style>
                    body { font-family: Arial, sans-serif; font-size: 12px; }
                    ul { padding-left: 20px; }
                    li { margin-bottom: 5px; }
                    img { max-width: 100%; height: auto; display: block; margin: auto; }
                    table { width: 100%; border-collapse: collapse; }
                    table, th, td { border: 1px solid black; }
                    th, td { padding: 8px; text-align: left; }
                </style>
                <div>' . $htmlContent . '</div>';
    
            $mpdf->WriteHTML($contentHTML);
    
            $id = $request->input('id');
            $filename = 'contract_' . $id . '.pdf';
    
            if ($id) {
                $record = SalesListDraft::find($id);
    
                if ($record) {
                    if ($record->selected_pdf_name) {
                        Storage::disk('public')->delete('pdf/' . $record->selected_pdf_name);
                    }
    
                    $record->update(['selected_pdf_name' => $filename]);
                } else {
                    return response()->json(['error' => 'Record not found'], 404);
                }
            } else {
                $existingPdf = SalesListDraft::where('selected_pdf_name', $filename)->first();
    
                if ($existingPdf) {
                    Storage::disk('public')->delete('pdf/' . $filename);
                }
    
                $lastRow = SalesListDraft::latest()->first();
    
                if ($lastRow) {
                    $lastRow->update(['selected_pdf_name' => $filename]);
                } else {
                    SalesListDraft::create(['selected_pdf_name' => $filename]);
                }
            }
    
            $pdfFilePath = 'pdf/' . $filename;
    
            Storage::disk('public')->put($pdfFilePath, $mpdf->Output('', 'S'));
    
            session(['html_content' => $htmlContent]);
    
            return response()->json(['pdf_url' => Storage::url($pdfFilePath)]);
    
        } catch (\Mpdf\MpdfException $e) {
            return response()->json(['error' => 'PDF generation failed', 'message' => $e->getMessage()], 500);
        }
    }
    

//-------------------------------------------------------------------


// public function sendDocumentForSignature(Request $request)
// {
//     $pdfUrl = $request->input('pdfUrl');
//     $recipientEmail = $request->input('recipientEmail');
//     $recipientName = $request->input('recipientName');
//     $recipientMobile = $request->input('recipientMobile');
//     $id = $request->input('id');
//     $selectedProduct = $request->input('selectedProduct');

//     $responseMessage = [
//         'email_status' => 'Email sent successfully.',
//         'close_io_status' => 'Note added successfully to Close.io.',
//     ];

//     // Retrieve the HTML content from session
//     $htmlContent = session('html_content');

//     if (!$htmlContent) {
//         return response()->json(['error' => 'HTML content is missing.'], 400);
//     }

//     // Process image tags to ensure positions are set
//     $htmlContent = $this->processImageTags($htmlContent);

//     // Replace the specific image tags with the signature tag
//     $htmlContent = $this->replaceImageTagsWithSignatureTag($htmlContent);

//     // Fetch the contract by ID or name
//     $contractIdentifier = $request->input('selectedContractId');
//     $contract = null;
//     $contractID = null;
//     $contractNote = null;

//     if (is_numeric($contractIdentifier)) {
//         $contract = Contract::select('editor_content')->find($contractIdentifier);
//         $contractID = $contractIdentifier;
//         $contractNote = Contract::find($contractIdentifier);
//     } else {
//         $contract = Contract::where('contract_name', $contractIdentifier)->first();
//         if ($contract) {
//             $contractID = $contract->id;
//             $contractNote = Contract::find($contract->id);
//         }
//     }

//     if (!$contract) {
//         return response()->json(['error' => 'Contract not found'], 404);
//     }

//     try {
//         // Step 1: Attempt to add a note to Close.io
//         try {
//             $this->addCloseIoNoteForPending($recipientEmail, $contractNote->contract_name, $selectedProduct);
//         } catch (\Exception $e) {
//             \Log::error("Error adding note to Close.io: " . $e->getMessage());
//             // Update the response message for Close.io error
//             $responseMessage['close_io_status'] = 'Failed to add note to Close.io.';
//         }

//         // Step 2: Proceed with document creation only if Close.io note addition is attempted
//         $config = HeaderAndFooterContractpage::where('contractID', $contractID)->first();

//         $headerContent = '';
//         $footerContent = '';

//         if ($config) {
//             if ($config->HeaderID) {
//                 $headerData = HeaderAndFooter::find($config->HeaderID);
//                 if ($headerData) {
//                     $headerContent = $headerData->editor_content;
//                 }
//             }

//             if ($config->FooterID) {
//                 $footerData = HeaderAndFooter::find($config->FooterID);
//                 if ($footerData) {
//                     $footerContent = $footerData->editor_content;
//                 }
//             }
//         }

//         // Fallback content if header and footer are not found
//         $headerContent = empty($headerContent) ? '<div style="text-align: center; font-weight: bold;"></div>' : '<div style="text-align: center; font-weight: bold;">' . $headerContent . '</div>';
//         $footerContent = empty($footerContent) ? '<div style="text-align: center;"></div>' : '<div style="text-align: center;">' . $footerContent . '</div>';

//         // Create new mPDF document
//         $mpdf = new \Mpdf\Mpdf([
//             'format' => 'A4',
//             'margin_top' => $headerContent ? 40 : 5,
//             'margin_bottom' => $footerContent ? 40 : 5,
//             'margin_left' => 15,
//             'margin_right' => 15,
//         ]);

//         // Set document properties
//         $mpdf->SetTitle('Document for Signature');
//         $mpdf->SetAuthor('Your Company');

//         // Set watermark if needed
//         $mpdf->SetWatermarkText('Giacomo Freddi');
//         $mpdf->showWatermarkText = true;
//         $mpdf->watermark_font = 'DejaVuSansCondensed';
//         $mpdf->watermarkTextAlpha = 0.1;

//         // Set header and footer content
//         $mpdf->SetHTMLHeader($headerContent);

//         $italianTimezone = new \DateTimeZone('Europe/Rome');
//         $dateTime = new \DateTime('now', $italianTimezone);
//         $formattedDateTime = $dateTime->format('d-m-Y H:i:s');

//         $footerHTML = '
//             <div style="width: 100%; font-size: 10px; display: flex; justify-content: space-between; align-items: center; position: relative;">
//                 <div style="flex: 1; text-align: center;">' . $footerContent . '</div>
//                 <div style="flex: 1; text-align: right;">Page {PAGENO}/{nbpg}</div>
//             </div>
//         ';

//         $mpdf->SetHTMLFooter($footerHTML);

//         // Add CSS for content styling, including table borders
//         $contentHTML = '
//             <style>
//                 body { font-family: Arial, sans-serif; font-size: 12px; }
//                 ul { padding-left: 20px; }
//                 li { margin-bottom: 5px; }
//                 img { max-width: 100%; height: auto; display: block; margin: auto; }
//                 table { width: 100%; border-collapse: collapse; }
//                 table, th, td { border: 1px solid black; }
//                 th, td { padding: 8px; text-align: left; }
//                 div.sig-container { margin: 10px; display: flex; justify-content: center; align-items: center; height: 50px; } /* Ensure the tag is on one line */
//                 div.sig-container.float-right { justify-content: flex-end; }
//                 div.sig-container.float-left { justify-content: flex-start; }
//             </style>
//             <div>' . $htmlContent . '</div>';

//         // Write the HTML content to the PDF
//         $mpdf->WriteHTML($contentHTML);

//         // Define the filename
//         $filename = 'contract_' . time() . '.pdf';
//         $pdfFilePath = 'pdf/' . $filename;

//         // Save the PDF file to storage
//         Storage::disk('public')->put($pdfFilePath, $mpdf->Output('', 'S'));

//         // Step 3: Send the PDF to HelloSign for signature
       
//         /*  
     
//         $client = new \HelloSign\Client(env('HELLOSIGN_API_KEY'));
//         $signatureRequest = new \HelloSign\SignatureRequest();
//         $signatureRequest->setTitle('Please sign this document');
//         $signatureRequest->setSubject('Document Signature Request');
//         $signatureRequest->setMessage('Please sign this document and let us know if you have any questions.');
//         $signatureRequest->addSigner(new \HelloSign\Signer([
//             'email_address' => $recipientEmail,
//             'name' => $recipientName
//         ]));
//         $signatureRequest->addFile(storage_path('app/public/' . $pdfFilePath));

        
//         $signatureRequest->setUseTextTags(true);
//         $signatureRequest->setHideTextTags(true); 

//         $response = $client->sendSignatureRequest($signatureRequest);
//         $envelopeId = $response->getId();
//         $signatureUrl = "https://app.hellosign.com/sign/$envelopeId";

//         */

//         // Step 4: Update the database with the new document details
//         $draftUpdated = false;

//         if ($id) {
//             $salesListDraft = SalesListDraft::find($id);
//             if ($salesListDraft) {
//                 // Delete the existing PDF file, if it exists
//                 if ($salesListDraft->selected_pdf_name) {
//                     Storage::disk('public')->delete('pdf/' . $salesListDraft->selected_pdf_name);
//                 }

//                 // Update with new details
//                 $salesListDraft->envelope_id = $envelopeId;
//                 $salesListDraft->recipient_email = $recipientEmail;
//                 $salesListDraft->status = 'pending';
//                 $salesListDraft->selected_pdf_name = $filename;
//                 $salesListDraft->save();
//                 $draftUpdated = true;
//             }
//         }

//         if (!$draftUpdated) {
//             $lastRow = SalesListDraft::latest()->first();
//             if ($lastRow) {
//                 // Update the last row if it exists
//                 $lastRow->envelope_id = $envelopeId;
//                 $lastRow->recipient_email = $recipientEmail;
//                 $lastRow->status = 'pending';
//                 $lastRow->selected_pdf_name = $filename;
//                 $lastRow->save();
//             } else {
//                 // Create a new record if no rows exist
//                 SalesListDraft::create([
//                     'envelope_id' => $envelopeId,
//                     'recipient_email' => $recipientEmail,
//                     'status' => 'pending',
//                     'selected_pdf_name' => $filename
//                 ]);
//             }
//         }

//         $responseMessage['envelope_id'] = $envelopeId;

//         return response()->json($responseMessage);

//     } catch (\Exception $e) {
//         \Log::error("Error processing document: " . $e->getMessage());
//         return response()->json(['error' => 'Failed to process the document. Please try again later.'], 500);
//     }
// }


       // Step 1: Attempt to add a note to Close.io for now closed 
        
        
        // try {
        //     $this->addCloseIoNoteForPending($recipientEmail, $contractNote->contract_name, $selectedProduct);
        // } catch (\Exception $e) {
        //     \Log::error("Error adding note to Close.io: " . $e->getMessage());
        //     // Update the response message for Close.io error
        //     $responseMessage['close_io_status'] = 'Failed to add note to Close.io.';
        // }

        // Step 2: Proceed with document creation only if Close.io note addition is attempted

// working one 2nd

public function sendDocumentForSignature(Request $request)
{
    $pdfUrl = $request->input('pdfUrl');
    $recipientEmail = $request->input('recipientEmail');
    $recipientName = $request->input('recipientName');
    $recipientMobile = $request->input('recipientMobile');
    $id = $request->input('id');  // Get the provided ID
    $selectedProduct = $request->input('selectedProduct');

    $responseMessage = [
        'email_status' => 'Email sent successfully.',
        'close_io_status' => 'Note added successfully to Close.io.',
    ];

    // Retrieve the HTML content from the session
    $htmlContent = session('html_content');

    if (!$htmlContent) {
        return response()->json(['error' => 'HTML content is missing.'], 400);
    }

    
     // Process image tags to ensure positions are set
     $htmlContent = $this->processImageTags($htmlContent);

     // Replace the specific image tags with the signature tag
  //   $htmlContent = $this->replaceImageTagsWithSignatureTag($htmlContent);

    // Fetch the contract by ID or name
    $contractIdentifier = $request->input('selectedContractId');
    $contract = null;
    $contractID = null;
    $contractNote = null;

    if (is_numeric($contractIdentifier)) {
        $contract = Contract::select('editor_content')->find($contractIdentifier);
        $contractID = $contractIdentifier;
        $contractNote = Contract::find($contractIdentifier);
    } else {
        $contract = Contract::where('contract_name', $contractIdentifier)->first();
        if ($contract) {
            $contractID = $contract->id;
            $contractNote = Contract::find($contract->id);
        }
    }

    if (!$contract) {
        return response()->json(['error' => 'Contract not found'], 404);
    }

    try {

         // Step 1: Attempt to add a note to Close.io
        // try {
        //     $this->addCloseIoNoteForPending($recipientEmail, $contractNote->contract_name, $selectedProduct);
        // } catch (\Exception $e) {
        //     \Log::error("Error adding note to Close.io: " . $e->getMessage());
        //     // Update the response message for Close.io error
        //     $responseMessage['close_io_status'] = 'Failed to add note to Close.io.';
        // }

        // Step 2: Proceed with document creation only if Close.io note addition is attempted
       
        $config = HeaderAndFooterContractpage::where('contractID', $contractID)->first();

        $headerContent = '';
        $footerContent = '';

        if ($config) {
            if ($config->HeaderID) {
                $headerData = HeaderAndFooter::find($config->HeaderID);
                if ($headerData) {
                    $headerContent = $headerData->editor_content;
                }
            }

            if ($config->FooterID) {
                $footerData = HeaderAndFooter::find($config->FooterID);
                if ($footerData) {
                    $footerContent = $footerData->editor_content;
                }
            }
        }

        // Fallback content if header and footer are not found
        $headerContent = empty($headerContent) ? '<div style="text-align: center; font-weight: bold;"></div>' : '<div style="text-align: center; font-weight: bold;">' . $headerContent . '</div>';
        $footerContent = empty($footerContent) ? '<div style="text-align: center;"></div>' : '<div style="text-align: center;">' . $footerContent . '</div>';

        // Create new mPDF document
        $mpdf = new \Mpdf\Mpdf([
            'format' => 'A4',
            'margin_top' => $headerContent ? 40 : 5,
            'margin_bottom' => $footerContent ? 40 : 5,
            'margin_left' => 15,
            'margin_right' => 15,
        ]);

        // Set document properties
        $mpdf->SetTitle('Document for Signature');
        $mpdf->SetAuthor('Your Company');

        // Set watermark if needed
        $mpdf->SetWatermarkText('Giacomo Freddi');
        $mpdf->showWatermarkText = true;
        $mpdf->watermark_font = 'DejaVuSansCondensed';
        $mpdf->watermarkTextAlpha = 0.1;

        // Set header and footer content
        $mpdf->SetHTMLHeader($headerContent);

        $italianTimezone = new \DateTimeZone('Europe/Rome');
        $dateTime = new \DateTime('now', $italianTimezone);
        $formattedDateTime = $dateTime->format('d-m-Y H:i:s');

        $footerHTML = '
            <div style="width: 100%; font-size: 10px; display: flex; justify-content: space-between; align-items: center; position: relative;">
                <div style="flex: 1; text-align: center;">' . $footerContent . '</div>
                <div style="flex: 1; text-align: right;">Page {PAGENO}/{nbpg}</div>
            </div>
        ';

        $mpdf->SetHTMLFooter($footerHTML);

        // Add CSS for content styling, including table borders
        $contentHTML = '
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                ul { padding-left: 20px; }
                li { margin-bottom: 5px; }
                img { max-width: 100%; height: auto; display: block; margin: auto; }
                table { width: 100%; border-collapse: collapse; }
                table, th, td { border: 1px solid black; }
                th, td { padding: 8px; text-align: left; }
                div.sig-container { margin: 10px; display: flex; justify-content: center; align-items: center; height: 50px; } /* Ensure the tag is on one line */
                div.sig-container.float-right { justify-content: flex-end; }
                div.sig-container.float-left { justify-content: flex-start; }
            </style>
            <div>' . $htmlContent . '</div>';

        // Write the content to the PDF
        $mpdf->WriteHTML($contentHTML);

        // Generate a unique filename using a consistent identifier (e.g., ID)
        $filename = 'contract_' . $id . '.pdf';
        $pdfFilePath = 'pdf/' . $filename;

        // Save the PDF file to storage
        Storage::disk('public')->put($pdfFilePath, $mpdf->Output('', 'S'));

        // Step 3: Check if the $id is provided and use it to update the existing row instead of creating a new one
        $salesListDraft = SalesListDraft::find($id);

        if ($salesListDraft) {
            // Update the existing row with new details
            $salesListDraft->recipient_email = $recipientEmail;
            $salesListDraft->status = 'pending';
            $salesListDraft->selected_pdf_name = $filename;  // Use the consistent filename here
            $salesListDraft->envelope_id = $id;  // Use the same ID as the envelope ID
            $salesListDraft->save();
        } else {
            // If no existing row is found, create a new one with the provided ID
            $salesListDraft = SalesListDraft::create([
                'id' => $id,  // Use the provided ID
                'recipient_email' => $recipientEmail,
                'status' => 'pending',
                'selected_pdf_name' => $filename,  // Use the consistent filename here
                'envelope_id' => $id // Use the same ID as the envelope ID
            ]);
        }

        // Generate the signing link with the same ID
        $signingUrl = route('sign.document', ['id' => $salesListDraft->id]);

        // Send the email with the signing link
        Mail::to($recipientEmail)->send(new \App\Mail\SendSignatureLink($signingUrl, $recipientName));

        // Step 4: Update the database with the new document details if needed
        $draftUpdated = false;

        if ($id) {
            $salesListDraft = SalesListDraft::find($id);
            if ($salesListDraft) {
                // Update with new details
                $salesListDraft->envelope_id = $id; // Use the same ID as the envelope ID
                $salesListDraft->recipient_email = $recipientEmail;
                $salesListDraft->status = 'pending';
                $salesListDraft->main_contract = $contentHTML;
                $salesListDraft->selected_pdf_name = $filename;  // Use the consistent filename here
                $salesListDraft->save();
                $draftUpdated = true;
            }
        }

        if (!$draftUpdated) {
            $lastRow = SalesListDraft::latest()->first();
            if ($lastRow) {
                // Update the last row if it exists
                $lastRow->envelope_id = $id; // Use the same ID as the envelope ID
                $lastRow->recipient_email = $recipientEmail;
                $lastRow->status = 'pending';
                $lastRow->main_contract = $contentHTML;
                $lastRow->selected_pdf_name = $filename;  // Use the consistent filename here
                $lastRow->save();
            } else {
                // Create a new record if no rows exist
                SalesListDraft::create([
                    'envelope_id' => $id, // Use the same ID as the envelope ID
                    'recipient_email' => $recipientEmail,
                    'status' => 'pending',
                    'selected_pdf_name' => $filename  // Use the consistent filename here
                ]);
            }
        }

        $responseMessage['envelope_id'] = $id;  // Return the same ID

        return response()->json($responseMessage);

    } catch (\Exception $e) {
        \Log::error("Error processing document: " . $e->getMessage());
        return response()->json(['error' => 'Failed to process the document. Error: ' . $e->getMessage()], 500);
    }
}

 

//--------------------------

private function addCloseIoNoteForPending($recipientEmail, $contractName, $selectedProduct)
{
    try {
        // Step 1: Retrieve the pending note template from the AppConnection table
        $appConnection = AppConnection::where('type', 'Close')->first();

        $noteTemplate = '';
        if ($appConnection && isset($appConnection->api_key)) {
            $apiData = json_decode($appConnection->api_key, true);
            if (isset($apiData['pending'])) {
                $noteTemplate = $apiData['pending'];
            }
        }

        // Default note text if no template is found in the database
        if (empty($noteTemplate)) {
            $noteTemplate = "All'e-mail del cliente è arrivato il contratto: \$contract_name$, del prodotto: \$product_name$. Il contratto è stato inviato con successo ed è in attesa di firma.";
        }

        // Replace placeholders with actual values
        $noteText = str_replace(['$contract_name$', '$product_name$'], [$contractName, $selectedProduct], $noteTemplate);

        // Step 2: Search for the lead in Close.io using the recipient's email
        $response = $this->closeClient->request('GET', 'https://api.close.com/api/v1/lead/', [
            'auth' => [$this->closeioApiKey, ''], // Use Basic Auth with the API key as the username
            'query' => [
                'query' => $recipientEmail,
            ]
        ]);

        $leads = json_decode($response->getBody(), true);

        \Log::info('Close.io Lead Search Response:', $leads);

        if (isset($leads['data']) && count($leads['data']) > 0) {
            $leadId = $leads['data'][0]['id'];

            // Log the variables to ensure they are not empty
            \Log::info("Adding note with Contract Name: {$contractName}, Product Name: {$selectedProduct}");

            // Step 3: Prepare data to add the note
            $data = [
                'note' => $noteText,
                'lead_id' => $leadId,
            ];

            // Step 4: Add the note to the lead
            $noteResponse = $this->closeClient->request('POST', 'https://api.close.com/api/v1/activity/note/', [
                'auth' => [$this->closeioApiKey, ''], // Use Basic Auth with the API key as the username
                'json' => $data, // Pass data directly as JSON
            ]);

            $noteResponseBody = json_decode($noteResponse->getBody(), true);
            \Log::info('Close.io Note Addition Response:', $noteResponseBody);

            // Return or log success message
            \Log::info("Successfully added note to Close.io for lead {$leadId}");
        } else {
            \Log::info("No lead found for email {$recipientEmail}");
            throw new \Exception("No lead found for email {$recipientEmail}");
        }
    } catch (\GuzzleHttp\Exception\RequestException $e) {
        // Handle Guzzle-specific exceptions, which include HTTP errors and network issues
        \Log::error("RequestException while communicating with Close.io API: " . $e->getMessage());
        if ($e->hasResponse()) {
            $responseBody = $e->getResponse()->getBody()->getContents();
            \Log::error("Response: " . $responseBody);
        }
        throw $e;
    } catch (\Exception $e) {
        // Log the error for debugging purposes
        \Log::error("General exception while communicating with Close.io API: " . $e->getMessage());
        throw $e;
    }
}

  

    

    private function replaceImageTagsWithSignatureTag($content)
    {
        // Define the regex pattern for the specific image src attribute
        $pattern = '/<img[^>]*src="https:\/\/i\.ibb\.co\/71g553C\/FIRMA-QUI\.jpg"[^>]*>/i';

        // Replace each pattern with the signer tag while keeping the alignment
        $content = preg_replace_callback($pattern, function ($matches) {
            // Extract style attribute if present
            preg_match('/style="([^"]*)"/i', $matches[0], $styleMatch);
            $style = $styleMatch[1] ?? '';

            // Determine position from the style and ensure the signer tag fits within a single line
            if (strpos($style, 'float: right;') !== false) {
                return '<div class="sig-container float-right" style="margin: 10px;">[sig|req|signer1]</div>';
            } elseif (strpos($style, 'float: left;') !== false) {
                return '<div class="sig-container float-left" style="margin: 10px;">[sig|req|signer1]</div>';
            } else {
                // Default to centered if no specific style is detected
                return '<div class="sig-container" style="margin: 10px;">[sig|req|signer1]</div>';
            }
        }, $content);

        return $content;
    }
 
  
    // For whats app message method 

    private function sendWhatsAppMessage($recipientMobile, $signatureUrl)
    {
        $twilioSid = env('TWILIO_SID');
        $twilioAuthToken = env('TWILIO_AUTH_TOKEN');
        $twilioWhatsAppNumber = env('TWILIO_WHATSAPP_NUMBER');

        $twilio = new TwilioClient($twilioSid, $twilioAuthToken);

        $message = "Hello, We are from Codice 1%. Here is your contract. Please sign this document: $signatureUrl";

        try {
            $twilio->messages->create(
                "whatsapp:$recipientMobile",
                [
                    'from' => "whatsapp:$twilioWhatsAppNumber",
                    'body' => $message
                ]
            );
            return true;
        } catch (\Exception $e) {
            \Log::error('Error sending WhatsApp message: ' . $e->getMessage());
            return false;
        }
    }


    // ********************************  
 

    public function show($id)
    {
        $draft = SalesListDraft::findOrFail($id);
        return response()->json([
            'variable_json' => $draft->variable_json
        ]);
    }


    public function getPriceListsPayment(Request $request)
    {
        $selectedContractId = $request->input('selectedContractId');
        $id = $request->input('id');

        $salesListDraft = SalesListDraft::where('contract_id', $selectedContractId)
                                        ->where('id', $id)
                                        ->first();

        if ($salesListDraft) {
            return response()->json([
                'price_json' => $salesListDraft->price_json
            ]);
        } else {
            return response()->json([
                'message' => 'Data not found'
            ], 404);
        }
    }
    

    public function fetchPriceJson($id)
    {
        // Fetch the record by ID from the sales_list_draft table
        $salesDraft = SalesListDraft::find($id);

        // Check if the record exists
        if (!$salesDraft) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        // Return the price_json as a JSON response
        return response()->json([
            'price_json' => json_decode($salesDraft->price_json)
        ]);
    }


 
public function updateVariableData(Request $request)
{
    $id = $request->input('id');
    $variableData = json_decode($request->input('variableData'), true);

    $salesListDraft = SalesListDraft::find($id);
    $lastRow = SalesListDraft::latest()->first();

    if ($salesListDraft) {
        $salesListDraft->variable_json = $variableData;
        $salesListDraft->save();

        return response()->json(['message' => 'Variable data updated successfully']);
    } else {
            $lastRow->variable_json = $variableData;
            $lastRow->save();
            return response()->json(['message' => 'Data saved successfully in the last row']);
    }
}

    // for save variable json data in SalesListDraft table
//     public function savePriceJsonData(Request $request)
//     {
//         // Get the variable data from the request
//         $variableData = $request->input('priceJsonData');
//         $id = $request->input('id');
//         $priceValues = $request->input('priceValues');

// /********** */
//         $dynamicminRange = htmlspecialchars($priceValues['dynamicminRange'] ?? $priceValues['fixedvalue'] ?? '');
//         $currency = htmlspecialchars($priceValues['currency']);
//         $paymentMaxRange = htmlspecialchars($priceValues['paymentMaxRange']);
//         $frequency = htmlspecialchars($priceValues['frequency']);
//         $payments = $priceValues['payments'];

//         $amountValues = $priceValues['amountValues'];
//         $dueDateValues = $priceValues['dueDateValues'];

//         // Convert includeonprice to boolean
//         $includeonprice = filter_var($priceValues['includeonprice'], FILTER_VALIDATE_BOOLEAN);
//         $vatpercentage = $priceValues['vatpercentage'];
//         $priceWithVat = (floatval($vatpercentage) * floatval($dynamicminRange) / 100) + floatval($dynamicminRange);

//         $totalCheck = 0;

//         for ($i = 0; $i < $paymentMaxRange; $i++) {
//             // Retrieve the corresponding amount and due date from the arrays
//             $formattedPaymentAmount = number_format(floatval($amountValues[$i]), 2, ',', '.');
//             $paymentAmount = floatval($amountValues[$i]);
//             $totalCheck += $paymentAmount;
//         }


//         $expectedTotal = $includeonprice ? $priceWithVat : floatval($dynamicminRange);
        
//         // Allow a discrepancy of up to 0.80

//         $acceptableDifference = 0.80;

//         if (abs($totalCheck - $expectedTotal) > $acceptableDifference) {
//             return response()->json([
//                 'error' => 'The total installment is not the same as the full price',
//                 'totalCheck' => $totalCheck,
//                 'expectedTotal' => $expectedTotal,
//                 'difference' => abs($totalCheck - $expectedTotal)
//             ], 500);
//         }


//         //***** */

//         $lastRow = SalesListDraft::latest()->first();

//         if ($id) {
//             // If ID is provided, try to find the contract by ID
//             $existingContract = SalesListDraft::find($id);

//             if ($existingContract) {
//                 // If contract exists, update its variable_json column with the new data
//                 $existingContract->update(['price_json' => $variableData]);
//                 return response()->json(['message' => 'Data saved successfully for the contract with ID ' . $id]);
//             } else {
//                 // If contract with provided ID not found, return an error response
//                 return response()->json(['status' => 'error', 'message' => 'Contract not found']);
//             }
//         } else {
//             $lastRow->price_json = $variableData;
//             $lastRow->save();
//             return response()->json(['message' => 'Data saved successfully in the last row']);
//         }
//     }

public function savePriceJsonData(Request $request)
{
    // Get the JSON data from the request
    $priceValues = $request->input('priceValues');
    $priceValuesforSave= $request->input('priceValuesforSave');
   
    $id = $request->input('id');

    $dynamicminRange = htmlspecialchars($priceValues['dynamicminRange'] ?? $priceValues['fixedvalue'] ?? '');
    $currency = htmlspecialchars($priceValues['currency']);
    $paymentMaxRange = htmlspecialchars($priceValues['paymentMaxRange']);
    $frequency = htmlspecialchars($priceValues['frequency']);
    $payments = $priceValues['payments'];

    $amountValues = $priceValues['amountValues'];
    $dueDateValues = $priceValues['dueDateValues'];

    // Convert includeonprice to boolean
    $includeonprice = filter_var($priceValues['includeonprice'], FILTER_VALIDATE_BOOLEAN);
    $vatpercentage = $priceValues['vatpercentage'];
    $priceWithVat = (floatval($vatpercentage) * floatval($dynamicminRange) / 100) + floatval($dynamicminRange);

    $totalCheck = 0;

    for ($i = 0; $i < $paymentMaxRange; $i++) {
        // Retrieve the corresponding amount and due date from the arrays
        $formattedPaymentAmount = number_format(floatval($amountValues[$i]), 2, ',', '.');
        $paymentAmount = floatval($amountValues[$i]);
        $totalCheck += $paymentAmount;
    }


    $expectedTotal = $includeonprice ? $priceWithVat : floatval($dynamicminRange);
    
    // Allow a discrepancy of up to 0.80

    $acceptableDifference = 0.80;

    if (abs($totalCheck - $expectedTotal) > $acceptableDifference) {
        return response()->json([
            'error' => 'The total installment is not the same as the full price',
            'totalCheck' => $totalCheck,
            'expectedTotal' => $expectedTotal,
            'difference' => abs($totalCheck - $expectedTotal)
        ], 500);
    }

    // Convert the full JSON data to a string to save it in the database
    //$jsonData = json_encode($priceValues, JSON_PRETTY_PRINT);
    $jsonData = $priceValuesforSave;

    // Logic for saving or updating the record in the database
    if ($id) {
        // If ID is provided, try to find the contract by ID
        $existingContract = SalesListDraft::find($id);

        if ($existingContract) {
            // If the contract exists, update its price_json column with the new data
            $existingContract->update(['price_json' => $jsonData]);
            return response()->json(['message' => 'Data saved successfully for the contract with ID ' . $id]);
        } else {
            // If the contract with the provided ID is not found, return an error response
            return response()->json(['status' => 'error', 'message' => 'Contract not found']);
        }
    } else {
        // If no ID is provided, save the data to the latest record
        $lastRow = SalesListDraft::latest()->first();

        if ($lastRow) {
            $lastRow->price_json = $jsonData;
            $lastRow->save();
            return response()->json(['message' => 'Data saved successfully in the last row']);
        } else {
            // Handle the case where there is no existing record to update
            return response()->json(['status' => 'error', 'message' => 'No record found to update'], 404);
        }
    }
}



     // for save variable json data in SalesListDraft table---------------------------------
     public function saveVariableData(Request $request)
    {
        // Get the variable data from the request
        $variableData = $request->input('variableData');
        $id = $request->input('id');

        $lastRow = SalesListDraft::latest()->first();

        if ($id) {
            // If ID is provided, try to find the contract by ID
            $existingContract = SalesListDraft::find($id);

            if ($existingContract) {
                // If contract exists, update its variable_json column with the new data
                $existingContract->update(['variable_json' => $variableData]);
                return response()->json(['message' => 'Data saved successfully for the contract with ID ' . $id]);
            } else {
                // If contract with provided ID not found, return an error response
                return response()->json(['status' => 'error', 'message' => 'Contract not found']);
            }
        } else {
            $lastRow->variable_json = $variableData;
            $lastRow->save();
            return response()->json(['message' => 'Data saved successfully in the last row']);
        }
    }
    
    

       // for save variable json data in SalesListDraft table
       public function saveEditedVariableData(Request $request)
       {
               // Get the variable data and the ID from the request
            $variableData = $request->input('variableData');
            $id = $request->input('id');

            // Find the draft in the database by its ID
            $draft = SalesListDraft::find($id);

            if ($draft) {
                // Update the 'variable_json' column with the new variable data
                $draft->variable_json = $variableData;
                $draft->save();  // Save the changes to the database

                // Return a response indicating success
                return response()->json(['message' => 'Variable data updated successfully!'], 200);
            } else {
                // Return a response indicating the draft was not found
                return response()->json(['message' => 'Draft not found'], 404);
            }
       }

    
     // testing method
     public function generateHtmlToPDF()
     {
         $html = '<h1>Generate html to PDF</h1>
                  <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry<p>';
         
         $pdf= PDF::loadHTML($html);
        
         return $pdf->download('invoice.pdf');
        
     }

    public function deletePdf(Request $request)
    {
        // Get the URL of the PDF file to be deleted from the request
        $pdfUrl = $request->input('pdfUrl');

        // Extract the filename from the URL
        $filename = basename($pdfUrl);

        // Delete the PDF file from the storage folder
        Storage::disk('public')->delete('pdf/' . $filename);

        // Return a success response
        return response()->json(['message' => 'PDF deleted successfully']);
    }

//**************** */

private function convertImagePaths($htmlContent)
{
    // Convert all image paths to absolute URLs
    return preg_replace_callback('/<img[^>]+src="([^">]+)"/', function ($matches) {
        $src = $matches[1];
        // If the src is already an absolute URL, return it as is
        if (filter_var($src, FILTER_VALIDATE_URL)) {
            return $matches[0];
        }

        // Otherwise, convert the relative path to an absolute URL
        $absoluteUrl = url($src);
        return str_replace($src, $absoluteUrl, $matches[0]);
    }, $htmlContent);
}

 

    private function getDateByFrequency($frequency, $offset)
    {
        $offset = $offset - 1; // Adjust offset to be zero-based
        $currentDate = new DateTime();
        switch ($frequency) {
            case 'daily':
                $currentDate->modify("+{$offset} day");
                break;
            case 'biweekly':
                $currentDate->modify("+" . ($offset * 14) . " day");
                break;
            case 'weekly':
                $currentDate->modify("+" . ($offset * 7) . " day");
                break;
            case 'monthly':
                $currentDate->modify("+{$offset} month");
                break;
            case 'annually':
                $currentDate->modify("+{$offset} year");
                break;
            default:
                break;
        }
        return $currentDate->format('d/m/Y');
    }
    
 
     // get selected editor content 
     public function geteditorcontent(Request $request)
     {
         // Get the selected contract ID from the request
         $selectedContract = $request->input('selectedContractId');
         
         // Retrieve the contract with the specified ID along with its editor_content field
         $contract = Contract::select('editor_content')->find($selectedContract);
     
         // Check if the contract exists
         if ($contract) {
             // Return the editor_content data
             return response()->json(['editor_content' => $contract->editor_content]);
         } else {
             // If the contract does not exist, return an error response
             return response()->json(['error' => 'Contract not found'], 404);
         }
     }
     


    // get price lists 
    public function getAllPriceLists(Request $request)
    {
        $selectedContract = $request->input('selectedContractId');

        // Query to get the price_id from the contracts table
        $priceId = Contract::where('id', $selectedContract)->value('price_id');

        // Query to get all values from the price_lists table based on the price_id
        $priceLists = PriceList::where('id', $priceId)->first();

        // Return the price lists data
        return response()->json($priceLists);
    }

 
    

    public function getVariablesForEdit(Request $request)
    {
        $contractID = $request->input('selectedContractId');
        $id = $request->input('id');
    
        // Query using Eloquent
        $draft = SalesListDraft::where('contract_id', $contractID)
                               ->where('id', $id)
                               ->first();
    
        if ($draft) {
            return response()->json([
                'success' => true,
                'data' => $draft->variable_json
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No entry found for the specified ID and Contract ID'
            ]);
        }
    }
    

    // ProductController.php
    public function getContracts(Request $request)
    {
        $productName = $request->input('product_name');

        $product = Product::where('product_name', $productName)->first();
 

        $productId = $product->id;

        // Retrieve the contract ID from the request
        $id = $request->input('id');

        if ($id) {
            // If ID is provided, try to find the contract by ID
            $existingContract = SalesListDraft::find($id);

            if ($existingContract) {
                // If contract exists, update its details
                $existingContract->update([
                    'product_id' => $productId,
                    'product_name' => $productName
                ]);
            } else {
                // If contract with provided ID not found, return an error response
                return response()->json(['status' => 'error', 'message' => 'Contract not found']);
            }
        } else {
            $lastRow = SalesListDraft::latest()->first();
            // If ID is not provided, create a new contract entry
            $lastRow->update([
                'product_id' => $productId,
                'product_name' => $productName
            ]);
        }

        // Query the contracts table to get all contracts relevant to the product ID
        $contracts = Contract::where('product_id', $productId)->get(['id', 'contract_name']);

        if ($contracts->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Contracts not found']);
        }

        // Prepare the response data
        $contractData = $contracts->map(function ($contract) {
            return [
                'id' => $contract->id,
                'contract_name' => $contract->contract_name
            ];
        });

        return response()->json(['status' => 'success', 'contracts' => $contractData]);
    }


    public function getProducts(Request $request)
    {
        $sellerName = $request->input('seller_name');
        
        // Get the user's sales details based on the seller name
        $salesDetail = SalesDetails::where('name', $sellerName)->first();

        if (!$salesDetail) {
            return response()->json(['status' => 'error', 'message' => 'Sales details not found']);
        }

        // Get the sales ID from the sales details
        $salesId = $salesDetail->id; // Here, $salesId will be 2 

        // Get all product IDs associated with the sales ID
        $productToSales = ProductToSales::where('sales_id', $salesId)->get();    

        // Here, $productIds will be an array containing 1 and 3
        $productIds = $productToSales->pluck('product_id');

        // Get the product details for each product ID
        $products = Product::whereIn('id', $productIds)->get();

        if ($products->isNotEmpty()) {
            // Extract product names
            // Assuming the product name column is 'product_name' in your 'products' table
            $productNames = $products->pluck('product_name')->toArray(); 

            // Here, $productNames will be an array containing 'lufy producta' and 'my preoduct'
            
            return response()->json(['status' => 'success', 'products' => $productNames]);
        }
        
        return response()->json(['status' => 'error', 'message' => 'Product not found']);
    }


    public function index()
    {
        $products = Product::all();
        return view('ProductList', compact('products'));
    }


    public function deleteproduct($id)
    {
        $Product = Product::findOrFail($id); // Find the variable by ID
        $Product->delete(); // Delete the variable
        return redirect()->back()->with('success', 'Variable deleted successfully'); // Redirect back with success message
    }

    public function saveProduct(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
        
            'productName' => 'required',
            'description' => 'required',
    
        
        ]);

    // Create a new product instance
    $product = new Product;
 
    $product->product_name = $validatedData['productName'];
    $product->description = $validatedData['description'];
   
 

    // Save the product to the database
    $product->save();

    // Optionally, you can return a response or redirect to another page
    return response()->json(['message' => 'Product saved successfully']);
    }

 
    public function update(Request $request, $id)
    {
        // Validate the request data
        $request->validate([
            'product_name' => 'required|string',
            'description' => 'required|string',
            
        ]);
    
        // Find the variable by ID
        $Product = Product::findOrFail($id);
    
        // Update variable details
        $Product->product_name = $request->input('product_name');
        $Product->description = $request->input('description');
    
        // Check if Description field is provided
        if ($request->has('description')) {
            $Product->description = $request->input('description');
        }
    
        // Save the updated variable
        $Product->save();
    
        // Return a response indicating success
        return response()->json(['success' => true, 'message' => 'Variable updated successfully']);
 
    }

}



