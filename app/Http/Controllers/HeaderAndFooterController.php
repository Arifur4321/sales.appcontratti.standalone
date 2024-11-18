<?php

namespace App\Http\Controllers;
use App\Models\HeaderAndFooter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\HeaderAndFooterContractpage;
use Mpdf\Mpdf;

class HeaderAndFooterController extends Controller
{
///***** */



// *******************************************
public function generatePdf(Request $request)
{
    // Validate the request
    $request->validate([
        'editorData' => 'required|string',
        'contractID' => 'required|integer',
    ]);

    $editorData = $request->input('editorData');
    $contractID = $request->input('contractID');

    // Fetch the header and footer configuration from the database
    $config = HeaderAndFooterContractpage::where('contractID', $contractID)->first();

    $headerContent = '';
    $footerContent = '';

    if ($config) {
        // Use the HeaderID and FooterID to fetch content from HeaderAndFooter table
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

    // Create new mPDF document
    $mpdf = new \Mpdf\Mpdf([
        'format' => 'A4',
        'margin_top' => $headerContent ? 40 : 10,
        'margin_bottom' => $footerContent ? 40 : 10, // Increased bottom margin for footer space
        'margin_left' => 15,
        'margin_right' => 15,
    ]);

    // Set document properties
    $mpdf->SetTitle('PDF with Header and Footer');
    $mpdf->SetAuthor('Your Name');

    // Set up the watermark to appear on every page
    $mpdf->SetWatermarkText('Giacomo Freddi');
    $mpdf->showWatermarkText = true;
    $mpdf->watermark_font = 'DejaVuSansCondensed';
    $mpdf->watermarkTextAlpha = 0.1;

    // Set header to appear only on the first page
    if ($headerContent) {
        $mpdf->SetHTMLHeader($headerContent, 'O'); // 'O' for odd (first) page
    }

    // Set the footer for all pages with content, page number, and date/time
    $italianTimezone = new \DateTimeZone('Europe/Rome');
    $dateTime = new \DateTime('now', $italianTimezone);
    $formattedDateTime = $dateTime->format('d-m-Y H:i:s');

    $mpdf->SetHTMLFooter('
        <div style="width: 100%; font-size: 10px; display: flex; justify-content: space-between; align-items: center; position: relative;">
          
            <div style="flex: 1; text-align: center;">' . $footerContent . '</div>
            <div style="flex: 1; text-align: right;">Page {PAGENO}/{nbpg}</div>
        </div>
    ');

    // Process the editor data to correctly position images
    $processedEditorData = $this->processImageTags($editorData);

    // Add main content with enhanced CSS for better layout, especially for images
    $contentHTML = '
        <style>
            body { font-family: DejaVu Sans, sans-serif; }
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #000; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            img { max-width: 100%; height: auto; display: block; margin: auto; }
        </style>
        <div>' . $processedEditorData . '</div>';

    $mpdf->WriteHTML($contentHTML);

    // Save the PDF to a file
    $filename = 'contract_' . time() . '_' . uniqid() . '.pdf';
    $pdfFilePath = 'pdf/' . $filename;
    Storage::disk('public')->put($pdfFilePath, $mpdf->Output('', 'S'));

    // Return the URL to the generated PDF
    return response()->json(['pdf_url' => Storage::url($pdfFilePath)]);
}

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




    //***** */

    public function show()
    {
        $headerAndFooterEntries = HeaderAndFooter::all();
        return view('HeaderAndFooter', compact('headerAndFooterEntries'));
    }

 
  

    public function save(Request $request)
    {
        try {
            $entry = HeaderAndFooter::updateOrCreate(
                ['id' => $request->id], // If ID exists, update, otherwise create
                [
                    'name' => $request->name,
                    'type' => $request->type,
                    'editor_content' => $request->editor_content
                ]
            );
    
            return response()->json(['success' => true, 'message' => 'Header/Footer entry saved successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error saving header/footer entry: ' . $e->getMessage()], 500);
        }
    }
    

    public function deleteContract($id)
    {
        $HeaderAndFooter = HeaderAndFooter::findOrFail($id); // Find the variable by ID
        $HeaderAndFooter->delete(); // Delete the variable
        return redirect()->back()->with('success', 'HeaderAndFooter deleted successfully'); // Redirect back with success message
    }

    public function update(Request $request, $id)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['Header', 'Footer'])], // Validate against specific values
            'editor_content' => 'required|string', // Adjust validation rules as needed
        ]);
    
        // Find the header/footer entry
        $entry = HeaderAndFooter::findOrFail($id);
        
        // Update the entry with the new values
        $entry->update($validatedData);
    
        // Optionally, you can return a response if needed
        return response()->json(['success' => true, 'message' => 'Header/Footer entry updated successfully.']);
    }
    
  
    public function destroy(Entry $entry)
    {
        // Perform validation or additional checks if needed
        
        $entry->delete();

        return redirect()->back()->with('success', 'Entry deleted successfully');
    }
    
}
