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
use Illuminate\Support\Facades\Auth;
use \Mpdf\Mpdf;

use App\Models\Company;
use Illuminate\Support\Facades\Log;

use App\Models\Contract;  
use Illuminate\Support\Facades\Storage;
use PDF;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\TcpdfFpdi;
use App\Models\HeaderAndFooterContractpage;
use App\Models\HeaderAndFooter;
use App\Mail\SignatureConfirmationMail;
use Illuminate\Support\Facades\Mail;
use App\Models\SalesDetails; 
use App\Models\Template;

use GuzzleHttp\Client as ActiveCampaignClient; // Alias for ActiveCampaign client

class SignatureController extends Controller
{
    private $closeioApiKey;
    private $closeClient;

    private $activeCampaignClient;
    
    public function __construct()
    {
        $this->closeioApiKey = env('CLOSEIO_API_KEY');
        $this->closeClient = new CloseClient();
    
                // Set up the ActiveCampaign base URL
         $this->activeCampaignBaseUrl = 'https://giacomofreddi.api-us1.com/api/3/';

           // Initialize the ActiveCampaign client without the API key (will set it later)
        $this->activeCampaignClient = new ActiveCampaignClient([
               'base_uri' => $this->activeCampaignBaseUrl,
        ]);
   
        
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


public function getCompanyName($id)
{
    // Get the sales_list_draft record based on the ID
    $salesDraft = SalesListDraft::find($id);

    if ($salesDraft) {
        // Check if company_id is 1
        if ($salesDraft->company_id == 1) {
            return response()->json(['company_name' => 'GF SRL']);
        }

        // Get the company record from the companies table based on company_id
        $company = Company::find($salesDraft->company_id);

        if ($company) {
            // Return the company_name from the companies table
            return response()->json(['company_name' => $company->company_name]);
        }
    }

    // Default response if something goes wrong
    return response()->json(['company_name' => 'GF SRL']);
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
            return '<div class="sig-container float-right" style="margin: 10px;" onclick="openSignatureModal(this)">  ' . __('translation.Click to Sign') . ' </div>';
        } elseif (strpos($style, 'float: left;') !== false) {
            return '<div class="sig-container float-left" style="margin: 10px;" onclick="openSignatureModal(this)">  ' . __('translation.Click to Sign') . ' </div>';
        } else {
            return '<div class="sig-container" style="margin: 10px;" onclick="openSignatureModal(this)">  ' . __('translation.Click to Sign') . ' </div>';
        }
    }, $content);
}

// this is my main controller method where 

// public function submitSignature(Request $request, $id)
// {
//     try {
//         // Retrieve the record by the provided ID
//         $pdfSignature = SalesListDraft::findOrFail($id);

//         // Validate the signatures
//         $request->validate([
//             'signatures' => 'required|array',
//         ]);

//         $signatures = $request->input('signatures');

//         // Check for consistency in signature counts
//         $htmlContent = $pdfSignature->main_contract;
//         $placeholderCount = preg_match_all('/<img[^>]*src="https:\/\/i\.ibb\.co\/71g553C\/FIRMA-QUI\.jpg"[^>]*>/i', $htmlContent);

//         if (count($signatures) !== $placeholderCount) {
//             throw new \Exception('Mismatch between number of placeholders and signatures.');
//         }

//         // Load the HTML content containing the signer tag
//         $updatedHtmlContent = $this->replaceImageTagsWithSignatureTags($htmlContent, $signatures);

//         // Fetch header and footer content
//         $contractID = $pdfSignature->contract_id;
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

//         // Default content for header and footer if not found
//         $headerContent = empty($headerContent) ? '<div style="text-align: center; font-weight: bold;"></div>' : '<div style="text-align: center; font-weight: bold;">' . $headerContent . '</div>';
//         $footerContent = empty($footerContent) ? '<div style="text-align: center;"></div>' : '<div style="text-align: center;">' . $footerContent . '</div>';

//         // Initialize mPDF
//         $mpdf = new Mpdf([
//             'format' => 'A4',
//             'margin_top' => $headerContent ? 45 : 5,
//             'margin_bottom' => $footerContent ? 40 : 5,
//             'margin_left' => 15,
//             'margin_right' => 15,
//         ]);

