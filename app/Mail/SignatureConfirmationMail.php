<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\SalesListDraft;

class SignatureConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pdfSignature;
    protected $pdfPath;

    public function __construct(SalesListDraft $pdfSignature, $pdfPath)
    {
        $this->pdfSignature = $pdfSignature;
        $this->pdfPath = $pdfPath;
    }

    public function build()
    {
        return $this->view('emails.signature_confirmation')
                    ->subject('Signature Confirmation')
                    ->with(['pdfSignature' => $this->pdfSignature])
                    ->attach($this->pdfPath, [
                        'as' => $this->pdfSignature->selected_pdf_name,
                        'mime' => 'application/pdf',
                    ]);
    }
}
