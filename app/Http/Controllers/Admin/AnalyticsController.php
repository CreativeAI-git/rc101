<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GoogleAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(GoogleAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * GA4 Dashboard page (UI shell). Data loads via AJAX for better UX.
     */
    public function index(Request $request)
    {
        $defaultPreset = '30';

        return view('admin.analytics.index', [
            'defaultPreset' => $defaultPreset,
            'connectUrl' => route('admin.google.analytics.connect'),
        ]);
    }

    /**
     * GA4 Dashboard JSON data endpoint.
     *
     * Query params:
     * - preset: 7|30|90|custom
     * - start_date: Y-m-d (required for custom)
     * - end_date: Y-m-d (required for custom)
     */
    public function data(Request $request)
    {
        [$startDate, $endDate, $preset] = $this->resolveDateRange($request);

        try {
            $payload = $this->analyticsService->getDashboardData($startDate, $endDate);

            return response()->json([
                'ok' => true,
                'range' => [
                    'preset' => $preset,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'data' => $payload,
            ]);
        } catch (\Throwable $e) {
            Log::warning('GA4 dashboard fetch failed', [
                'error' => $e->getMessage(),
                'preset' => $preset,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
                'connect_url' => route('admin.google.analytics.connect'),
            ], 500);
        }
    }

    /**
     * Resolve date range (server-side) to keep GA4 requests consistent.
     */
    private function resolveDateRange(Request $request): array
    {
        $preset = (string) $request->query('preset', '30');

        // Use the GA4 property reporting timezone to match GA UI cards.
        // Configure via GOOGLE_ANALYTICS_TIMEZONE (e.g. Asia/Kolkata).
        $tz = (string) (config('services.google_analytics.timezone') ?: 'UTC');

        // GA4 UI "Last X days" typically ends at yesterday (full days),
        // while "today" is partial and often causes mismatches vs GA UI cards.
        $endDay = Carbon::now($tz)->subDay();

        if ($preset === '7') {
            return [$endDay->copy()->subDays(6)->toDateString(), $endDay->toDateString(), '7'];
        }

        if ($preset === '30') {
            return [$endDay->copy()->subDays(29)->toDateString(), $endDay->toDateString(), '30'];
        }

        if ($preset === '90') {
            return [$endDay->copy()->subDays(89)->toDateString(), $endDay->toDateString(), '90'];
        }

        if ($preset === 'custom') {
            $start = (string) $request->query('start_date', '');
            $end = (string) $request->query('end_date', '');

            $startDate = Carbon::parse($start, $tz)->toDateString();
            $endDate = Carbon::parse($end, $tz)->toDateString();

            if ($startDate > $endDate) {
                [$startDate, $endDate] = [$endDate, $startDate];
            }

            return [$startDate, $endDate, 'custom'];
        }

        // Fallback
        return [$endDay->copy()->subDays(29)->toDateString(), $endDay->toDateString(), '30'];
    }
}
