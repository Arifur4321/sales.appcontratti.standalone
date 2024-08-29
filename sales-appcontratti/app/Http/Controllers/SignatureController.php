<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesListDraft;
use Dropbox\Sign\Client;
use Dropbox\Sign\Configuration;
use Dropbox\Sign\Api\SignatureRequestApi;
use Dropbox\Sign\Model\SignatureRequest;
use Dropbox\Sign\Model\Signer;
use Dropbox\Sign\Api\EmbeddedApi;
use Dropbox\Sign\ApiException;
use Dropbox\Sign\Model\SignatureRequestCreateEmbeddedRequest;
use Dropbox\Sign\Model\SignatureRequestGetResponse;
use Dropbox\Sign\Model\SubSignatureRequestSigner;
use Dropbox\Sign\Model\SubSigningOptions;
use GuzzleHttp\Client as CloseClient;

use Illuminate\Support\Facades\DB;
use App\Models\AppConnection;

use \Mpdf\Mpdf;
 
use Illuminate\Support\Facades\Storage;
use PDF;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\TcpdfFpdi;
use App\Models\HeaderAndFooterContractpage;
use App\Models\HeaderAndFooter;
use App\Mail\SignatureConfirmationMail;
use Illuminate\Support\Facades\Mail;

class SignatureController extends Controller
{
    private $closeioApiKey;
    private $closeClient;

