<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    // API to get subscription plans without login
    public function getAllSubscription(Request $request)
    {
        $subscriptions = Subscription::all();

        if ($subscriptions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No subscriptions available.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'subscriptions' => $subscriptions
        ]);
    }

    public function getSubscriptions(Request $request)
    {
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return response()->json(['success' => false, 'message' => 'Google token not found'], 400);
        }

        $teacher = User::where('google_token', $accessToken)->first();

        if (!$teacher || $teacher->google_classroom_role !== "teacher") {
            return response()->json(['success' => false, 'message' => 'Access denied. Please log in with a teacher account.'], 400);
        }

        // Expire any past-due subscriptions for this teacher before checking active
        UserSubscription::where('user_id', $teacher->id)
            ->where('status', 1)
            ->whereNotNull('end_date')
            ->where('end_date', '<', date('Y-m-d'))
            ->update([
                'status' => 2,
                'stripe_subscription_id' => null,
            ]);

        // Fetch all available subscriptions
        $subscriptions = Subscription::all();

        // Fetch the user's active subscription (if any)
        // $userSubscription = UserSubscription::where('user_id', $teacher->id)
        //     ->where('status', 1) // Only fetch active subscriptions
        //     ->with('subscription') // Eager load subscription details
        //     ->first();
        $userSubscription = UserSubscription::where('user_id', $teacher->id)
            ->where('status', 1)
            ->with('subscription')
            ->orderByDesc('subscription_id')
            ->first();

        return response()->json([
            'success' => true,
            'subscriptions' => $subscriptions,
            'user_subscription' => $userSubscription
        ]);
    }

    // API to initiate checkout session
    // public function checkout(Request $request)
    // {
    //     $accessToken = $request->bearerToken();

    //     if (!$accessToken) {
    //         return response()->json(['success' => false, 'message' => 'Google token not found'], 400);
    //     }

    //     $teacher = User::where('google_token', $accessToken)->first();

    //     if (!$teacher || $teacher->google_classroom_role !== "teacher") {
    //         return response()->json(['success' => false, 'message' => 'Access denied. Please log in with a teacher account.'], 400);
    //     }

    //     $request->validate([
    //         'subscription_id' => 'required|exists:subscriptions,id',
    //         'type' => 'required|in:monthly,yearly,free',
    //     ]);

    //     // Prevent multiple active subscriptions
    //     $existingSubscription = UserSubscription::where('user_id', $teacher->id)
    //         ->where('status', 1)
    //         ->first();

    //     if ($existingSubscription) {
    //         return response()->json(['success' => false, 'message' => 'User already has an active subscription'], 400);
    //     }

    //     $subscription = Subscription::findOrFail($request->subscription_id);

    //     // Handle Free Plan (No Stripe checkout)
    //     if ($request->type === 'free') {
    //         // Check if user already used free trial before
    //         $usedFreeTrial = UserSubscription::where('user_id', $teacher->id)
    //             ->where('type', 'free')
    //             ->exists();

    //         if ($usedFreeTrial) {
    //             return response()->json(['success' => false, 'message' => 'Free trial already used. Please choose a paid plan.'], 400);
    //         }

    //         // Create 3-day free trial
    //         UserSubscription::create([
    //             'user_id' => $teacher->id,
    //             'subscription_id' => $subscription->id,
    //             'type' => 'free',
    //             'start_date' => now(),
    //             'end_date' => now()->addDays(3),
    //             'stripe_subscription_id' => null,
    //             'status' => 1,
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Free 3-day trial activated successfully',
    //             'type' => 'free',
    //             'data' => [
    //                 'start_date' => now()->toDateTimeString(),
    //                 'end_date' => now()->addDays(3)->toDateTimeString(),
    //             ],
    //         ]);
    //     }

    //     // For Paid Plans (Monthly/Yearly)
    //     $price = $request->type === 'monthly' ? $subscription->monthly_price : $subscription->yearly_price;

    //     if (!$price || $price <= 0) {
    //         return response()->json(['success' => false, 'message' => 'Invalid price'], 400);
    //     }

    //     try {
    //         Stripe::setApiKey(env('STRIPE_SECRET'));

    //         $session = Session::create([
    //             'payment_method_types' => ['card'],
    //             'customer_email' => $teacher->email,

    //             'line_items' => [[
    //                 'price_data' => [
    //                     'currency' => 'usd',
    //                     'product_data' => [
    //                         'name' => $subscription->name . ' (' . ucfirst($request->type) . ')',
    //                     ],
    //                     'unit_amount' => $price * 100, // one-time amount
    //                 ],
    //                 'quantity' => 1,
    //             ]],

    //             'mode' => 'payment', // ONE-TIME PAYMENT

    //             'success_url' => env('FRONT_URL') . '/teacher/payment-success?session_id={CHECKOUT_SESSION_ID}',
    //             'cancel_url' => env('FRONT_URL') . '/teacher/payment-failed',

    //             'metadata' => [
    //                 'subscription_id' => $subscription->id,
    //                 'type' => $request->type,
    //             ],
    //         ]);

    //         return response()->json(['success' => true, 'session_url' => $session->url]);
    //     } catch (\Stripe\Exception\ApiErrorException $e) {
    //         return response()->json(['success' => false, 'message' => 'Stripe error: ' . $e->getMessage()], 500);
    //     } catch (\Exception $e) {
    //         return response()->json(['success' => false, 'message' => 'Something went wrong: ' . $e->getMessage()], 500);
    //     }
    // }

    public function checkout(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate reCAPTCHA FIRST
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'recaptcha_token' => 'required',
            'subscription_id' => 'required|exists:subscriptions,id',
            'type' => 'required|in:monthly,yearly,free',
        ]);

        $recaptchaResponse = Http::asForm()->post(
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'secret'   => env('GOOGLE_RECAPTCHA_SECRET_KEY'),
                'response' => $request->recaptcha_token,
                'remoteip' => $request->ip(),
            ]
        );

        $recaptchaData = $recaptchaResponse->json();

        if (
            !$recaptchaData['success'] ||
            $recaptchaData['score'] < 0.7 ||
            $recaptchaData['action'] !== 'checkout'
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Suspicious activity detected'
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Your Existing Logic (unchanged)
        |--------------------------------------------------------------------------
        */

        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return response()->json(['success' => false, 'message' => 'Google token not found'], 400);
        }

        $teacher = User::where('google_token', $accessToken)->first();

        if (!$teacher || $teacher->google_classroom_role !== "teacher") {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 400);
        }

        $existingSubscription = UserSubscription::where('user_id', $teacher->id)
            ->where('status', 1)
            ->first();

        if ($existingSubscription) {
            return response()->json(['success' => false, 'message' => 'User already has an active subscription'], 400);
        }

        $subscription = Subscription::findOrFail($request->subscription_id);

        /*
        |--------------------------------------------------------------------------
        | FREE PLAN
        |--------------------------------------------------------------------------
        */

        if ($request->type === 'free') {

            $usedFreeTrial = UserSubscription::where('user_id', $teacher->id)
                ->where('type', 'free')
                ->exists();

            if ($usedFreeTrial) {
                return response()->json(['success' => false, 'message' => 'Free trial already used.'], 400);
            }

            UserSubscription::create([
                'user_id' => $teacher->id,
                'subscription_id' => $subscription->id,
                'type' => 'free',
                'start_date' => now(),
                'end_date' => now()->addDays(3),
                'stripe_subscription_id' => null,
                'status' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Free trial activated successfully'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | PAID PLAN
        |--------------------------------------------------------------------------
        */

        $price = $request->type === 'monthly'
            ? $subscription->monthly_price
            : $subscription->yearly_price;

        if (!$price || $price <= 0) {
            return response()->json(['success' => false, 'message' => 'Invalid price'], 400);
        }

        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));

            $session = Session::create([
                'payment_method_types' => ['card'],
                'customer_email' => $teacher->email,
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $subscription->name . ' (' . ucfirst($request->type) . ')',
                        ],
                        'unit_amount' => $price * 100,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => env('FRONT_URL') . '/teacher/payment-success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => env('FRONT_URL') . '/teacher/payment-failed',
                'metadata' => [
                    'subscription_id' => $subscription->id,
                    'type' => $request->type,
                ],
            ]);

            return response()->json([
                'success' => true,
                'session_url' => $session->url
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment error'
            ], 500);
        }
    }

    // API to handle payment success
    public function success(Request $request)
    {
        $request->validate([
            'session_id' => 'required'
        ]);

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $session = \Stripe\Checkout\Session::retrieve($request->session_id);

            if (!$session || $session->payment_status !== 'paid') {
                return response()->json(['success' => false, 'message' => 'Payment not completed'], 400);
            }

            $user = User::where('email', $session->customer_email)->first();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            $subscription = Subscription::find($session->metadata['subscription_id']);
            if (!$subscription) {
                return response()->json(['success' => false, 'message' => 'Subscription not found'], 404);
            }

            // Prevent duplicate active subscription
            $existing = UserSubscription::where('user_id', $user->id)
                ->where('status', 1)
                ->first();

            if ($existing) {
                return response()->json(['success' => false, 'message' => 'User already has an active subscription'], 400);
            }

            // Calculate end date correctly
            $startDate = now();
            $endDate = $session->metadata['type'] === 'monthly'
                ? $startDate->copy()->addMonth()
                : $startDate->copy()->addYear();

            UserSubscription::create([
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'type' => $session->metadata['type'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'stripe_subscription_id' => null, // one-time payment
                'status' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription activated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // API to cancel subscription
    // public function cancelSubscription(Request $request)
    // {
    //     $accessToken = $request->bearerToken();

    //     if (!$accessToken) {
    //         return response()->json(['success' => false, 'message' => 'Google token not found'], 400);
    //     }

    //     $teacher = User::where('google_token', $accessToken)->first();

    //     if (!$teacher || $teacher->google_classroom_role !== "teacher") {
    //         return response()->json(['success' => false, 'message' => 'Access denied. Please log in with a teacher account.'], 400);
    //     }

    //     $request->validate([
    //         'subscription_id' => 'required|exists:user_subscriptions,id'
    //     ]);

    //     // IMPORTANT FIX: use ID instead of subscription_id
    //     $userSubscription = UserSubscription::where('id', $request->subscription_id)
    //         ->where('user_id', $teacher->id)
    //         ->where('status', 1)
    //         ->first();

    //     if (!$userSubscription) {
    //         return response()->json(['success' => false, 'message' => 'Subscription not found or does not belong to this user'], 404);
    //     }

    //     try {

    //         // FREE PLAN
    //         if ($userSubscription->type === 'free') {
    //             $userSubscription->update(['status' => 0]);

    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Free Subscription canceled successfully'
    //             ]);
    //         }

    //         // PAID PLAN — OLD USERS (Stripe subscription exists)
    //         if (!empty($userSubscription->stripe_subscription_id)) {

    //             Stripe::setApiKey(env('STRIPE_SECRET'));

    //             try {
    //                 $subscription = \Stripe\Subscription::retrieve(
    //                     $userSubscription->stripe_subscription_id
    //                 );

    //                 if ($subscription && $subscription->status !== 'canceled') {
    //                     $subscription->cancel();
    //                 }
    //             } catch (\Stripe\Exception\InvalidRequestException $e) {
    //                 // Stripe subscription might already be deleted — ignore safely
    //             }
    //         }

    //         // PAID PLAN — NEW USERS (One-time payment, no Stripe)
    //         // Just update DB
    //         $userSubscription->update(['status' => 0]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Subscription canceled successfully'
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Something went wrong',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function cancelSubscription(Request $request)
    {
        // Validate input
        $request->validate([
            'subscription_id' => 'required|exists:user_subscriptions,id',
            'recaptcha_token' => 'required|string'
        ]);

        /*
        |--------------------------------------------------------------------------
        | Verify Google reCAPTCHA
        |--------------------------------------------------------------------------
        */
        $recaptchaResponse = Http::asForm()->post(
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'secret'   => env('GOOGLE_RECAPTCHA_SECRET_KEY'),
                'response' => $request->recaptcha_token,
                'remoteip' => $request->ip(),
            ]
        );

        $recaptchaBody = $recaptchaResponse->json();

        if (!$recaptchaBody['success'] || ($recaptchaBody['score'] ?? 1) < 0.5) {
            return response()->json([
                'success' => false,
                'message' => 'reCAPTCHA verification failed'
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Authenticate Teacher
        |--------------------------------------------------------------------------
        */
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return response()->json([
                'success' => false,
                'message' => 'Google token not found'
            ], 400);
        }

        $teacher = User::where('google_token', $accessToken)
            ->where('google_classroom_role', 'teacher')
            ->first();

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Please log in with a teacher account.'
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | Find Subscription
        |--------------------------------------------------------------------------
        */
        $userSubscription = UserSubscription::where('id', $request->subscription_id)
            ->where('user_id', $teacher->id)
            ->where('status', 1)
            ->first();

        if (!$userSubscription) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription not found or does not belong to this user'
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Cancel Subscription
        |--------------------------------------------------------------------------
        */
        try {

            // FREE PLAN
            if ($userSubscription->type === 'free') {
                $userSubscription->update(['status' => 0]);

                return response()->json([
                    'success' => true,
                    'message' => 'Free Subscription canceled successfully'
                ]);
            }

            // PAID PLAN (Stripe subscription exists)
            if (!empty($userSubscription->stripe_subscription_id)) {

                Stripe::setApiKey(env('STRIPE_SECRET'));

                try {
                    $subscription = \Stripe\Subscription::retrieve(
                        $userSubscription->stripe_subscription_id
                    );

                    if ($subscription && $subscription->status !== 'canceled') {
                        $subscription->cancel();
                    }
                } catch (\Exception $e) {
                    // Ignore if already canceled on Stripe
                }
            }

            // Update DB
            $userSubscription->update(['status' => 0]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription canceled successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }
}
