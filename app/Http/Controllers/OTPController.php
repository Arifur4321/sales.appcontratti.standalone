<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Session;
use App\Models\SalesListDraft; // Import your model
use Illuminate\Support\Facades\Auth;
use App\Models\AppConnection;
use App\Models\Company;

class OTPController extends Controller
{
    protected $twilio;

    public function __construct()
    {
        $this->twilio = new Client(
            config('services.twilio.account_sid'), 
            config('services.twilio.auth_token')
        );
    }

      public function checkSMSEnabled()
    {
       // $user = Auth::user();
      //  $company_id = $user->company_id;

        // Retrieve the AppConnection for SMS type
    //    $appConnection = AppConnection::where('company_id', $company_id)
     //                                 ->where('type', 'SMS')
     //                                 ->first();

        // Default smsEnabled to false
    //    $smsEnabled = false;

        // Check if sms_enabled is set to true in the api_key JSON
      //  if ($appConnection && isset(json_decode($appConnection->api_key)->sms_enabled)) {
      //      $smsEnabled = json_decode($appConnection->api_key)->sms_enabled;
      //  }

      //  return response()->json(['sms_enabled' => $smsEnabled]);

        return response()->json(['sms_enabled' => true]);
    }

    public function getMobileNumber($id)
    {
        // Find the contract in the SalesListDraft table using the ID
        $salesListDraft = SalesListDraft::find($id);

        if (!$salesListDraft) {
            return response()->json(['mobile_number' => null], 404);
        }

        // Split the number assuming first two digits are the country code for Italy (39)
        $fullMobileNumber = $salesListDraft->variable_id;

        if (strlen($fullMobileNumber) > 2 && substr($fullMobileNumber, 0, 2) == '39') {
            $countryCode = '39';
            $mobileNumber = substr($fullMobileNumber, 2); // Remove the country code
        } else {
            $countryCode = ''; // Handle other cases
            $mobileNumber = $fullMobileNumber; // Default to full number
        }

        // Return the country code and mobile number separately
        return response()->json([
            'mobile_number' => $mobileNumber,
            'country_code' => $countryCode
        ], 200);
    }

    

    public function sendOTP(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'mobile' => 'required|string',
            'id'     => 'required|integer', // Validate that the 'id' is an integer
        ]);

        // Extract the mobile number and the ID from the request
        $mobile = $request->input('mobile');
        $id = $request->input('id'); // Get the ID from the request body

        try {
            // Clear previous OTP send timestamps when a new signing starts
            Session::forget('otp_sends');

            // Retrieve the SalesListDraft record by ID
            $salesListDraft = SalesListDraft::findOrFail($id);

            // Get the company_id from SalesListDraft
            $companyId = $salesListDraft->company_id;

            // Retrieve the company name
            $company = Company::findOrFail($companyId);
            $companyName = $company->company_name;

            // Prepare custom variables
            $customVariables = urlencode(json_encode([
                'company_name' => $companyName,
                'friendly_name' => $companyName, // For {{friendly_name}} placeholder
            ]));

               // Another way 
             $verification = $this->twilio->verify->v2->services(config('services.twilio.verify_service_sid'))
             ->verifications
             ->create($mobile, "sms", [
                 'customFriendlyName' => $companyName, // Use company name as friendly name
             ]);

            // No custom-friendly name
          /*  
          $verification = $this->twilio->verify->v2->services(config('services.twilio.verify_service_sid'))
            ->verifications
            ->create($mobile, "sms");
            */

            Session::put('mobile_number', $mobile);

            // Get the existing OTPviewed data (if any) and decode it
            $otpViewedData = json_decode($salesListDraft->OTPviewed, true);

            // If no existing data, initialize it as an empty array
            if (!$otpViewedData) {
                $otpViewedData = [];
            }

            // Add the current OTP send timestamp to the OTPviewed array
            $otpViewedData[] = [
                'otp_sent_at' => now()->timezone('Europe/Rome')->format('Y-m-d H:i:s'),
                'mobile' => $mobile,
            ];

            // Save the updated OTPviewed data back to the database as JSON
            $salesListDraft->OTPviewed = json_encode($otpViewedData);
            $salesListDraft->save();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Twilio OTP Send Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to send OTP. Please try again.']);
        }
    }


    // public function sendOTP(Request $request)
    // {
    //     // Validate the incoming request
    //     $request->validate([
    //         'mobile' => 'required|string',
    //         'id'     => 'required|integer', // Validate that the 'id' is an integer
    //     ]);

    //     // Extract the mobile number and the ID from the request
    //     $mobile = $request->input('mobile');
    //     $id = $request->input('id'); // Get the ID from the request body

    //     try {
    //         // Clear previous OTP send timestamps when a new signing starts
    //         Session::forget('otp_sends');

    //         // Send OTP using Twilio
    //         $verification = $this->twilio->verify->v2->services(config('services.twilio.verify_service_sid'))
    //             ->verifications
    //             ->create($mobile, "sms");

    //         Session::put('mobile_number', $mobile);

    //         // Retrieve the sales_list_draft record by ID
    //         $pdfSignature = SalesListDraft::findOrFail($id);

    //         // Get the existing OTPviewed data (if any) and decode it
    //         $otpViewedData = json_decode($pdfSignature->OTPviewed, true);

    //         // If no existing data, initialize it as an empty array
    //         if (!$otpViewedData) {
    //             $otpViewedData = [];
    //         }

    //         // Add the current OTP send timestamp to the OTPviewed array
    //         $otpViewedData[] = [
    //             'otp_sent_at' => now()->timezone('Europe/Rome')->format('Y-m-d H:i:s'),
    //             'mobile' => $mobile,
    //         ];

    //         // Save the updated OTPviewed data back to the database as JSON
    //         $pdfSignature->OTPviewed = json_encode($otpViewedData);
    //         $pdfSignature->save();

    //         return response()->json(['success' => true]);
    //     } catch (\Exception $e) {
    //         // Log the error for debugging
    //         \Log::error('Twilio OTP Send Error: ' . $e->getMessage());
    //         return response()->json(['success' => false, 'message' => 'Failed to send OTP. Please try again.']);
    //     }
    // }



    // For verify OTP 
    public function verifyOTP(Request $request)
    {
        $request->validate([
            'otp' => 'required|string',
        ]);

        $otp = $request->input('otp');

        $mobile = Session::get('mobile_number');

        if (!$mobile) {
            return response()->json(['success' => false, 'message' => 'Mobile number not found. Please request OTP again.']);
        }

        try {
            // Update this part
            $verification_check = $this->twilio->verify->v2->services(config('services.twilio.verify_service_sid'))
                ->verificationChecks
                ->create([
                    'code' => $otp,
                    'to' => $mobile
                ]);

            if ($verification_check->status === "approved") {
                // OTP is verified successfully
                Session::forget('mobile_number');
                return response()->json(['success' => true]);
            } else {
                return response()->json(['success' => false, 'message' => 'Invalid OTP. Please try again.']);
            }
        } catch (\Exception $e) {
            \Log::error('Twilio OTP Verify Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to verify OTP. Please try again.']);
        }
    }

}

 
