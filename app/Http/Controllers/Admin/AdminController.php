<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Carbon;
use App\Models\Product;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserSubscription;

class AdminController extends Controller
{
    /**
     * Authenticate the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'email' => 'required|max:255|email',
            'password' => 'required|min:8'
        ]);

        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if ($user->role_id == 1) {
                return redirect()->intended('dashboard')->with('success_msg', 'You have Successfully logged in');
            } else {
                // Redirect users with other role IDs to a different page
                return redirect('https://www.creativethoughtsinfo.com/CT01/advanced_react');
            }
        }

        return redirect('/')->with('error_msg', 'Oppes! You have entered invalid credentials');
    }

    /**
     * Display a dashboard to authenticated users.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        $today = Carbon::today();
        $currentDate = Carbon::now();

        $data['totalSubscription'] = Subscription::count();
        $data['totalUser'] = User::whereNotNull('google_id')->where('role_id', '=', 2)->count();
        $data['totalSubscriber'] = UserSubscription::count();
        $total = UserSubscription::with('subscription')
            ->whereDate('created_at', $today)
            // ->where('status', 1)
            ->get()
            ->sum(function ($item) {
                if (!$item->subscription) return 0;

                return $item->type === 'monthly'
                    ? $item->subscription->monthly_price
                    : $item->subscription->yearly_price;
            });

        $data['totalTodaySales'] = str_replace('.00', '', number_format($total, 2, '.', ''));

        // This month's total sales
        $data['totalThisMonthSales'] = str_replace('.00', '', number_format(
            UserSubscription::with('subscription')
                ->whereYear('created_at', $currentDate->year)
                ->whereMonth('created_at', $currentDate->month)
                // ->where('status', 1)
                ->get()
                ->sum(function ($item) {
                    if (!$item->subscription) return 0;
                    return $item->type === 'monthly'
                        ? $item->subscription->monthly_price
                        : $item->subscription->yearly_price;
                }),
            2,
            '.',
            ''
        ));

        // Overall total sales
        $data['totalOverAllSales'] = str_replace('.00', '', number_format(
            UserSubscription::with('subscription')
                // ->where('status', 1)
                ->get()
                ->sum(function ($item) {
                    if (!$item->subscription) return 0;
                    return $item->type === 'monthly'
                        ? $item->subscription->monthly_price
                        : $item->subscription->yearly_price;
                }),
            2,
            '.',
            ''
        ));

        // Sales for the last 12 months
        $totalSalesLastOneYear = [];
        $currentDate->startOfMonth();

        for ($i = 0; $i <= 11; $i++) {
            $date = $currentDate->copy()->subMonths($i);

            $monthlyTotal = UserSubscription::with('subscription')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                // ->where('status', 1)
                ->get()
                ->sum(function ($item) {
                    if (!$item->subscription) return 0;
                    return $item->type === 'monthly'
                        ? $item->subscription->monthly_price
                        : $item->subscription->yearly_price;
                });

            $formattedMonthly = str_replace('.00', '', number_format($monthlyTotal, 2, '.', ''));
            $totalSalesLastOneYear[$date->format('M-y')] = $formattedMonthly;
        }

        $data['totalSalesLastOneYear'] = array_reverse($totalSalesLastOneYear);



        // $data['totalTodaySales'] = str_replace('.00', '', number_format(
        //     Order::whereDate('created_at', $today)
        //         ->where('status', 1)
        //         ->sum('total_price'),
        //     2,
        //     '.',
        //     ''
        // ));

        // $data['totalThisMonthSales'] = str_replace('.00', '', number_format(
        //     Order::whereYear('created_at', $currentDate->year)
        //         ->whereMonth('created_at', $currentDate->month)
        //         ->where('status', 1)->sum('total_price'),
        //     2,
        //     '.',
        //     ''
        // ));
        // $data['totalOverAllSales'] = str_replace('.00', '', number_format(Order::where('status', 1)->sum('total_price'), 2, '.', ''));

        // $totalSalesLastOneYear = [];
        // $currentDate->startOfMonth();

        // for ($i = 0; $i <= 11; $i++) {
        //     $date = $currentDate->copy()->subMonths($i);
        //     $totalSales = Order::whereYear('created_at', $date->year)
        //         ->whereMonth('created_at', $date->month)
        //         ->where('status', 1)->sum('total_price');

        //     $formattedTotalSales = str_replace('.00', '', number_format($totalSales, 2, '.', ''));
        //     $totalSalesLastOneYear[$date->format('M-y')] = $formattedTotalSales;
        // }

        // $data['totalSalesLastOneYear'] = array_reverse($totalSalesLastOneYear);

        return view('admin.dashboard', $data);
    }

    public function logout()
    {
        Session::flush();
        session()->forget('admin_info');
        return redirect('/');
    }

    public function getProfile()
    {
        return view('admin.profile');
    }

    public function editProfile()
    {
        return view('admin.edit_profile');
    }


    public function updateProfile(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'name' => 'required|string|min:3|max:100|regex:/^\S.*\S$/',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'number' => 'required|digits_between:10,15',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            // Add more validation rules for other fields if needed
        ]);

        // Update the user's profile information
        // $user = Auth::user();
        $user = User::find(Auth::id());
        $user->name = $request->name;
        $user->email = $request->email;
        $user->number = $request->number;

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                $oldImagePath = public_path('profile_images') . '/' . basename($user->profile_image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            // Upload the new profile image
            $profile_image = $request->file('profile_image');
            $fileName = uniqid() . '.' . $profile_image->getClientOriginalExtension();
            $profile_image->move(public_path('profile_images'), $fileName);

            $user->profile_image = $fileName;
        }
        // Update other fields as needed

        if ($user->save()) {
            Session::flash('message', 'Profile Updated Succesfully !');
            Session::flash('alert-class', 'success');
            return redirect('get-profile');
        } else {
            Session::flash('message', 'Oops !! Something went wrong!');
            Session::flash('alert-class', 'error');
            return redirect()->back();
        }
    }

    public function changePassword()
    {
        return view('admin.update_password');
    }

    public function updatePassword(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|same:password',
        ]);


        // if ($validator->fails()) {
        //     return redirect()->back()->withErrors($validator)->withInput();
        // }

        // Check if the user is authenticated
        if (Auth::check()) {
            // $user = Auth::user();
            $user = User::find(Auth::id());
            // Verify current password
            if (Hash::check($request->current_password, $user->password)) {
                $user->password = Hash::make($request->password);
                $user->show_password = $request->password;
                $user->save();

                Session::flash('message', 'Password Changed Succesfully !');
                Session::flash('alert-class', 'success');
                return redirect()->back();
            } else {
                Session::flash('message', 'Oops !! Something went wrong!');
                Session::flash('alert-class', 'error');
                return redirect()->back();
            }
        } else {
            // Redirect with error message
            return redirect()->back()->withErrors(['current_password' => 'Authentication error.'])->withInput();
        }
    }
}
