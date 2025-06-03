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
        
        Log::info('Image request', [
            'path' => $fullPath,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('Referer')
        ]);
        
        // Validate the path to prevent directory traversal
        if (str_contains($fullPath, '..') || str_contains($fullPath, '\\')) {
            Log::warning('Invalid path attempted: ' . $fullPath);
            abort(400, 'Invalid path');
        }
        
        // Clean the path - remove any prefixes that might cause issues
        $cleanPath = $this->cleanImagePath($fullPath);
        
        Log::info('Cleaned path: ' . $cleanPath);
        
        // Check if file exists in public storage
        if (!Storage::disk('public')->exists($cleanPath)) {
            Log::error('Image not found', [
                'original_path' => $fullPath,
                'clean_path' => $cleanPath,
                'storage_path' => storage_path('app/public/' . $cleanPath)
            ]);
            
            // List directory contents for debugging
            $this->logDirectoryContents($cleanPath);
            
            // Return a 1x1 transparent PNG as fallback instead of 404
            return $this->getTransparentImageResponse();
        }
        
        try {
            // Get file contents and metadata
            $file = Storage::disk('public')->get($cleanPath);
            $mimeType = Storage::disk('public')->mimeType($cleanPath);
            $fileSize = Storage::disk('public')->size($cleanPath);
            $lastModified = Storage::disk('public')->lastModified($cleanPath);
            
            Log::info('Serving image', [
                'path' => $cleanPath,
                'mime_type' => $mimeType,
                'size' => $fileSize,
                'last_modified' => date('Y-m-d H:i:s', $lastModified)
            ]);
            
            // Create response with proper headers
            $response = Response::make($file, 200);
            
            // Set content headers
            $response->header('Content-Type', $mimeType);
            $response->header('Content-Length', $fileSize);
            
            // Enhanced CORS headers
            $response->header('Access-Control-Allow-Origin', '*');
            $response->header('Access-Control-Allow-Methods', 'GET, HEAD, OPTIONS');
            $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With');
            $response->header('Access-Control-Allow-Credentials', 'true');
            $response->header('Access-Control-Expose-Headers', 'Content-Length, Content-Type');
            
            // Caching headers
            $response->header('Cache-Control', 'public, max-age=86400'); // 24 hours
            $response->header('Expires', gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');
            
            // ETag for cache validation
            $etag = md5($cleanPath . $fileSize . $lastModified);
            $response->header('ETag', '"' . $etag . '"');
            
            // Check if client has cached version
            if ($request->header('If-None-Match') === '"' . $etag . '"') {
                return response('', 304, [
                    'Cache-Control' => 'public, max-age=86400',
                    'ETag' => '"' . $etag . '"',
                    'Access-Control-Allow-Origin' => '*',
                ]);
            }
            
            return $response;
            
        } catch (\Exception $e) {
            Log::error('Error serving image', [
                'path' => $cleanPath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->getTransparentImageResponse();
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
            'Access-Control-Max-Age' => '86400', // 24 hours
        ]);
    }
    
    /**
     * Clean the image path by removing problematic prefixes.
     *
     * @param string $path
     * @return string
     */
    private function cleanImagePath(string $path): string
    {
        // Remove leading slashes
        $path = ltrim($path, '/');
        
        // Remove /storage/ prefix if present
        $path = str_replace('/storage/', '', $path);
        $path = str_replace('storage/', '', $path);
        
        // Remove any remaining leading slashes
        $path = ltrim($path, '/');
        
        return $path;
    }
    
    /**
     * Log directory contents for debugging.
     *
     * @param string $path
     * @return void
     */
    private function logDirectoryContents(string $path): void
    {
        try {
            $parentDir = dirname($path);
            
            if ($parentDir && $parentDir !== '.' && Storage::disk('public')->exists($parentDir)) {
                $files = Storage::disk('public')->files($parentDir);
                $directories = Storage::disk('public')->directories($parentDir);
                
                Log::info('Directory contents', [
                    'parent_directory' => $parentDir,
                    'files' => $files,
                    'directories' => $directories
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Could not list directory contents: ' . $e->getMessage());
        }
    }
    
    /**
     * Get a transparent 1x1 PNG image response as fallback.
     *
     * @return \Illuminate\Http\Response
     */
    private function getTransparentImageResponse(): \Illuminate\Http\Response
    {
        // 1x1 transparent PNG
        $transparentPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        
        return Response::make($transparentPng, 200, [
            'Content-Type' => 'image/png',
            'Content-Length' => strlen($transparentPng),
            'Access-Control-Allow-Origin' => '*',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}