<?php

namespace App\Services;

use Google\Auth\Credentials\UserRefreshCredentials;
use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\RunRealtimeReportRequest;
use Google\Client as GoogleClient;
use Google\Service\GoogleAnalyticsAdmin;

class GoogleAnalyticsService
{
    protected $client;
    protected $propertyId;
    protected $oauthCredentials = null;

    /**
     * Simple per-request memoization to avoid repeated GA4 API calls
     * when multiple controller/view components need the same data.
     */
    private array $memo = [];

    public function __construct()
    {
        $this->propertyId = config('services.google_analytics.property_id') ?: env('GOOGLE_ANALYTICS_PROPERTY_ID');
    }

    /**
     * Initialize OAuth Analytics Client
     */
    private function initializeOAuthClient()
    {
        $googleClientId = env('GOOGLE_CLIENT_ID');
        $googleClientSecret = env('GOOGLE_CLIENT_SECRET');

        $user = auth()->user();

        if (!$user) {
            throw new \Exception('User not authenticated');
        }

        if (empty($this->propertyId)) {
            throw new \Exception('GOOGLE_ANALYTICS_PROPERTY_ID is not configured');
        }

        $refreshToken = $user->google_refresh_token ?? null;

        if (empty($refreshToken)) {
            throw new \Exception('Google refresh token not found');
        }

        $credentials = new UserRefreshCredentials(
            ['https://www.googleapis.com/auth/analytics.readonly'],
            [
                'type' => 'authorized_user',
                'client_id' => $googleClientId,
                'client_secret' => $googleClientSecret,
                'refresh_token' => $refreshToken,
            ]
        );

        $this->oauthCredentials = $credentials;

        $this->client = new BetaAnalyticsDataClient([
            'credentials' => $credentials,
        ]);
    }

