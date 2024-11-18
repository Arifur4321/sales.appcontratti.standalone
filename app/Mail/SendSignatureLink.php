<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\SalesListDraft;
use App\Models\Template;
use App\Models\Company;

class SendSignatureLink extends Mailable
{
    use Queueable, SerializesModels;

    public $signingUrl;
    public $recipientName;
    public $pdfSignature;
    public $companyName;

    /**
     * Create a new message instance.
     *replace should work now
     * @param string $signingUrl
     * @param string $recipientName
     * @param object $pdfSignature
     */
    public function __construct($signingUrl, $recipientName, $pdfSignature)
    {
        $this->signingUrl = $signingUrl;
        $this->recipientName = $recipientName;
        $this->pdfSignature = $pdfSignature;

        $salesDraft = SalesListDraft::find($pdfSignature->id);

        if ($salesDraft) {
            if ($salesDraft->company_id == 1) {
                $this->companyName = 'GF SRL';
            } else {
                $company = Company::find($salesDraft->company_id);
                $this->companyName = $company ? $company->company_name : 'GF SRL';
            }
        } else {
            $this->companyName = 'GF SRL';
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Check if an email template exists in the database
        $template = Template::where('company_id', $this->pdfSignature->company_id)->first();

        if ($template && $template->email_content) {
            // Use the email template from the database
            $emailContent = $this->replacePlaceholders($template->email_content);
            return $this->view('emails.dynamic_template')
                        ->subject('Si prega di firmare il documento contrattuale del Codice 1%')
                        ->replyTo('no-reply@giacomofreddi.it')  // Set the no-reply email address
                        ->with([
                            'emailContent' => $emailContent,
                            'recipientName' => $this->recipientName,
                            'companyName' => $this->companyName,
                        ]);
        } else {
            // Use the default template (signature_link.blade.php)
            return $this->view('emails.signature_link')
                        ->subject('Si prega di firmare il documento contrattuale del Codice 1%')
                        ->replyTo('no-reply@giacomofreddi.it')  // Set the no-reply email address
                        ->with([
                            'signingUrl' => $this->signingUrl,
                            'recipientName' => $this->recipientName,
                            'pdfSignature' => $this->pdfSignature,
                            'companyName' => $this->companyName,
                        ]);
        }
    }

  
    private function replacePlaceholders($emailContent)
    {
        // Define the route link for signing
        $signingUrl = route('signature.view', ['id' => $this->pdfSignature->id]);
    
        // Use regex to find all instances of %word% in the template
        $emailContent = preg_replace_callback('/%(.*?)%/', function ($matches) use ($signingUrl) {
            // $matches[1] contains the text inside the %%, use it as the link text
            $linkText = $matches[1];  // This will capture "Firmare il documento" or "Click here"
            return '<a href="' . $signingUrl . '">' . e($linkText) . '</a>';
        }, $emailContent);
    
        return $emailContent;
    }
    
}



// namespace App\Mail;

// use Illuminate\Bus\Queueable;
// use Illuminate\Mail\Mailable;
// use Illuminate\Queue\SerializesModels;
// use App\Models\SalesListDraft;
// use App\Models\Company;

// class SendSignatureLink extends Mailable
// {
//     use Queueable, SerializesModels;

//     public $signingUrl;
//     public $recipientName;
//     public $pdfSignature;
//     public $companyName;

//     /**
//      * Create a new message instance.
//      *
//      * @param string $signingUrl
//      * @param string $recipientName
//      * @param object $pdfSignature
//      */
//     public function __construct($signingUrl, $recipientName, $pdfSignature)
//     {
//         $this->signingUrl = $signingUrl;
//         $this->recipientName = $recipientName;
//         $this->pdfSignature = $pdfSignature;

//         $salesDraft = SalesListDraft::find($pdfSignature->id);

//         if ($salesDraft) {
//             if ($salesDraft->company_id == 1) {
//                 $this->companyName = 'GF SRL';
//             } else {
//                 $company = Company::find($salesDraft->company_id);
//                 $this->companyName = $company ? $company->company_name : 'GF SRL';
//             }
//         } else {
//             $this->companyName = 'GF SRL';
//         }
//     }

   
//     public function build()
//     {
//         return $this->view('emails.signature_link')
//                     ->subject('Si prega di firmare il documento contrattuale del Codice 1%')
//                     ->replyTo('no-reply@giacomofreddi.it')  // Set the no-reply email address
//                     ->with([
//                         'signingUrl' => $this->signingUrl,
//                         'recipientName' => $this->recipientName,
//                         'pdfSignature' => $this->pdfSignature,
//                         'companyName' => $this->companyName,
//                     ]);
//     }
// }  
