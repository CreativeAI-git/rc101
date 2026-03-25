<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ClassroomStudent;
use App\Models\GoogleAssignment;
use App\Models\GoogleCourse;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoogleController extends Controller
{
    public function loginToGoogle($role)
    {
        // Extract actual role (student or teacher)
        $actualRole = str_starts_with($role, 'teacher') ? 'teacher' : $role;

        if (!in_array($actualRole, ['student', 'teacher'])) {
            return response()->json(['success' => false, 'message' => 'Invalid role'], 400);
        }

        session_start();
        $_SESSION["google_login_role"] = $role;

        return Socialite::driver('google')
            ->stateless()
            ->scopes([
                'https://www.googleapis.com/auth/classroom.courses',
                'https://www.googleapis.com/auth/classroom.rosters',
                'https://www.googleapis.com/auth/classroom.coursework.students',
                'https://www.googleapis.com/auth/classroom.profile.emails',
                'https://www.googleapis.com/auth/userinfo.profile',
                'https://www.googleapis.com/auth/userinfo.email',
                'openid',
            ])
            ->with([
                'access_type' => 'offline',
                'prompt' => 'consent select_account',
            ])
            ->redirect();
    }

    // public function handleGoogleCallback($google_login_role)
    // {
    //     // Determine actual role
    //     $actualRole = str_starts_with($google_login_role, 'teacher') ? 'teacher' : $google_login_role;

    //     // Determine redirected_from
    //     if ($actualRole == 'teacher') {
    //         $redirectedFrom = Str::after($google_login_role, 'teacher');
    //         $redirectedFrom = $redirectedFrom == '' ? 'dashboard' : $redirectedFrom;
    //     } else {
    //         $redirectedFrom = 'dashboard';
    //     }

    //     try {
    //         $googleUser = Socialite::driver('google')->stateless()->user();

    //         $user = User::where('google_id', $googleUser->id)->first();

    //         if ($user) {
    //             $user->update([
    //                 'google_token' => $googleUser->token,
    //                 'google_refresh_token' => $googleUser->refreshToken,
    //                 'redirected_from' => $redirectedFrom,
    //             ]);
    //         } else {
    //             $user = User::create([
    //                 'name' => $googleUser->name,
    //                 'email' => $googleUser->email,
    //                 'google_id' => $googleUser->id,
    //                 'google_token' => $googleUser->token,
    //                 'google_refresh_token' => $googleUser->refreshToken,
    //                 'google_profile_image' => $googleUser->avatar,
    //                 'role_id' => $actualRole === 'teacher' ? 3 : 2,
    //                 'google_classroom_role' => $actualRole,
    //                 'status' => 1,
    //                 'redirected_from' => $redirectedFrom,
    //                 'password' => bcrypt(Str::random(16)),
    //             ]);
    //         }

    //         $subscription = UserSubscription::where('user_id', $user->id)
    //             ->where('status', 1)
    //             ->with('subscription')
    //             ->orderByDesc('subscription_id')
    //             ->first();

    //         Auth::login($user);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Login successful',
    //             'user' => $user,
    //             'subscription' => $subscription // Return subscription details if available
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Google login failed',
    //             'details' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function handleGoogleCallback($google_login_role)
    {
        $actualRole = str_starts_with($google_login_role, 'teacher') ? 'teacher' : $google_login_role;

        if ($actualRole == 'teacher') {
            $redirectedFrom = Str::after($google_login_role, 'teacher');
            $redirectedFrom = $redirectedFrom == '' ? 'dashboard' : $redirectedFrom;
        } else {
            $redirectedFrom = 'dashboard';
        }

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::where('google_id', $googleUser->id)->first();

            if ($user) {
                $user->update([
                    'google_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                    'redirected_from' => $redirectedFrom,
                ]);
            } else {
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'google_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                    'google_profile_image' => $googleUser->avatar,
                    'role_id' => $actualRole === 'teacher' ? 3 : 2,
                    'google_classroom_role' => $actualRole,
                    'status' => 1,
                    'redirected_from' => $redirectedFrom,
                    'password' => bcrypt(Str::random(16)),
                ]);
            }

            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

            $expiredSubscriptions = UserSubscription::where('status', 1)
                ->whereNotNull('end_date')
                ->where('end_date', '<', now())
                ->get();

            foreach ($expiredSubscriptions as $expired) {

                // If Stripe subscription exists, cancel it
                if (!empty($expired->stripe_subscription_id)) {
                    try {
                        $stripeSubscription = \Stripe\Subscription::retrieve(
                            $expired->stripe_subscription_id
                        );

                        if ($stripeSubscription && $stripeSubscription->status !== 'canceled') {
                            $stripeSubscription->cancel();
                        }
                    } catch (\Exception $e) {
                        // Stripe subscription may already be canceled/deleted
                        // Do not stop DB expiration
                    }
                }

                // Expire subscription in DB
                $expired->update([
                    'status' => 2, // expired
                    'stripe_subscription_id' => null
                ]);
            }

            // Ensure active free plan status is correct by end date
            UserSubscription::where('user_id', $user->id)
                ->where('type', 'free')
                ->whereDate('end_date', '>=', now()->toDateString())
                ->update(['status' => 1]);
        

            Auth::login($user);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Google login failed',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function removeAccount(Request $request)
    {
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization token not found'
            ], 401);
        }

        $user = User::where('google_token', $accessToken)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        DB::beginTransaction();

        try {
            $userId = $user->id;

            GoogleCourse::where('owner_id', $userId)->delete();
            ClassroomStudent::where('teacher_id', $userId)->delete();
            GoogleAssignment::where('owner_id', $userId)->delete();
            UserSubscription::where('user_id', $userId)->delete();

            $user->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Teacher account removed successfully'
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove account'
            ], 500);
        }
    }
}