    /**
     * Build Google Client
     */
    private function buildGoogleClient(): ?GoogleClient
    {
        if (empty($this->oauthCredentials)) {
            return null;
        }

        $user = auth()->user();

        $client = new GoogleClient();

        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));

        $client->refreshToken($user->google_refresh_token);

        $client->addScope('https://www.googleapis.com/auth/analytics.readonly');

        return $client;
    }

    /**
     * List Accessible GA4 Properties
     */
    public function listAccessibleProperties(): array
    {
        $this->initializeOAuthClient();

        $googleClient = $this->buildGoogleClient();

        if (!$googleClient) {
            return [];
        }

        $adminService = new GoogleAnalyticsAdmin($googleClient);

        $summaries = $adminService->accountSummaries->listAccountSummaries();

        $properties = [];

        foreach ($summaries->getAccountSummaries() as $accountSummary) {

            foreach ($accountSummary->getPropertySummaries() as $propertySummary) {

                $properties[] = [
                    'account' => $accountSummary->getDisplayName(),
                    'property_display_name' => $propertySummary->getDisplayName(),
                    'property' => $propertySummary->getProperty(),
                    'property_id' => str_replace('properties/', '', $propertySummary->getProperty()),
                    // 'property_id' => $propertySummary->getPropertyId(),
                ];
            }
        }

        return $properties;
    }

    /**
     * Get Visitors Report
     */
    public function getVisitors()
    {
        $this->initializeOAuthClient();

        try {

            $response = $this->client->runReport(
                new RunReportRequest([
                    'property' => 'properties/' . $this->propertyId,

                    'date_ranges' => [
                        new DateRange([
                            'start_date' => '30daysAgo',
                            'end_date'   => 'today',
                        ]),
                    ],

                    'metrics' => [
                        new Metric([
                            'name' => 'activeUsers',
                        ]),
                    ],
                ])
            );

            return $response;

        } catch (\Exception $e) {

            throw $e;
        }
    }

    /**
     * Helper: run a GA4 report with consistent error handling.
     *
     * @param  string  $startDate  Format: Y-m-d or GA4 relative (e.g. 30daysAgo)
     * @param  string  $endDate    Format: Y-m-d or GA4 relative (e.g. today)
     */
    private function runReport(string $startDate, string $endDate, array $metrics, array $dimensions = [], array $options = [])
    {
        $this->initializeOAuthClient();

        $request = new RunReportRequest(array_merge([
            'property' => 'properties/' . $this->propertyId,
            'date_ranges' => [
                new DateRange([
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]),
            ],
            'metrics' => array_map(fn ($metricName) => new Metric(['name' => $metricName]), $metrics),
            'dimensions' => array_map(fn ($dimensionName) => new Dimension(['name' => $dimensionName]), $dimensions),
        ], $options));

        return $this->client->runReport($request);
    }

    /**
     * Helper: Extract a single metric total from a report response.
     */
    private function extractSingleTotalMetric($response, int $metricIndex = 0): float
    {
        $totals = $response?->getTotals();
        $firstTotal = (!empty($totals)) ? ($totals[0] ?? null) : null;
        $metricValues = $firstTotal?->getMetricValues() ?? [];
        if (empty($metricValues)) {
            $rows = $response?->getRows() ?? [];
            $firstRow = (!empty($rows)) ? ($rows[0] ?? null) : null;
            $metricValues = $firstRow?->getMetricValues() ?? [];
        }

        if (empty($metricValues)) {
            return 0;
        }

        $value = ($metricValues[$metricIndex] ?? null)?->getValue();

        return is_numeric($value) ? (float) $value : 0;
    }

    /**
     * Helper: memoize within the same request.
     */
    private function remember(string $key, \Closure $callback)
    {
        if (array_key_exists($key, $this->memo)) {
            return $this->memo[$key];
        }

        $this->memo[$key] = $callback();

        return $this->memo[$key];
    }

    /**
     * Fetch summary metrics in a single GA4 request (optimized).
     */
    private function getSummary(string $startDate, string $endDate): array
    {
        $cacheKey = "summary:{$startDate}:{$endDate}";

        return $this->remember($cacheKey, function () use ($startDate, $endDate) {
            $response = $this->runReport(
                $startDate,
                $endDate,
                // Match GA UI cards more closely: New Users + Active Users + Sessions + Views...
                // Keep totalUsers too (last index) for optional use elsewhere.
                ['newUsers', 'activeUsers', 'sessions', 'screenPageViews', 'averageSessionDuration', 'bounceRate', 'totalUsers']
            );

            $totals = $response?->getTotals();
            $firstTotal = (!empty($totals)) ? ($totals[0] ?? null) : null;
            $metricValues = $firstTotal?->getMetricValues() ?? [];
            if (empty($metricValues)) {
                // If metric aggregations (totals) aren't returned, GA4 typically provides a single row when no dimensions are set.
                $rows = $response?->getRows() ?? [];
                $firstRow = (!empty($rows)) ? ($rows[0] ?? null) : null;
                $metricValues = $firstRow?->getMetricValues() ?? [];
            }

            $get = function (int $index) use ($metricValues): float {
                $value = ($metricValues[$index] ?? null)?->getValue();
                return is_numeric($value) ? (float) $value : 0;
            };

            return [
                'new_users' => (int) round($get(0)),
                'active_users' => (int) round($get(1)),
                'sessions' => (int) round($get(2)),
                'page_views' => (int) round($get(3)),
                'avg_session_duration' => $get(4), // seconds
                'bounce_rate' => $get(5), // percentage (0-100)
                'total_users' => (int) round($get(6)),
            ];
        });
    }

    /**
     * Total Active Users for the selected date range.
     */
    public function getActiveUsers(string $startDate, string $endDate): int
    {
        return (int) ($this->getSummary($startDate, $endDate)['active_users'] ?? 0);
    }

    /**
     * Total Sessions for the selected date range.
     */
    public function getSessions(string $startDate, string $endDate): int
    {
        return (int) ($this->getSummary($startDate, $endDate)['sessions'] ?? 0);
    }

    /**
     * Total Page Views for the selected date range.
     */
    public function getPageViews(string $startDate, string $endDate): int
    {
        return (int) ($this->getSummary($startDate, $endDate)['page_views'] ?? 0);
    }

    /**
     * Top visited pages (path + views).
     */
    public function getTopPages(string $startDate, string $endDate, int $limit = 10): array
    {
        $cacheKey = "top_pages:{$startDate}:{$endDate}:{$limit}";

        return $this->remember($cacheKey, function () use ($startDate, $endDate, $limit) {
            $response = $this->runReport(
                $startDate,
                $endDate,
                ['screenPageViews', 'activeUsers'],
                ['pagePathPlusQueryString', 'pageTitle'],
                [
                    'limit' => $limit,
                    'order_bys' => [
                        new OrderBy([
                            'metric' => new OrderBy\MetricOrderBy(['metric_name' => 'screenPageViews']),
                            'desc' => true,
                        ]),
                    ],
                ]
            );

            $rows = $response?->getRows() ?? [];
            $data = [];

            foreach ($rows as $row) {
                $dimensions = $row->getDimensionValues();
                $metrics = $row->getMetricValues();

                $data[] = [
                    'path' => (string) ((($dimensions[0] ?? null)?->getValue()) ?? ''),
                    'title' => (string) ((($dimensions[1] ?? null)?->getValue()) ?? ''),
                    'views' => (int) round((float) (((($metrics[0] ?? null)?->getValue()) ?? 0))),
                    'active_users' => (int) round((float) (((($metrics[1] ?? null)?->getValue()) ?? 0))),
                ];
            }

            return $data;
        });
    }

    /**
     * Traffic sources (session source) by sessions.
     */
    public function getTrafficSources(string $startDate, string $endDate, int $limit = 8): array
    {
        $cacheKey = "traffic_sources:{$startDate}:{$endDate}:{$limit}";

        return $this->remember($cacheKey, function () use ($startDate, $endDate, $limit) {
            $response = $this->runReport(
                $startDate,
                $endDate,
                ['sessions'],
                ['sessionSource'],
                [
                    'limit' => $limit,
                    'order_bys' => [
                        new OrderBy([
                            'metric' => new OrderBy\MetricOrderBy(['metric_name' => 'sessions']),
                            'desc' => true,
                        ]),
                    ],
                ]
            );

            $rows = $response?->getRows() ?? [];
            $data = [];

            foreach ($rows as $row) {
                $dimensionValues = $row->getDimensionValues();
                $metricValues = $row->getMetricValues();
                $source = (string) ((($dimensionValues[0] ?? null)?->getValue()) ?? 'unknown');
                $sessions = (int) round((float) (((($metricValues[0] ?? null)?->getValue()) ?? 0)));

                if ($sessions <= 0) {
                    continue;
                }

                $data[] = [
                    'source' => $source,
                    'sessions' => $sessions,
                ];
            }

            return $data;
        });
    }

    /**
     * Countries by active users.
     */
    public function getCountries(string $startDate, string $endDate, int $limit = 10): array
    {
        $cacheKey = "countries:{$startDate}:{$endDate}:{$limit}";

        return $this->remember($cacheKey, function () use ($startDate, $endDate, $limit) {
            $response = $this->runReport(
                $startDate,
                $endDate,
                ['activeUsers'],
                ['country'],
                [
                    'limit' => $limit,
                    'order_bys' => [
                        new OrderBy([
                            'metric' => new OrderBy\MetricOrderBy(['metric_name' => 'activeUsers']),
                            'desc' => true,
                        ]),
                    ],
                ]
            );

            $rows = $response?->getRows() ?? [];
            $data = [];

            foreach ($rows as $row) {
                $dimensionValues = $row->getDimensionValues();
                $metricValues = $row->getMetricValues();
                $country = (string) ((($dimensionValues[0] ?? null)?->getValue()) ?? 'unknown');
                $activeUsers = (int) round((float) (((($metricValues[0] ?? null)?->getValue()) ?? 0)));

                if ($activeUsers <= 0) {
                    continue;
                }

                $data[] = [
                    'country' => $country,
                    'active_users' => $activeUsers,
                ];
            }

            return $data;
        });
    }

    /**
     * Devices by active users.
     */
    public function getDevices(string $startDate, string $endDate, int $limit = 10): array
    {
        $cacheKey = "devices:{$startDate}:{$endDate}:{$limit}";

        return $this->remember($cacheKey, function () use ($startDate, $endDate, $limit) {
            $response = $this->runReport(
                $startDate,
                $endDate,
                ['activeUsers'],
                ['deviceCategory'],
                [
                    'limit' => $limit,
                    'order_bys' => [
                        new OrderBy([
                            'metric' => new OrderBy\MetricOrderBy(['metric_name' => 'activeUsers']),
                            'desc' => true,
                        ]),
                    ],
                ]
            );

            $rows = $response?->getRows() ?? [];
            $data = [];

            foreach ($rows as $row) {
                $dimensionValues = $row->getDimensionValues();
                $metricValues = $row->getMetricValues();
                $device = (string) ((($dimensionValues[0] ?? null)?->getValue()) ?? 'unknown');
                $activeUsers = (int) round((float) (((($metricValues[0] ?? null)?->getValue()) ?? 0)));

                if ($activeUsers <= 0) {
                    continue;
                }

                $data[] = [
                    'device' => $device,
                    'active_users' => $activeUsers,
                ];
            }

            return $data;
        });
    }

    /**
     * Browsers by active users.
     */
    public function getBrowsers(string $startDate, string $endDate, int $limit = 10): array
    {
        $cacheKey = "browsers:{$startDate}:{$endDate}:{$limit}";

        return $this->remember($cacheKey, function () use ($startDate, $endDate, $limit) {
            $response = $this->runReport(
                $startDate,
                $endDate,
                ['activeUsers'],
                ['browser'],
                [
                    'limit' => $limit,
                    'order_bys' => [
                        new OrderBy([
                            'metric' => new OrderBy\MetricOrderBy(['metric_name' => 'activeUsers']),
                            'desc' => true,
                        ]),
                    ],
                ]
            );

            $rows = $response?->getRows() ?? [];
            $data = [];

            foreach ($rows as $row) {
                $dimensionValues = $row->getDimensionValues();
                $metricValues = $row->getMetricValues();
                $browser = (string) ((($dimensionValues[0] ?? null)?->getValue()) ?? 'unknown');
                $activeUsers = (int) round((float) (((($metricValues[0] ?? null)?->getValue()) ?? 0)));

                if ($activeUsers <= 0) {
                    continue;
                }

                $data[] = [
                    'browser' => $browser,
                    'active_users' => $activeUsers,
                ];
            }

            return $data;
        });
    }

    /**
     * Realtime users (runRealtimeReport).
     */
    public function getRealtimeUsers(): int
    {
        $cacheKey = "realtime_users";

        return (int) $this->remember($cacheKey, function () {
            $this->initializeOAuthClient();

            $request = new RunRealtimeReportRequest([
                'property' => 'properties/' . $this->propertyId,
                'metrics' => [
                    new Metric(['name' => 'activeUsers']),
                ],
            ]);

            $response = $this->client->runRealtimeReport($request);

            // realtime report totals are typically provided in rows; handle both.
            $rows = $response?->getRows() ?? [];
            $firstRow = (!empty($rows)) ? ($rows[0] ?? null) : null;
            if ($firstRow && !empty($firstRow?->getMetricValues())) {
                $metricValues = $firstRow->getMetricValues() ?? [];
                $value = ($metricValues[0] ?? null)?->getValue();
                return is_numeric($value) ? (int) round((float) $value) : 0;
            }

            return 0;
        });
    }

    /**
     * Daily visitors (active users) for line chart.
     */
    public function getDailyVisitors(string $startDate, string $endDate): array
    {
        $cacheKey = "daily_visitors:{$startDate}:{$endDate}";

        return $this->remember($cacheKey, function () use ($startDate, $endDate) {
            $response = $this->runReport(
                $startDate,
                $endDate,
                ['activeUsers'],
                ['date'],
                [
                    'order_bys' => [
                        new OrderBy([
                            'dimension' => new OrderBy\DimensionOrderBy(['dimension_name' => 'date']),
                            'desc' => false,
                        ]),
                    ],
                ]
            );

            $rows = $response?->getRows() ?? [];
            $labels = [];
            $values = [];

            foreach ($rows as $row) {
                $dimensionValues = $row->getDimensionValues();
                $metricValues = $row->getMetricValues();
                $date = (string) ((($dimensionValues[0] ?? null)?->getValue()) ?? '');
                $value = (float) (((($metricValues[0] ?? null)?->getValue()) ?? 0));

                // GA4 date dimension is YYYYMMDD
                if (preg_match('/^\\d{8}$/', $date)) {
                    $date = substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);
                }

                $labels[] = $date;
                $values[] = (int) round($value);
            }

            return [
                'labels' => $labels,
                'values' => $values,
            ];
        });
    }

    /**
     * Dashboard payload - keeps controller clean and avoids repeated calls.
     */
    public function getDashboardData(string $startDate, string $endDate): array
    {
        $summary = $this->getSummary($startDate, $endDate);

        return [
            'summary' => $summary,
            'daily_visitors' => $this->getDailyVisitors($startDate, $endDate),
            'top_pages' => $this->getTopPages($startDate, $endDate),
            'traffic_sources' => $this->getTrafficSources($startDate, $endDate),
            'countries' => $this->getCountries($startDate, $endDate),
            'devices' => $this->getDevices($startDate, $endDate),
            'browsers' => $this->getBrowsers($startDate, $endDate),
            'realtime_users' => $this->getRealtimeUsers(),
        ];
    }
}