//         $mpdf->SetTitle('Document for Signature');
//         $mpdf->SetAuthor('Your Company');
//         $mpdf->SetWatermarkText('Giacomo Freddi');
//         $mpdf->showWatermarkText = true;
//         $mpdf->watermark_font = 'DejaVuSansCondensed';
//         $mpdf->watermarkTextAlpha = 0.1;

//         $mpdf->SetHTMLHeader($headerContent);

//         $footerHTML = '
//             <div style="width: 100%; font-size: 10px; display: flex; justify-content: space-between; align-items: center; position: relative;">
//                 <div style="flex: 1; text-align: center;">' . $footerContent . '</div>
//                 <div style="flex: 1; text-align: right;">Page {PAGENO}/{nbpg}</div>
//             </div>
//         ';
//         $mpdf->SetHTMLFooter($footerHTML);

//         $contentHTML = '
//             <style>
//                 body { font-family: Arial, sans-serif; font-size: 12px; }
//                 ul { padding-left: 20px; }
//                 li { margin-bottom: 5px; }
//                 img { max-width: 100%; height: auto; display: block; margin: auto; }
//                 table { width: 100%; border-collapse: collapse; }
//                 table, th, td { border: 1px solid black; }
//                 th, td { padding: 8px; text-align: left; }
//                 div.sig-container { margin: 10px; display: flex; justify-content: center; align-items: center; height: 50px; }
//                 div.sig-container.float-right { justify-content: flex-end; }
//                 div.sig-container.float-left { justify-content: flex-start; }
//             </style>
//             <div>' . $updatedHtmlContent . '</div>';

//         // Write the content to the PDF
//         $mpdf->WriteHTML($contentHTML);

//         $filename = 'signed_contract_' . $id . '.pdf';
//         $outputPath = storage_path('app/public/pdf/' . $filename);

//         // Save the signed PDF
//         $mpdf->Output($outputPath, 'F');

//         // Update the database with the signed PDF details
//         $pdfSignature->status = 'signed';
//         $pdfSignature->selected_pdf_name = $filename;
//         $pdfSignature->save();

//         // Attach the signed PDF and send the confirmation email
//         Mail::to($pdfSignature->recipient_email)->send(new SignatureConfirmationMail($pdfSignature, $outputPath));

//         return response()->json([
//             'success' => true,
//             'redirectUrl' => route('view.signed.document', ['id' => $pdfSignature->id]),
//         ]);

//     } catch (\Exception $e) {
//         \Log::error('Error signing document: ' . $e->getMessage());
//         return response()->json(['success' => false, 'message' => 'An error occurred while processing the document. Please try again.'], 500);
//     }
// }

//------------------ testing audit trial ----------------------------------------------

public function trackViewed($id, Request $request)
{
    try {
        // Capture the exact time the document was viewed
        $viewedAt = now()->timezone('Europe/Rome');
        $viewedIp = $request->ip(); // Get real IP address

        // Check if latitude and longitude are provided
        $latitude = $request->input('lat');
        $longitude = $request->input('lon');

        if ($latitude && $longitude) {
            // If lat/lon are provided, get the location from Google Maps API
            $viewedLocation = $this->getLocationFromCoordinates($latitude, $longitude);
        } else {
            // Fallback to IP-based location if no coordinates are provided
            $viewedLocation = $this->getGeolocationFromIP($viewedIp);
        }

        // Store the viewed data temporarily in the session
        session([
            'viewedAt' => $viewedAt,
            'viewedIp' => $viewedIp,
            'viewedLocation' => $viewedLocation,
        ]);

        return response()->json([
            'success' => true,
            'viewedAt' => $viewedAt->format('d / m / Y H:i'),
            'viewedIp' => $viewedIp,
            'viewedLocation' => $viewedLocation,
        ]);
    } catch (\Exception $e) {
        \Log::error('Error tracking viewed event: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Error tracking viewed event'], 500);
    }
}


