<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class HealthController extends Controller
{
    /**
     * Health check endpoint for Render.com
     */
    public function index(Request $request)
    {
        try {
            // Check database connection
            DB::connection()->getPdo();
            
            // Check cache (if configured)
            Cache::put('health_check', 'ok', 60);
            Cache::get('health_check');
            
            return response()->json([
                'status' => 'healthy',
                'timestamp' => now()->toISOString(),
                'version' => app()->version(),
                'environment' => app()->environment(),
                'database' => 'connected',
                'cache' => 'working'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'unhealthy',
                'timestamp' => now()->toISOString(),
                'error' => $e->getMessage()
            ], 503);
        }
    }
}
