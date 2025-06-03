<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class MediaController extends Controller
{
    public function serve($filename)
    {
        // Optional: check user permissions here
        $path = storage_path("app/private/lessons/{$filename}");

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path); // or ->download($path)
    }
}