private function getLocationFromCoordinates($latitude, $longitude)
{
    $apiKey = env('GOOGLE_MAPS_API_KEY');
    $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$latitude},{$longitude}&key={$apiKey}";

    try {
        $response = file_get_contents($url);
        $data = json_decode($response, true);

        if (isset($data['results'][0]['address_components'])) {
            foreach ($data['results'][0]['address_components'] as $component) {
                if (in_array('locality', $component['types'])) {
                    $city = $component['long_name'];
                }
                if (in_array('country', $component['types'])) {
                    $country = $component['long_name'];
                }
            }
            return isset($city) && isset($country) ? $city . ', ' . $country : 'Location not available';
        }

        return 'Location not available';
    } catch (\Exception $e) {
        \Log::error('Error fetching location from coordinates: ' . $e->getMessage());
        return 'Location not available';
    }
}

private function getGeolocationFromIP($ip)
{
    $apiKey = env('GOOGLE_MAPS_API_KEY');
    $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$ip}&key={$apiKey}";

    try {
        $response = file_get_contents($url);
        $data = json_decode($response, true);

        if (isset($data['results'][0]['address_components'])) {
            foreach ($data['results'][0]['address_components'] as $component) {
                if (in_array('locality', $component['types'])) {
                    $city = $component['long_name'];
                }
                if (in_array('country', $component['types'])) {
                    $country = $component['long_name'];
                }
            }
            return isset($city) && isset($country) ? $city . ', ' . $country : 'Location not available';
        }

        return 'Location not available';
    } catch (\Exception $e) {
        \Log::error('Error fetching location data from Google API: ' . $e->getMessage());
        return 'Location not available';
    }
}
 
public function viewDocument($documentId)
{
    // Call the method to capture viewing details (timestamp and IP)
    $this->captureViewingDetails($documentId);

    // Fetch the document details from the database
    $pdfSignature = SalesListDraft::findOrFail($documentId);

    // Display the document to the user in the view (HTML or PDF render view)
    return view('document.view', compact('pdfSignature'));
}

public function captureViewingDetails($documentId)
{
    // Check if viewing details already exist in the session to avoid overwriting on subsequent views
    if (!session()->has('viewedAt') && !session()->has('viewedIp')) {
        session([
            'viewedAt' => now()->timezone('Europe/Rome'),  // Capture current time
            'viewedIp' => request()->ip(),                 // Capture user's IP address
            'currentUser' =>  Auth::check() ? Auth::user()->email : 'Unknown user',
        ]);
    }

    return response()->json(['success' => true]);
}
 
// app/Http/Controllers/SignatureController.php

