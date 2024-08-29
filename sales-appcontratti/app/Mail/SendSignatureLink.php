<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
 
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailable;


class SendSignatureLink extends Mailable
{
    use Queueable, SerializesModels;

    public $signingUrl;
    public $recipientName;

    public function __construct($signingUrl, $recipientName)
    {
        $this->signingUrl = $signingUrl;
        $this->recipientName = $recipientName;
    }

    public function build()
    {
        return $this->view('emails.signature_link')
                    ->subject('Please Sign the Codice 1% contract Document')
                    ->with([
                        'signingUrl' => $this->signingUrl,
                        'recipientName' => $this->recipientName,
                    ]);
    }
}
