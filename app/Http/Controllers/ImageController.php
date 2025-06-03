<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;

class ImageController extends Controller
{
    public function show(Request $request, $path = null)
    {
        // Get the full path from the route parameter
        $fullPath = $request->route('path');
        
        Log::info('Image request for path: ' . $fullPath);
        
        // Check if file exists in public storage
        if (!Storage::disk('public')->exists($fullPath)) {
            Log::error('Image not found: ' . $fullPath);
            abort(404, 'Image not found');
        }
        
        try {
            // Get file contents and mime type
            $file = Storage::disk('public')->get($fullPath);
            $mimeType = Storage::disk('public')->mimeType($fullPath);
            
            Log::info('Serving image: ' . $fullPath . ' (MIME: ' . $mimeType . ')');
            
            // Create response with proper headers
            $response = Response::make($file, 200);
            
            // Set content type
            $response->header('Content-Type', $mimeType);
            
            // Add CORS headers
            $response->header('Access-Control-Allow-Origin', '*');
            $response->header('Access-Control-Allow-Methods', 'GET, HEAD, OPTIONS');
            $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With');
            $response->header('Access-Control-Allow-Credentials', 'true');
            
            // Add caching headers
            $response->header('Cache-Control', 'public, max-age=3600');
            
            return $response;
            
        } catch (\Exception $e) {
            Log::error('Error serving image: ' . $e->getMessage());
            abort(500, 'Error serving image');
        }
    }
    
    public function options(Request $request)
    {
        // Handle OPTIONS request for CORS preflight
        return Response::make('', 200, [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
            'Access-Control-Allow-Headers' => 'Origin, Content-Type, Accept, Authorization, X-Requested-With',
            'Access-Control-Allow-Credentials' => 'true',
        ]);
    }
}