public function logView(Request $request, $id)
{
    // Capture the exact time when the user clicked the link
    $viewedAt = now()->timezone('Europe/Rome');
    $viewedIp = $request->ip();

    // Retrieve the record from the 'sales_list_draft' table
    $pdfSignature = SalesListDraft::findOrFail($id);

    // Check if the 'viewedAt' column is empty
    if (empty($pdfSignature->viewedAt)) {
        // Prepare the data in JSON format
        $viewedData = [
            'viewedAt' => $viewedAt->format('Y-m-d H:i:s'),
            'viewedIp' => $viewedIp,
        ];

        // Save the data in the 'viewedAt' column as JSON
        $pdfSignature->viewedAt = json_encode($viewedData);
        $pdfSignature->save();
    }

    // Redirect the user to the signing page
    return redirect()->route('sign.document', ['id' => $id]);
}

 
 public function submitSignature(Request $request, $id)
{
    try {

        // Now you have the email from the sales_details table
        $salesEmail = "1%Contract";

        $mobileNumber = $request->input('recipientMobile');

        // Retrieve the record by the provided ID
        $pdfSignature = SalesListDraft::findOrFail($id);

        // Validate the signatures
        $request->validate([
            'signatures' => 'required|array',
        ]);

        $signatures = $request->input('signatures');

        // Check for consistency in signature counts
        $htmlContent = $pdfSignature->main_contract;
        $placeholderCount = preg_match_all('/<img[^>]*src="https:\/\/i\.ibb\.co\/71g553C\/FIRMA-QUI\.jpg"[^>]*>/i', $htmlContent);

        if (count($signatures) !== $placeholderCount) {
            throw new \Exception('Mismatch between number of placeholders and signatures.');
        }

        // Load the HTML content containing the signer tag
        $updatedHtmlContent = $this->replaceImageTagsWithSignatureTags($htmlContent, $signatures);

        // Fetch header and footer content
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

        // Initialize mPDF
        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_top' => $headerContent ? 45 : 5,
            'margin_bottom' => $footerContent ? 40 : 5,
            'margin_left' => 15,
            'margin_right' => 15,
        ]);

        $mpdf->SetTitle('Document for Signature');
        $mpdf->SetAuthor('Your Company');


       // $mpdf->SetWatermarkText('Giacomo Freddi');
            // $mpdf->showWatermarkText = true;
            // $mpdf->watermark_font = 'DejaVuSansCondensed';
            // $mpdf->watermarkTextAlpha = 0.1;


          // Retrieve the record by the provided ID from SalesListDraft
            $pdfSignature = SalesListDraft::findOrFail($id);
            
            $companyId = $pdfSignature->company_id ?? null;
            $template = Template::where('company_id', $companyId)->first();
            $watermarkText = $template->watermark ?? null;

            if (!empty($watermarkText)) {
                // Set watermark if available
                $mpdf->SetWatermarkText($watermarkText);
                $mpdf->showWatermarkText = true;
                $mpdf->watermark_font = 'DejaVuSansCondensed';
                $mpdf->watermarkTextAlpha = 0.1;
            }
            


        $mpdf->SetHTMLHeader($headerContent);

        $footerHTML = '
            <div style="width: 100%; font-size: 10px; display: flex; justify-content: space-between; align-items: center; position: relative;">
                <div style="flex: 1; text-align: center;">' . $footerContent . '</div>
                <div style="flex: 1; text-align: right;">Page {PAGENO}/{nbpg}</div>
            </div>
        ';
        $mpdf->SetHTMLFooter($footerHTML);

        // Add the main document content to the PDF
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

        $mpdf->WriteHTML($contentHTML);

        // --- Capture dynamic IP and location data ---
        $signedIp = $request->ip(); // Capture IP for signing
        $signedAt = now()->timezone('Europe/Rome'); // Capture signed time in Italy's timezone

        // --- Retrieve viewing data from the database (stored in JSON format) ---
        $viewedData = json_decode($pdfSignature->viewedAt, true); // Decode JSON

        $viewedAt = isset($viewedData['viewedAt']) ? \Carbon\Carbon::parse($viewedData['viewedAt']) : null;
        $viewedIp = $viewedData['viewedIp'] ?? null;

        $invitedAt = $pdfSignature->created_at;  // Timestamp when the document was created/sent
        $formattedInvitedAt = \Carbon\Carbon::parse($invitedAt)->format('d / m / Y H:i:s');  // Adjust format as needed

        // --- Retrieve OTP sent timestamps from the OTPviewed column ---
        $otpSends = json_decode($pdfSignature->OTPviewed, true) ?? []; // Decode the OTPviewed JSON field
        
        // --- Add Audit Trail Page ---
        $mpdf->AddPage(); // Add a new page for the audit trail

        // Construct the audit trail content
        $auditTrailHTML = '
            <h2>Tracciabilità Audit</h2>
            <p><strong>Titolo:</strong> ' . $pdfSignature->contract_name . '</p>
            <p><strong>Nome del file:</strong> ' . $pdfSignature->selected_pdf_name . '</p>
            <p><strong>ID documento:</strong> ' . $pdfSignature->id . '</p>
            <p><strong>Stato:</strong> <span style="color:green;">Firmato</span></p>
            
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left;">Azione</th>
                        <th style="text-align:left;">Timestamp (UTC)</th>
                        <th style="text-align:left;">Dettagli</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Invitato</strong></td>
                        <td>' . $formattedInvitedAt . ' UTC</td>
                        <td>Invitato alla firma da ' . $salesEmail . ' da ' . $viewedIp . ' (IP: ' . $viewedIp . ')</td>
                    </tr>';

        // Add Visualizzato row using real data from JSON
        if ($viewedAt && $viewedIp) {
            $auditTrailHTML .= '
                    <tr>
                        <td><strong>Visualizzato</strong></td>
                        <td>' . $viewedAt->format('d / m / Y H:i:s') . ' UTC</td>
                        <td>Visualizzato da ' . $pdfSignature->recipient_email . ' da ' . $viewedIp . ' (IP: ' . $viewedIp . ')</td>
                    </tr>';
        } else {
            $auditTrailHTML .= '
                    <tr>
                        <td><strong>Visualizzato</strong></td>
                        <td>N/A</td>
                        <td>N/A</td>
                    </tr>';
        }

        // Add multiple rows for each OTP sent, based on the data from OTPviewed column
        foreach ($otpSends as $otpData) {
            $otpSentAt = isset($otpData['otp_sent_at']) ? \Carbon\Carbon::parse($otpData['otp_sent_at'])->format('d / m / Y H:i:s') : 'N/A';
            $mobileUsed = isset($otpData['mobile']) ? $otpData['mobile'] : 'N/A';

            $auditTrailHTML .= '
                    <tr>
                        <td><strong>Inviato OTP</strong></td>
                        <td>' . $otpSentAt . ' UTC</td>
                        <td>' . $mobileUsed . ' ricevuto OTP</td>
                    </tr>';
        }

        // Add final completion row
        $auditTrailHTML .= '
                    <tr>
                        <td><strong>Firmato</strong></td>
                        <td>' . $signedAt->format('d / m / Y H:i:s') . ' UTC</td>
                        <td>Firmato da ' . $pdfSignature->recipient_email . ' da ' . $signedIp . ' (IP: ' . $signedIp . ')</td>
                    </tr>
                    <tr>
                        <td><strong>Completato</strong></td>
                        <td>' . $signedAt->format('d / m / Y H:i:s') . ' UTC</td>
                        <td>Il documento è completato</td>
                    </tr>
                </tbody>
            </table>';

        $mpdf->WriteHTML($auditTrailHTML);

        // --- End of Audit Trail Page ---

        $filename = 'signed_contract_' . $id . '.pdf';
        $outputPath = storage_path('app/public/pdf/' . $filename);

        // Save the signed PDF
        $mpdf->Output($outputPath, 'F');
        
         // --- Save PDF content into database as binary ---
        $pdfBinaryContent = file_get_contents($outputPath); // Read the PDF file content
        $pdfSignature->pdf_content = $pdfBinaryContent; // Store the PDF binary data into the database column

        // Update the database with the signed PDF details
        $pdfSignature->status = 'signed';
        $pdfSignature->selected_pdf_name = $filename;
        $pdfSignature->save();
        
         //-----------------

          // Call addNoteAndTagSigned method
        try {
            $this->addNoteAndTagSigned(
                $pdfSignature->id,
                $pdfSignature->recipient_email,
                $pdfSignature->contract_name,
                $pdfSignature->product_name
            );
        } catch (\Exception $e) {
            \Log::error("Error in addNoteAndTagSigned for SalesListDraft ID {$pdfSignature->id}: " . $e->getMessage());
        }

        // Call addCloseIoNoteForSigned method
        try {
            $this->addCloseIoNoteForSigned(
                $pdfSignature->id,
                $pdfSignature->recipient_email,
                $pdfSignature->contract_name,
                $pdfSignature->product_name
            );
        } catch (\Exception $e) {
            \Log::error("Error in addCloseIoNoteForSigned for SalesListDraft ID {$pdfSignature->id}: " . $e->getMessage());
        }

        // for webhook  purposes
 
         // Call sendToWebhook for signed WebHook URL

        try {
            $webhookResponse = $this->sendToWebhook($id, $pdfSignature, 'signed');
            \Log::info('Signed WebHook response: ' . $webhookResponse);
        } catch (\Exception $e) {
            \Log::error('Error sending data to Signed WebHook: ' . $e->getMessage());
        }

        //-----------------------
        

        // Attach the signed PDF and send the confirmation email
        Mail::to($pdfSignature->recipient_email)->send(new SignatureConfirmationMail($pdfSignature, $outputPath));

        $signedDocumentUrl = route('sign.document.view', ['id' => $pdfSignature->id]);

        return response()->json([
            'success' => true,
           // 'redirectUrl' => route('view.signed.document', ['id' => $pdfSignature->id]),
           'redirectUrl' =>   $signedDocumentUrl,
        ]);

    } catch (\Exception $e) {
        \Log::error('Error signing document: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'An error occurred while processing the document. Please try again.'], 500);
    }
}

