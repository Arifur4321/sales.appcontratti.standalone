<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LocalizationController extends Controller
{
    public function changeLanguage(Request $request)
    {
        // Get the language from the request
        $lang = $request->input('lang');
        
        // Check if the language is valid
        if (in_array($lang, ['en', 'it', 'ru', 'sp', 'gr'])) {
            // Store the language in session
            session(['lang' => $lang]);
            // Set the application locale
            App::setLocale($lang);
        }

        // Return a success response
        return response()->json(['status' => 'success']);
    }
}
