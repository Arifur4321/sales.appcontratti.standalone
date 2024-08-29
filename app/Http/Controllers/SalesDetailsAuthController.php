<?php

namespace App\Http\Controllers;
use App\Models\SalesDetails; // Assuming SalesDetail is your model for sales_details table
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Hash;

class SalesDetailsAuthController extends Controller
{

    public function authenticate(Request $request)
    {
        $salesDetails = SalesDetails::where('email', $request->email)->first();

        if ($salesDetails && Hash::check($request->password, $salesDetails->password)) {
            Auth::login($salesDetails);

            if (Auth::check()) {
                return redirect()->intended('/Your-Lists'); // Redirect to the Sales-Lists URL Your-Lists
            } else {
                return "User not logged in";
            }
        }  else if ($salesDetails && $request->password == $salesDetails->password) {
            Auth::login($salesDetails);

            if (Auth::check()) {
                return redirect()->intended('/Your-Lists'); // Redirect to the Sales-Lists URL
            } else {
                return "User not logged in";
            }
        }
        
        else {
            return redirect()->route('login')->withErrors(['loginError' => 'Invalid email or password']);
        }
    }
    

}
