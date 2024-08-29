<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesListDraft; // Import your model

class HelloSignWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $event = $request->input('event');

        if ($event['event_type'] === 'signature_request_viewed') {
            $signatureRequestId = $event['signature_request']['signature_request_id'];
            $signerEmail = $event['signature_request']['signatures'][0]['signer_email_address'];

            $this->updateStatus($signatureRequestId, $signerEmail, 'viewed');
        } elseif ($event['event_type'] === 'signature_request_signed') {
            $signatureRequestId = $event['signature_request']['signature_request_id'];
            $signerEmail = $event['signature_request']['signatures'][0]['signer_email_address'];

            $this->updateStatus($signatureRequestId, $signerEmail, 'signed');
        }

        return response()->json(['status' => 'success'], 200);
    }

    protected function updateStatus($signatureRequestId, $signerEmail, $status)
    {
        // Update your database to reflect the new status
        $document = SalesListDraft::where('envelope_id', $signatureRequestId)
                            ->where('recipient_email', $signerEmail)
                            ->first();

        if ($document) {
            $document->percentage_complete = $status;
            $document->save();
        }
    }
}