//for webhook url for signed status  
private function sendToWebhook($id, $salesDraftRecord, $type = 'pending')
{
    // Ensure variable_json is a string before decoding
    $variableJson = is_string($salesDraftRecord->variable_json) 
        ? json_decode($salesDraftRecord->variable_json, true) 
        : $salesDraftRecord->variable_json;

    // Ensure price_json is a string before decoding
    $priceJson = is_string($salesDraftRecord->price_json) 
        ? json_decode($salesDraftRecord->price_json, true) 
        : $salesDraftRecord->price_json;

    // Fetch the WebHook URL
    $companyId = $salesDraftRecord->company_id;
    $appConnection = AppConnection::where('company_id', $companyId)
        ->where('type', 'WebHook')
        ->first();

    if (!$appConnection || empty($appConnection->api_key)) {
        throw new \Exception('WebHook URL not found for this company.');
    }

    // Fetch the appropriate WebHook URL based on the type (pending or signed)
    $webhookUrl = json_decode($appConnection->api_key, true)[$type] ?? null;

    if (!$webhookUrl) {
        throw new \Exception(ucfirst($type) . ' WebHook URL is missing.');
    }

 
    $totalPrice = $priceJson['dynamicminRange'] ?? null;

    // Remove dynamicminRange, payments, and fixedvalue from price_json
    unset($priceJson['dynamicminRange']);
    unset($priceJson['payments']);
    unset($priceJson['fixedvalue']);

    // Prepare data for WebHook
    $webhookData = [
        'id' => $id,
        'status' => $type,
        'variable_json' => $variableJson,
        'total_price' => $totalPrice, // Add total_price here
        'price_json' => $priceJson, // Modified price_json without dynamicminRange, payments, and fixedvalue
       
    ];

    // Send data to WebHook
    $client = new \GuzzleHttp\Client();
    try {
        $response = $client->post($webhookUrl, ['json' => $webhookData]);

        return $response->getBody()->getContents();
    } catch (\Exception $e) {
        throw new \Exception('Failed to send data to ' . ucfirst($type) . ' WebHook: ' . $e->getMessage());
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

 

//---------------------------------------------------
   public function checkSignatureStatus()
    {
        // Fetch records where 'status' is 'signed' and 'price_id' is not 3 (i.e., not fully processed)
        $salesListDrafts = SalesListDraft::where('status', 'signed')
            ->where('price_id', '!=', 3)
            ->get();

        foreach ($salesListDrafts as $salesListDraft) {
            DB::transaction(function () use ($salesListDraft) {
                // Lock the row for update
                $salesListDraft->lockForUpdate();

                // Refresh the model
                $salesListDraft->refresh();

                // Double-check within the transaction
                if ($salesListDraft->status == 'signed' && $salesListDraft->price_id != 3) {
                    $closeIoSuccess = false;
                    $activeCampaignSuccess = false;

                    // Determine which notes have already been added
                    $existingPriceId = $salesListDraft->price_id;

                    // Attempt to add note to Close.io if not already added
                    if ($existingPriceId != 1 && $existingPriceId != 3) {
                        try {
                            $this->addCloseIoNoteForSigned(
                                $salesListDraft->id,
                                $salesListDraft->recipient_email,
                                $salesListDraft->contract_name,
                                $salesListDraft->product_name
                            );
                            $closeIoSuccess = true;
                        } catch (\Exception $e) {
                            \Log::error("Error in addCloseIoNoteForSigned for SalesListDraft ID {$salesListDraft->id}: " . $e->getMessage());
                        }
                    } elseif ($existingPriceId == 1 || $existingPriceId == 3) {
                        // Note to Close.io already added
                        $closeIoSuccess = true;
                    }

                    // Attempt to add note to ActiveCampaign if not already added
                    if ($existingPriceId != 2 && $existingPriceId != 3) {
                        try {
                            $this->addNoteAndTagSigned(
                                $salesListDraft->id,
                                $salesListDraft->recipient_email,
                                $salesListDraft->contract_name,
                                $salesListDraft->product_name
                            );
                            $activeCampaignSuccess = true;
                        } catch (\Exception $e) {
                            \Log::error("Error in addNoteAndTagSigned for SalesListDraft ID {$salesListDraft->id}: " . $e->getMessage());
                        }
                    } elseif ($existingPriceId == 2 || $existingPriceId == 3) {
                        // Note to ActiveCampaign already added
                        $activeCampaignSuccess = true;
                    }

                    // Update the price_id to mark which notes have been sent
                    if ($closeIoSuccess && $activeCampaignSuccess) {
                        $salesListDraft->price_id = 3;
                    } elseif ($closeIoSuccess) {
                        $salesListDraft->price_id = 1;
                    } elseif ($activeCampaignSuccess) {
                        $salesListDraft->price_id = 2;
                    } else {
                        // Neither succeeded, decide how to handle (e.g., set to 4)
                        $salesListDraft->price_id = 4;
                    }

                    // Save the changes
                    $salesListDraft->save();
                }
            });
        }

        return response()->json(['message' => 'Status check completed']);
    }

    // to add signed note in Active Campaigne
    
    public function addNoteAndTagSigned($id, $recipientEmail, $contractName, $selectedProduct)
    {
        try {
            // Retrieve company ID and AppConnection
            $companyId = SalesListDraft::where('id', $id)->value('company_id');
            
            $appConnection = AppConnection::where('type', 'ActiveCampaign')
                                        ->where('company_id', $companyId)
                                        ->first();

            if (!$appConnection) {
                \Log::info("No ActiveCampaign connection found for company ID {$companyId}");
                return;
            }

            // Parse API data
            $apiData = json_decode($appConnection->api_key, true);
            $this->activeCampaignApiKey = $apiData['api_key'] ?? null;

            if (empty($this->activeCampaignApiKey)) {
                \Log::error("ActiveCampaign API key is missing.");
                throw new \Exception("ActiveCampaign API key is missing.");
            }

            // Parse selected tags for signed status
            $selectedTags = json_decode($apiData['selectedSignedTags'] ?? '[]', true);

            // Get note template for signed status and replace placeholders
            $noteTemplate = $apiData['signed'] ?? "Il contratto \$contract_name\$ per il prodotto \$product_name\$ è stato firmato.";
            $noteText = str_replace(['$contract_name$', '$product_name$'], [$contractName, $selectedProduct], $noteTemplate);

            // Search for the contact
            $response = $this->activeCampaignClient->request('GET', 'contacts', [
                'headers' => [
                    'Api-Token' => $this->activeCampaignApiKey,
                ],
                'query' => [
                    'email' => $recipientEmail,
                ],
            ]);

            $contacts = json_decode($response->getBody(), true);
            \Log::info('ActiveCampaign Contact Search Response:', $contacts);

            if (isset($contacts['contacts']) && count($contacts['contacts']) > 0) {
                $contactId = $contacts['contacts'][0]['id'];

                // Add the note to the contact
                $noteResponse = $this->activeCampaignClient->request('POST', 'notes', [
                    'headers' => [
                        'Api-Token' => $this->activeCampaignApiKey,
                    ],
                    'json' => [
                        'note' => [
                            'note' => $noteText,
                            'reltype' => 'Subscriber',
                            'relid' => $contactId,
                        ],
                    ],
                ]);

                \Log::info('ActiveCampaign Note Addition Response:', json_decode($noteResponse->getBody(), true));

                // Add each tag to the contact
                foreach ($selectedTags as $tagName) {
                    try {
                        // Search for the tag by name to get its ID
                        $tagResponse = $this->activeCampaignClient->request('GET', 'tags', [
                            'headers' => [
                                'Api-Token' => $this->activeCampaignApiKey,
                            ],
                            'query' => [
                                'search' => $tagName,
                            ],
                        ]);

                        $tags = json_decode($tagResponse->getBody(), true);

                        if (isset($tags['tags']) && count($tags['tags']) > 0) {
                            // The tag exists, get its ID
                            $tagId = $tags['tags'][0]['id'];
                            \Log::info("Tag '{$tagName}' found with ID {$tagId}.");
                        } else {
                            // The tag does not exist, create it
                            \Log::info("Tag '{$tagName}' not found. Creating new tag.");

                            $createTagResponse = $this->activeCampaignClient->request('POST', 'tags', [
                                'headers' => [
                                    'Api-Token' => $this->activeCampaignApiKey,
                                ],
                                'json' => [
                                    'tag' => [
                                        'tag' => $tagName,
                                        'tagType' => 'contact',
                                    ],
                                ],
                            ]);

                            $createdTag = json_decode($createTagResponse->getBody(), true);
                            $tagId = $createdTag['tag']['id'];

                            \Log::info("Tag '{$tagName}' created with ID {$tagId}.");
                        }

                        // Add the tag to the contact
                        $tagData = [
                            'contactTag' => [
                                'contact' => $contactId,
                                'tag' => $tagId,
                            ],
                        ];

                        $contactTagResponse = $this->activeCampaignClient->request('POST', 'contactTags', [
                            'headers' => [
                                'Api-Token' => $this->activeCampaignApiKey,
                            ],
                            'json' => $tagData,
                        ]);

                        \Log::info("Tag '{$tagName}' added to contact ID {$contactId}.");
                    } catch (\GuzzleHttp\Exception\RequestException $e) {
                        \Log::error("Error adding tag '{$tagName}' to contact: " . $e->getMessage());
                        if ($e->hasResponse()) {
                            $responseBody = $e->getResponse()->getBody()->getContents();
                            \Log::error("Response: " . $responseBody);
                        }
                        continue; // Proceed to the next tag
                    }
                }

                \Log::info("Successfully added signed note and tags to ActiveCampaign for contact {$contactId}");
            } else {
                \Log::info("No contact found for email {$recipientEmail}");
                throw new \Exception("No contact found for email {$recipientEmail}");
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            \Log::error("RequestException while communicating with ActiveCampaign API: " . $e->getMessage());
            if ($e->hasResponse()) {
                $responseBody = $e->getResponse()->getBody()->getContents();
                \Log::error("Response: " . $responseBody);
            }
            throw $e;
        } catch (\Exception $e) {
            \Log::error("General exception while communicating with ActiveCampaign API: " . $e->getMessage());
            throw $e;
        }
    }





    // to add signed not in close crm 
 
 
 
    private function addCloseIoNoteForSigned($id, $recipientEmail, $contractName, $product_name)
    {
        try {
            // Retrieve the company_id from the SalesListDraft table
            $companyId = SalesListDraft::where('id', $id)->value('company_id');
    
            // Check if AppConnection exists for the given company_id with type 'Close'
            $appConnection = AppConnection::where('type', 'Close')
                ->where('company_id', $companyId)
                ->first();
    
            // Retrieve the signed note template and API key from the AppConnection table
            $noteTemplate = '';
            $closeApiKey = null;
            if ($appConnection && isset($appConnection->api_key)) {
                $apiData = json_decode($appConnection->api_key, true);
                $closeApiKey = $apiData['api_key'] ?? null;
                $noteTemplate = $apiData['signed'] ?? null;
            }
    
            // Default note text if no template is found in the database
            if (empty($noteTemplate)) {
                $noteTemplate = "al cliente è arrivato il contratto: \$contract_name$, del prodotto: \$product_name$ il contratto è stato firmato con successo.";
            }
    
            // Exit if no Close API key is found
            if (!$closeApiKey) {
                \Log::info("No Close API key found for company_id {$companyId}");
                return;
            }
    
            // Replace placeholders with actual values
            $noteText = str_replace(['$contract_name$', '$product_name$'], [$contractName, $product_name], $noteTemplate);
    
            // Step 2: Search for the lead in Close.io using the recipient's email
            $response = $this->closeClient->request('GET', 'https://api.close.com/api/v1/lead/', [
                'auth' => [$closeApiKey, ''], // Use Basic Auth with the retrieved API key
                'query' => [
                    'query' => 'email:"' . $recipientEmail . '"', // Search specifically for the exact email address
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
                    'auth' => [$closeApiKey, ''], // Use the retrieved API key here as well
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


  


