<?php

namespace App\Services;

use Google\Auth\Credentials\UserRefreshCredentials;
use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Metric;
use Google\Client as GoogleClient;
use Google\Service\GoogleAnalyticsAdmin;

class GoogleAnalyticsService
{
    protected $client;
    protected $propertyId;
    protected $oauthCredentials = null;

    public function __construct()
    {
        $this->propertyId = env('GOOGLE_ANALYTICS_PROPERTY_ID');
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
}