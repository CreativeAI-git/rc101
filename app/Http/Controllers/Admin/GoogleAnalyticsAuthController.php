<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAnalyticsAuthController extends Controller
{
    private function analyticsRedirectUrl(): string
    {
        return env('GOOGLE_ANALYTICS_REDIRECT_URI') ?: route('admin.google.analytics.callback');
    }

    private function requiredGoogleEmail(): string
    {
        return (string) (env('GOOGLE_ANALYTICS_ADMIN_GOOGLE_EMAIL') ?: '');
    }

    public function connect(Request $request)
    {
        if (!Auth::check() || (int) Auth::user()->role_id !== 1) {
            abort(403);
        }

        $requiredGoogleEmail = $this->requiredGoogleEmail();

        return Socialite::driver('google')
            ->stateless()
            ->redirectUrl($this->analyticsRedirectUrl())
            ->scopes([
                'https://www.googleapis.com/auth/analytics.readonly',
                'https://www.googleapis.com/auth/userinfo.email',
                'https://www.googleapis.com/auth/userinfo.profile',
                'openid',
            ])
            ->with([
                'access_type' => 'offline',
                'prompt' => 'consent select_account',
                'include_granted_scopes' => 'true',
                // Helps Google pre-select the correct account (still allows changing).
                'login_hint' => $requiredGoogleEmail ?: null,
            ])
            ->redirect();
    }

    public function callback(Request $request)
    {
        if (!Auth::check() || (int) Auth::user()->role_id !== 1) {
            abort(403);
        }

        $googleUser = Socialite::driver('google')
            ->stateless()
            ->redirectUrl($this->analyticsRedirectUrl())
            ->user();

        $user = Auth::user();

        $requiredGoogleEmail = $this->requiredGoogleEmail() ?: (string) $user->email;

        if (!empty($googleUser->email) && !empty($requiredGoogleEmail) && strcasecmp($googleUser->email, $requiredGoogleEmail) !== 0) {
            return redirect('/analytics')->with(
                'error_msg',
                'Please sign in with this Google account for Analytics: ' . $requiredGoogleEmail
            );
        }

        if (empty($googleUser->refreshToken)) {
            return redirect('/analytics')->with(
                'error_msg',
                'Google did not return a refresh token. Remove this app from the Google account\'s third‑party access, then try Connect again.'
            );
        }

        $user->google_id = $googleUser->id;
        $user->google_token = $googleUser->token;
        $user->google_refresh_token = $googleUser->refreshToken;

        $user->save();

        return redirect('/analytics')->with('success_msg', 'Google Analytics connected successfully.');
    }
}
