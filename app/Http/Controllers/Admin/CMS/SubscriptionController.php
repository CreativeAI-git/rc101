<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Subscription::orderBy('id', 'DESC')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('serial_number', function ($row) {
                    static $index = 0;
                    return ++$index;
                })
                ->addColumn('description', function ($row) {
                    $plainText = strip_tags($row->description);
                    return strlen($plainText) > 50
                        ? substr($plainText, 0, 50) . '...'
                        : $plainText;
                })
                ->addColumn('monthly_price', function ($row) {
                    return "$ " . $row->monthly_price;
                })
                ->addColumn('type', function ($row) {
                    return $row->is_paid == 'Yes' ? 'Paid' : 'Free';
                })
                ->addColumn('yearly_price', function ($row) {
                    return "$ " . $row->yearly_price;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex align-items-center gap-3">
                        <a href="' . url('subscription-edit/' . $row->id) . '">
                                        <lord-icon data-bs-toggle="modal" data-bs-target="#ct_edit_product" src="https://cdn.lordicon.com/wuvorxbv.json" trigger="hover" colors="primary:#333333,secondary:#333333" style="width:20px;height:20px">
                                        </lord-icon>
                                    </a>
                        <a href="javascript:;"  title="Delete" onclick="deleteConfirm(' . $row->id . ')"><lord-icon src="https://cdn.lordicon.com/drxwpfop.json"
                        trigger="hover"
                        colors="primary:#ff0000,secondary:#ff0000"
                        style="width:20px;height:20px">
                    </lord-icon></a>
                     </div>';
                    return $btn;
                })
                ->rawColumns(['image', 'type', 'action'])
                ->make(true);
        }

        return view('admin.content_management.subscriptions.list');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.content_management.subscriptions.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'required|string',
            'is_paid' => 'required|in:Yes,No',
            'monthly_price' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->is_paid === 'Yes' && $value <= 0) {
                        $fail('Monthly price must be greater than 0 for paid plans.');
                    }
                    if ($request->is_paid === 'No' && $value != 0) {
                        $fail('Monthly price must be 0 for free plans.');
                    }
                },
            ],
            'yearly_price' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->is_paid === 'Yes' && $value <= 0) {
                        $fail('Yearly price must be greater than 0 for paid plans.');
                    }
                    if ($request->is_paid === 'No' && $value != 0) {
                        $fail('Yearly price must be 0 for free plans.');
                    }
                },
            ],
            'user_access_count' => 'required|numeric|min:0',
        ]);

        $monthlyPrice = $request->is_paid === 'No' ? 0 : $request->monthly_price;
        $yearlyPrice  = $request->is_paid === 'No' ? 0 : $request->yearly_price;

        $isInserted = Subscription::insert([
            'name' => $request->name,
            'description' => $request->description,
            'is_paid' => $request->is_paid,
            'monthly_price' => $monthlyPrice,
            'yearly_price' => $yearlyPrice,
            'user_access_count' => $request->user_access_count,
        ]);

        if ($isInserted) {
            return redirect('subscription-list')->with('success_msg', 'Data added successfully!');
        }

        return redirect('subscription-list')->with('error_msg', 'Something went wrong!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data['subscriptionData'] = Subscription::where('id', $id)->first();
        return view('admin.content_management.subscriptions.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:subscriptions,id',
            'name' => 'required|string|max:150',
            'description' => 'required|string',
            'is_paid' => 'required|in:Yes,No',
            'monthly_price' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->is_paid === 'Yes' && $value <= 0) {
                        $fail('Monthly price must be greater than 0 for paid plans.');
                    }
                    if ($request->is_paid === 'No' && $value != 0) {
                        $fail('Monthly price must be 0 for free plans.');
                    }
                },
            ],
            'yearly_price' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->is_paid === 'Yes' && $value <= 0) {
                        $fail('Yearly price must be greater than 0 for paid plans.');
                    }
                    if ($request->is_paid === 'No' && $value != 0) {
                        $fail('Yearly price must be 0 for free plans.');
                    }
                },
            ],
            'user_access_count' => 'required|numeric|min:0',
        ]);

        $id = $request->id;

        $monthlyPrice = $request->is_paid === 'No' ? 0 : $request->monthly_price;
        $yearlyPrice  = $request->is_paid === 'No' ? 0 : $request->yearly_price;

        $updateData = [
            'name' => $request->name,
            'description' => $request->description,
            'is_paid' => $request->is_paid,
            'monthly_price' => $monthlyPrice,
            'yearly_price' => $yearlyPrice,
            'user_access_count' => $request->user_access_count,
        ];

        $isUpdated = Subscription::where('id', $id)->update($updateData);

        if ($isUpdated) {
            return redirect('subscription-list')->with('success_msg', 'Data updated successfully!');
        }

        return redirect('subscription-list')->with('error_msg', 'Failed to update data.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $result = Subscription::where('id', $request->id)->first();

        if ($result) {
            Subscription::where('id', $request->id)->delete();
            return response()->json(['success' => true, 'message' => 'Data deleted successfully.'], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Data not found.'], 404);
        }
    }

    /**
     * Get the list of all subscribers from storage.
     */
    public function subscriberList(Request $request)
    {
        if ($request->ajax()) {
            // Expire any past-due subscriptions before listing
            UserSubscription::where('status', 1)
                ->whereNotNull('end_date')
                ->where('end_date', '<', now())
                ->update([
                    'status' => 2,
                    'stripe_subscription_id' => null,
                ]);

            $data = UserSubscription::with(['subscription', 'user'])->orderBy('id', 'DESC')->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('serial_number', function ($row) {
                    static $index = 0;
                    return ++$index;
                })
                ->addColumn('user_name', fn($row) => $row->user->name)
                ->addColumn('subscription_name', fn($row) => $row->subscription->name)
                ->addColumn('type', fn($row) => $row->type)
                ->addColumn('start_date', fn($row) => $row->start_date)
                ->addColumn('end_date', function ($row) {
                    if ($row->type === 'free') {
                        return '
                            <div class="editable-end-date" data-id="' . $row->id . '" style="display: flex; align-items: center; gap: 8px;">
                                <span class="date-text">' . e($row->end_date) . '</span>
                                <a href="javascript:void(0)" class="edit-end-date">
                                    <lord-icon
                                        src="https://cdn.lordicon.com/wuvorxbv.json"
                                        trigger="hover"
                                        colors="primary:#333333,secondary:#333333"
                                        style="width:20px;height:20px">
                                    </lord-icon>
                                </a>
                            </div>
                        ';
                    } else {
                        return '
                            <div class="editable-end-date" data-id="' . $row->id . '" style="display: flex; align-items: center; gap: 8px;">
                                <span class="date-text">' . e($row->end_date) . '</span>
                            </div>
                        ';
                    }
                })
                ->addColumn('user_access_count', function ($row) {

                    $count = $row->user_access_count ?? $row->subscription->user_access_count;

                    if ($row->type === 'free') {
                        return '
                            <div class="editable-user-count"
                                data-id="' . $row->id . '"
                                style="display:flex; align-items:center; gap:8px;">
                                
                                <span class="count-text">' . e($count) . '</span>

                                <a href="javascript:void(0)" class="edit-user-count">
                                    <lord-icon
                                        src="https://cdn.lordicon.com/wuvorxbv.json"
                                        trigger="hover"
                                        colors="primary:#333333,secondary:#333333"
                                        style="width:20px;height:20px">
                                    </lord-icon>
                                </a>
                            </div>
                        ';
                    }

                    return '
                        <div class="editable-user-count"
                            style="display:flex; align-items:center; gap:8px;">
                            <span class="count-text">' . e($count) . '</span>
                        </div>
                    ';
                })


                ->addColumn('amount', function ($row) {
                    $amount = 0;

                    if ($row->type === "monthly") {
                        $amount = $row->subscription->monthly_price ?? 0;
                    } elseif ($row->type === "yearly") {
                        $amount = $row->subscription->yearly_price ?? 0;
                    }

                    return "$ " . $amount;
                })
                ->addColumn('status', function ($row) {
                    if ($row->status == 1) {
                        return '<span style="color:green; font-weight:bold;">Active</span>';
                    } else if ($row->status == 2) {
                        return '<span style="color:red; font-weight:bold;">Expired</span>';
                    } else if ($row->status == 0) {
                        return '<span style="color:orange; font-weight:bold;">Cancelled</span>';
                    } else {
                        return '<span style="color:blue; font-weight:bold;">Pending</span>';
                    }
                })
                ->rawColumns(['user_name', 'subscription_name', 'amount', 'end_date', 'user_access_count', 'status', 'actions'])
                ->make(true);
        }

        return view('admin.content_management.subscribers.list');
    }

    // public function subscriberList(Request $request)
    // {
    //     if ($request->ajax()) {
    //         $data = UserSubscription::with(['subscription', 'user'])->orderBy('id', 'DESC')->get();

    //         return DataTables::of($data)
    //             ->addIndexColumn()
    //             ->addColumn('serial_number', function ($row) {
    //                 static $index = 0;
    //                 return ++$index;
    //             })
    //             ->addColumn('user_name', fn($row) => $row->user->name)
    //             ->addColumn('subscription_name', fn($row) => $row->subscription->name)
    //             ->addColumn('type', fn($row) => $row->type)
    //             ->addColumn('start_date', fn($row) => $row->start_date)
    //             ->addColumn('end_date', fn($row) => $row->end_date)
    //             ->addColumn('amount', function ($row) {
    //                 $amount = 0;

    //                 if ($row->type === "monthly") {
    //                     $amount = $row->subscription->monthly_price ?? 0;
    //                 } elseif ($row->type === "yearly") {
    //                     $amount = $row->subscription->yearly_price ?? 0;
    //                 }

    //                 return "$ " . $amount;
    //             })
    //             ->addColumn('status', function ($row) {
    //                 if ($row->end_date > date('Y-m-d')) {
    //                     return '<span style="color:green; font-weight:bold;">Active</span>';
    //                 } else {
    //                     return '<span style="color:red; font-weight:bold;">Expired</span>';
    //                 }
    //             })
    //             ->addColumn('actions', function ($row) {
    //                 if ($row->type === 'free') {
    //                     return '<div class="d-flex align-items-center gap-3"><a href="javascript:void(0)" class="edit-end-date" data-id="' . $row->id . '" data-end-date="' . $row->end_date . '"><lord-icon data-bs-toggle="modal" data-bs-target="#ct_edit_product" src="https://cdn.lordicon.com/wuvorxbv.json" trigger="hover" colors="primary:#333333,secondary:#333333" style="width:20px;height:20px">
    //                     </lord-icon></a></div>';
    //                 }
    //                 return '-';
    //             })
    //             ->rawColumns(['user_name', 'subscription_name', 'amount', 'status', 'actions'])
    //             ->make(true);
    //     }

    //     return view('admin.content_management.subscribers.list');
    // }

    // public function updateEndDate(Request $request)
    // {
    //     $request->validate([
    //         'id' => 'required|integer|exists:user_subscriptions,id',
    //         'end_date' => 'required|date|after_or_equal:start_date',
    //     ]);

    //     $subscription = UserSubscription::find($request->id);
    //     $subscription->end_date = $request->end_date;
    //     $subscription->save();

    //     return response()->json(['success' => true, 'message' => 'End date updated successfully!']);
    // }
    public function updateEndDate(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'end_date' => 'required|date',
        ]);

        $subscription = UserSubscription::find($request->id);
        if (!$subscription) {
            return response()->json(['success' => false, 'message' => 'Subscription not found']);
        }

        $rawEndDate = $request->end_date;
        $endDate = Carbon::parse($rawEndDate);
        // If only a date is provided (no time), treat it as end of that day
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawEndDate)) {
            $endDate = $endDate->endOfDay();
        }
        $subscription->end_date = $endDate;
        $subscription->status = $endDate->greaterThanOrEqualTo(now()) ? 1 : 2;
        $subscription->save();

        return response()->json(['success' => true, 'message' => 'End date updated successfully']);
    }

    public function updateUserAccessCount(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:user_subscriptions,id',
            'user_access_count' => 'required|integer|min:1',
        ]);

        UserSubscription::where('id', $request->id)->update([
            'user_access_count' => $request->user_access_count
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User access count updated successfully'
        ]);
    }
}
