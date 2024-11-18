<?php
    
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Template;
use Illuminate\Support\Facades\Auth;

class TemplateController extends Controller
{

    public function useTemplate (){


        
    }

    public function show()
    {
        // Get the currently logged-in user
        $user = Auth::user();

        // Retrieve the template for the logged-in user's company, if it exists
        $template = Template::where('company_id', $user->company_id)->first();

        // Default email content if no template exists
        $defaultEmailContent = "
            <p>Caro cliente,</p>
            <p>Per favore firma il documento cliccando sul link qui sotto:</p>
            <p><a href='#'>Firmare il documento</a></p>
            <p>Distinti saluti,<br>GF SRL.</p>
        ";

        // Default SMS content (you can customize this as needed)
        $defaultSmsContent = "Default SMS Content";

        // Pass the existing template content or default content to the view
        return view('Email-SMS-Template', [
            'emailContent' => $template ? $template->email_content : $defaultEmailContent,
            'smsContent' => $template ? $template->sms_content : $defaultSmsContent
        ]);
    }

    
    public function store(Request $request)
    {
        // Get the currently logged-in user
        $user = Auth::user();

        // Validate the input
        $request->validate([
            'email_template' => 'nullable|string',
            'sms_template' => 'nullable|string',
        ]);

        // Check if the email template contains at least one word inside % %
        if (!preg_match('/%\s*[^%]+\s*%/', $request->email_template)) {
            // Return error response if the condition is not met
            return response()->json([
                'error' => 'Create your signature link using %%. For Example: %Click Here%'
            ], 422);
        }

        // Check if the template for this company already exists
        $template = Template::where('company_id', $user->company_id)->first();

        if (!$template) {
            // If no template exists, create a new one
            $template = new Template();
            $template->compane_email = $user->email;
            $template->company_id = $user->company_id;
        }

        // Update the template content
        $template->email_content = $request->email_template;
        $template->sms_content = $request->sms_template;
        $template->save();

        return response()->json(['success' => 'Template saved successfully']);
    }



}