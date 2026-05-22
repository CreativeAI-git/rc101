@extends('admin.layouts.app')

@section('content')

<!-- Main Content Area -->
<div class="main-content introduction-farm">
    <div class="content-wraper-area">
        <div class="dashboard-area">
            <div class="container-fluid">
                <div class="row g-4">
                    <div class="col-12">
                        @if(Session::has('error_msg'))
                        <div class="alert alert-danger"> {{ Session::get('error_msg') }} </div>
                        @endif

                        @if (Session::has('success_msg'))
                        <div class="alert alert-success"> {{ Session::get('success_msg') }} </div>
                        @endif
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="dashboard-header-title">
                                <h5 class="mb-0">Congratulations</h5>
                                <p class="mb-0">You have earns <span class="text-success">${{ $totalTodaySales }}</span> today.
                                </p>
                            </div>


                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-6 col-xxl-3">
                        <div class="card ">
                            <div class="card-body">
                                <div
                                    class="single-widget d-flex align-items-center justify-content-center">
                                    <div>
                                        <div class="widget-icon mx-auto">
                                            <lord-icon
                                                src="https://cdn.lordicon.com/taymdfsf.json"
                                                trigger="loop"
                                                delay="2000"
                                                style="width:50px;height:50px">
                                            </lord-icon>
                                        </div>
                                        <div class="widget-desc">
                                            <h3 class="text-center">{{ $totalSubscription }}</h3>
                                            <h5><a href="{{url('subscription-list');}}">Total Plans</a></h5>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-6 col-xxl-3">
                        <div class="card">
                            <div class="card-body">
                                <div
                                    class="single-widget d-flex align-items-center justify-content-center">
                                    <div>
                                        <div class="widget-icon mx-auto">
                                            <lord-icon
                                                src="https://cdn.lordicon.com/piwupaqb.json"
                                                trigger="loop"
                                                style="width:50px;height:50px">
                                            </lord-icon>
                                        </div>
                                        <div class="widget-desc">
                                            <h3 class="text-center">{{ $totalUser }}</h3>
                                            <h5><a href="{{url('users/list-student');}}">All Students</a></h5>

                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-6 col-xxl-3">
                        <div class="card">
                            <div class="card-body">
                                <div
                                    class="single-widget d-flex align-items-center justify-content-center">
                                    <div>
                                        <div class="widget-icon mx-auto">
                                            <lord-icon
                                                src="https://cdn.lordicon.com/odavpkmb.json"
                                                trigger="loop"
                                                style="width:50px;height:50px">
                                            </lord-icon>

                                        </div>
                                        <div class="widget-desc">
                                            <h3 class="text-center">{{ $totalSubscriber }}</h3>
                                            <h5><a href="{{url('subscriber-list');}}">All Subscribers</a></h5>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-6 col-xxl-3">
                        <div class="card">
                            <div class="card-body" data-intro="Growth">
                                <div
                                    class="single-widget d-flex align-items-center justify-content-center">
                                    <div>
                                        <div class="widget-icon">
                                            <lord-icon
                                                src="https://cdn.lordicon.com/zlbisnuz.json"
                                                trigger="loop"
                                                style="width:50px;height:50px">
                                            </lord-icon>
                                        </div>
                                        <div class="widget-desc">
                                            <h3 class="text-center">${{ $totalOverAllSales }}</h3>
                                            <h5><a href="{{url('subscriber-list');}}">Total Sales</a></h5>

                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-sm-12 col-lg-12">
                        <div class="card w-100 position-relative overflow-hidden">
                            <div class="card-body pb-4">
                                <div class="card-title">
                                    <h4> Monthly Earnings </h4>
                                    <h6 class="text-success">${{ $totalThisMonthSales }}</h6>
                                    <input type="hidden" id="totalSalesLastOneYear" value="{{ json_encode($totalSalesLastOneYear ?? '') }}">
                                </div>

                                <div id="most-visited"></div>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="col-sm-6 col-lg-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="card-title">
                                                <h4>Sales Overview</h4>
                                                <p class="mb-0">Overview of Profit</p>
                                            </div>
                                            <div id="sales-overview"></div>
                                        </div>
                                    </div>
                                </div> -->

                    <!-- <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div
                                                class="card-title border-bootom-none mb-30 d-flex align-items-center justify-content-between">
                                                <h6 class="mb-0">All Sold Products</h6>
                                              
                                            </div>
                                            <div class="table-responsive text-nowrap">
                                                <table class="table table-centered table-nowrap table-hover mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Product Name</th>
                                                            <th>Category</th>
                                                            <th>Sub Category</th>
                                                           
                                                            <th>Total Price</th>
                                                            <th>Status</th>
                                                            <th>Payment Method</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td class="d-flex align-items-center"><img class="shop-img"
                                                                    src="{{asset('admin/img/shop-img/1.png')}}" alt="">
                                                                <span>Sound Box</span>
                                                            </td>
                                                            <td>Motors</td>
                                                            <td>Drive Motors</td>
                                                            <td>$125</td>
                                                            <td class="text-success">In stock</td>
                                                            <td>
                                                               Credit Card
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="d-flex align-items-center"><img class="shop-img"
                                                                    src="{{asset('admin/img/shop-img/1.png')}}" alt="">
                                                                <span>Sound Box</span>
                                                            </td>
                                                            <td>Motors</td>
                                                            <td>Drive Motors</td>
                                                            <td>$125</td>
                                                            <td class="text-success">In stock</td>
                                                            <td>
                                                               Debit Card
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="d-flex align-items-center"><img class="shop-img"
                                                                    src="{{asset('admin/img/shop-img/1.png')}}" alt="">
                                                                <span>Sound Box</span>
                                                            </td>
                                                            <td>Motors</td>
                                                            <td>Drive Motors</td>
                                                            <td>$125</td>
                                                            <td class="text-success">In stock</td>
                                                            <td>
                                                               Credit Card
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="d-flex align-items-center"><img class="shop-img"
                                                                    src="{{asset('admin/img/shop-img/1.png')}}" alt="">
                                                                <span>Sound Box</span>
                                                            </td>
                                                            <td>Motors</td>
                                                            <td>Drive Motors</td>
                                                            <td>$125</td>
                                                            <td class="text-success">In stock</td>
                                                            <td>
                                                               Debit Card
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div> -->
                </div>
            </div>
        </div>
        @endsection