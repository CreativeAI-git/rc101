<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GoogleAnalyticsService;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(GoogleAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function index()
    {
        $accessibleProperties = [];

        try {
            $analytics = $this->analyticsService->getVisitors();
            $error = null;
        } catch (\Throwable $e) {
            $analytics = null;
            $error = $e->getMessage();

            try {
                $accessibleProperties = $this->analyticsService->listAccessibleProperties();
            } catch (\Throwable $ignored) {
                // ignore listing errors; main error is shown
            }
        }

        $user = Auth::user();

        $debug = [
            'property_id' => env('GOOGLE_ANALYTICS_PROPERTY_ID'),
            'required_google_email' => env('GOOGLE_ANALYTICS_ADMIN_GOOGLE_EMAIL'),
            'admin_email' => $user?->email,
            'has_google_refresh_token' => !empty($user?->google_refresh_token),
            'google_refresh_token_len' => $user?->google_refresh_token ? strlen((string) $user->google_refresh_token) : 0,
        ];

        return view('admin.analytics.index', compact('analytics', 'error', 'debug', 'accessibleProperties'));
    }
}
