<?php

namespace App\Http\Controllers;

use App\Models\Footer;
use Illuminate\Http\Request;

class FooterController extends Controller
{
    public function index()
    {
        return response()->json(Footer::first());
    }

    public function update(Request $request, Footer $footer)
    {
        $validated = $request->validate([
            'description' => 'required|string',
            'ar_description' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
        ]);

        $footer->update($validated);

        return response()->json(['message' => 'Footer updated successfully', 'footer' => $footer]);
    }
}