    public function __construct()
    {
        $this->closeioApiKey = env('CLOSEIO_API_KEY');
        $this->closeClient = new CloseClient();
    }

//-------------------------------------------------   

public function showSignDocument($id)
{
    $pdfSignature = SalesListDraft::findOrFail($id);

    if ($pdfSignature->status === 'signed') {
        return view('view_signed_document', ['pdfSignature' => $pdfSignature]);
    }

    return view('sign_document', [
        'pdfSignature' => $pdfSignature,
        'headerContent' => '', // Replace with your header content logic
        'footerContent' => '', // Replace with your footer content logic
        'htmlContent' => $this->replaceImageTagsWithClickableSignaturePad($pdfSignature->main_contract),
    ]);
}

public function loadDocumentForSigning($id)
{
    // Retrieve the record by the provided ID
    $pdfSignature = SalesListDraft::findOrFail($id);

    // Check if the document is already signed
    if ($pdfSignature->status === 'signed') {
        // If signed, redirect to view the signed PDF
        return view('view_signed_document', [
            'pdfSignature' => $pdfSignature,
        ]);
    }

    // If not signed, show the document with the signature pad
    $htmlContent = $pdfSignature->main_contract;
    $htmlContent = $this->replaceImageTagsWithClickableSignaturePad($htmlContent);

    // Fetch header and footer content from the database or fallback to defaults
    $contractID = $pdfSignature->contract_id;
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

    // Paginate the HTML content into pages
    $paginatedContent = $this->paginateHtmlContent($htmlContent);

    // Pass the HTML content, header, footer, and watermark information to the view
    return view('sign_document', [
        'pdfSignature' => $pdfSignature,
        'paginatedContent' => $paginatedContent,
        'headerContent' => $headerContent,
        'footerContent' => $footerContent,
        'watermarkText' => 'Giacomo Freddi',
    ]);
}

/**
 * Function to paginate the HTML content into chunks for each page.
 */
private function paginateHtmlContent($htmlContent)
{
    // Example: Split by paragraphs (This is a simple example, you may need to customize this)
    $contentArray = explode('</p>', $htmlContent);
    $pages = [];
    $currentPageContent = '';

    foreach ($contentArray as $content) {
        $currentPageContent .= $content . '</p>';
        // Assuming each page can hold 4 paragraphs for demonstration
        if (str_word_count($currentPageContent) > 500) {  // adjust this condition as necessary
            $pages[] = $currentPageContent;
            $currentPageContent = '';
        }
    }

    if (!empty($currentPageContent)) {
        $pages[] = $currentPageContent;
    }

    return $pages;
}


private function replaceImageTagsWithClickableSignaturePad($content)
{
    $pattern = '/<img[^>]*src="https:\/\/i\.ibb\.co\/71g553C\/FIRMA-QUI\.jpg"[^>]*>/i';

    return preg_replace_callback($pattern, function ($matches) {
        preg_match('/style="([^"]*)"/i', $matches[0], $styleMatch);
        $style = $styleMatch[1] ?? '';

        if (strpos($style, 'float: right;') !== false) {
            return '<div class="sig-container float-right" style="margin: 10px;" onclick="openSignatureModal(this)">Click to Sign</div>';
        } elseif (strpos($style, 'float: left;') !== false) {
            return '<div class="sig-container float-left" style="margin: 10px;" onclick="openSignatureModal(this)">Click to Sign</div>';
        } else {
            return '<div class="sig-container" style="margin: 10px;" onclick="openSignatureModal(this)">Click to Sign</div>';
        }
    }, $content);
}


// main submit method 

public function submitSignature(Request $request, $id)
{
    try {
        // Retrieve the record by the provided ID
        $pdfSignature = SalesListDraft::findOrFail($id);

        // Validate the signatures
        $request->validate([
            'signatures' => 'required|array',
        ]);

        $signatures = $request->input('signatures');

        // If applying the same signature everywhere, replicate it for all placeholders
        if (count($signatures) === 1) {
            $placeholderCount = preg_match_all('/<img[^>]*src="https:\/\/i\.ibb\.co\/71g553C\/FIRMA-QUI\.jpg"[^>]*>/i', $pdfSignature->main_contract);
            $signatures = array_fill(0, $placeholderCount, $signatures[0]); // Duplicate the single signature for all placeholders
        }

        // Load the HTML content containing the signer tag
        $htmlContent = $pdfSignature->main_contract;

        // Replace each placeholder with its corresponding signature
        $updatedHtmlContent = $this->replaceImageTagsWithSignatureTags($htmlContent, $signatures);

        // Fetch header and footer content from the database or fallback to defaults
        $contractID = $pdfSignature->contract_id;
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

        // Create new mPDF document with adjusted margins based on header/footer presence
        $mpdf = new Mpdf([
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
                div.sig-container { margin: 10px; display: flex; justify-content: center; align-items: center; height: 50px; }
                div.sig-container.float-right { justify-content: flex-end; }
                div.sig-container.float-left { justify-content: flex-start; }
            </style>
            <div>' . $updatedHtmlContent . '</div>';

        // Write the content to the PDF
        $mpdf->WriteHTML($contentHTML);

        $filename = 'signed_contract_' . $id . '.pdf';
        $outputPath = storage_path('app/public/pdf/' . $filename);

        // Save the signed PDF
        $mpdf->Output($outputPath, 'F');

        // Update the database with the signed PDF details
        $pdfSignature->status = 'signed';
        $pdfSignature->selected_pdf_name = $filename;
        $pdfSignature->save();

        // Attach the signed PDF and send the confirmation email
        Mail::to($pdfSignature->recipient_email)->send(new SignatureConfirmationMail($pdfSignature, $outputPath));

        // Return a JSON response with the redirect URL
        return response()->json([
            'success' => true,
            'redirectUrl' => route('view.signed.document', ['id' => $pdfSignature->id]),
        ]);

    } catch (\Exception $e) {
        \Log::error('Error signing document: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'An error occurred while processing the document. Please try again.'], 500);
    }
}


private function replaceImageTagsWithSignatureTags($content, $signatures)
{
    $pattern = '/<img[^>]*src="https:\/\/i\.ibb\.co\/71g553C\/FIRMA-QUI\.jpg"[^>]*>/i';
    $index = 0;

    return preg_replace_callback($pattern, function ($matches) use (&$index, $signatures) {
        $signatureImageTag = '<img src="' . $signatures[$index] . '" width="150" height="100">';
        $index++;
        return '<div class="sig-container" style="margin: 10px;">' . $signatureImageTag . '</div>';
    }, $content);
}

public function sendConfirmationEmail(Request $request, $id)
{
    try {
        $pdfSignature = SalesListDraft::findOrFail($id);
        $recipientEmail = $pdfSignature->recipient_email;

        // Ensure the email is valid
        if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['success' => false, 'message' => 'Invalid recipient email.'], 400);
        }

        // Send the email
        Mail::to($recipientEmail)->send(new SignatureConfirmationMail($pdfSignature));

        // Redirect to the view_signed_document view
        return view('view_signed_document', ['pdfSignature' => $pdfSignature]);

    } catch (\Exception $e) {
        \Log::error('Error sending confirmation email: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'An error occurred while sending the confirmation email.'], 500);
    }
}


public function downloadSignedPdf($id)
{
    $pdfSignature = SalesListDraft::findOrFail($id);
    $pdfFilePath = storage_path('app/public/pdf/' . $pdfSignature->selected_pdf_name);

    if (!file_exists($pdfFilePath)) {
        return response()->json(['success' => false, 'message' => 'Signed PDF file not found.'], 404);
    }

    return response()->download($pdfFilePath)->deleteFileAfterSend(false);
}

 
public function viewSignedDocument($id)
{
    // Retrieve the record by the provided ID
    $pdfSignature = SalesListDraft::findOrFail($id);

    // Return the view with the signed PDF data
    return view('view_signed_document', [
        'pdfSignature' => $pdfSignature,
    ]);
}



 
// i attach some screenshot . analyze it . so now i need to add a feature like hellosign
// in the screenshot there is a button continue appear at the top right side after all signature finished by user . 
// after click on continue another two button Edit and I agree button should appear .
// if user click on Edit then give the option to edit the signature .
// if the user click on I agree the show the pop up that thank you for your signature .and a confirmation email to the  recipient_email which
// can be found in the table sales_list_draft column name  recipient_email based on id row

//---------------------------------------------------
  

    public function checkSignatureStatus()
    {
        $config = \Dropbox\Sign\Configuration::getDefaultConfiguration();
        $config->setUsername(env('HELLOSIGN_API_KEY'));
    
        $signatureRequestApi = new \Dropbox\Sign\Api\SignatureRequestApi($config);
    
        $salesListDrafts = SalesListDraft::all();
    
        $statuses = [];
    
        foreach ($salesListDrafts as $salesListDraft) {
            $envelopeId = $salesListDraft->envelope_id;
    
            // Check if envelopeId is not null or empty
            if (empty($envelopeId)) {
                $statuses[$salesListDraft->id] = 'no envelope id';
                continue;
            }
    
            try {
                $result = $signatureRequestApi->signatureRequestGet($envelopeId);
                $signatureRequest = $result->getSignatureRequest();
    
                $allStatuses = [];
                foreach ($signatureRequest->getSignatures() as $signature) {
                    $allStatuses[] = $signature->getStatusCode();
                }
    
                // Determine the overall status
                $overallStatus = 'pending';
                if (in_array('declined', $allStatuses)) {
                    $overallStatus = 'declined';
                } elseif (in_array('signed', $allStatuses)) {
                    $overallStatus = 'signed';
    
                    // Only call addCloseIoNoteForSigned if price_id is not equal to 1
                    if ($salesListDraft->price_id != 1) {
                        DB::transaction(function () use ($salesListDraft) {
                            // Lock the row for update to prevent duplicate processing
                            $salesListDraft->lockForUpdate();
    
                            if ($salesListDraft->price_id != 1) { // Double-check within the transaction
                                $this->addCloseIoNoteForSigned(
                                    $salesListDraft->recipient_email, 
                                    $salesListDraft->contract_name,
                                    $salesListDraft->product_name
                                );
    
                                // Update the price_id to mark that the note has been sent
                                $salesListDraft->price_id = 1; // You can use any non-null value to indicate that the note has been sent
                                $salesListDraft->save();
                            }
                        });
                    }
                } elseif (in_array('viewed', $allStatuses)) {
                    $overallStatus = 'viewed';
                } elseif (in_array('sent', $allStatuses)) {
                    $overallStatus = 'pending';
                }
    
                // Update the status in the database
                $salesListDraft->status = $overallStatus;
                $salesListDraft->save();
    
                // Add to statuses array for response
                $statuses[$envelopeId] = $overallStatus;
    
            } catch (\Exception $e) {
                // Handle individual errors without stopping the entire process
                $statuses[$envelopeId] = 'error';
                \Log::error("Error checking status for envelope ID $envelopeId: " . $e->getMessage());
            }
        }
    
        return response()->json(['statuses' => $statuses]);
    }
    


    

    private function addCloseIoNoteForSigned($recipientEmail, $contractName, $product_name)
    {
        try {
            // Step 1: Retrieve the signed note template from the AppConnection table
            $appConnection = AppConnection::where('type', 'Close')->first();

            $noteTemplate = '';
            if ($appConnection && isset($appConnection->api_key)) {
                $apiData = json_decode($appConnection->api_key, true);
                if (isset($apiData['signed'])) {
                    $noteTemplate = $apiData['signed'];
                }
            }

            // Default note text if no template is found in the database
            if (empty($noteTemplate)) {
                $noteTemplate = "al cliente è arrivato il contratto: \$contract_name$, del prodotto: \$product_name$ il contratto è stato firmato con successo.";
            }

            // Replace placeholders with actual values
            $noteText = str_replace(['$contract_name$', '$product_name$'], [$contractName, $product_name], $noteTemplate);

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
                \Log::info("Adding note with Contract Name: {$contractName}, Product Name: {$product_name}");

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

                // Log success message
                \Log::info("Successfully added note to Close.io for lead {$leadId}");
            } else {
                \Log::info("No lead found for email {$recipientEmail}");
                throw new \Exception("No lead found for email {$recipientEmail}");
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            \Log::error("RequestException while communicating with Close.io API: " . $e->getMessage());
            if ($e->hasResponse()) {
                $responseBody = $e->getResponse()->getBody()->getContents();
                \Log::error("Response: " . $responseBody);
            }
            throw $e;
        } catch (\Exception $e) {
            \Log::error("General exception while communicating with Close.io API: " . $e->getMessage());
            throw $e;
        }
    }

}


  


