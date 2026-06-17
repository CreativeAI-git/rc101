@extends('admin.layouts.app')

@section('content')

<div class="main-content introduction-farm">
    <div class="content-wraper-area">
        <div class="dashboard-area">
            <div class="container-fluid">

                <div class="row g-4">
                    <div class="col-lg-12">

                        <div class="card">
                            <div class="card-body">

                                <div class="card-title border-bootom-none mb-30 d-flex align-items-center justify-content-between">
                                    <h6 class="mb-0">Visitor Emails</h6>
                                </div>

                                <table class="table visitor-data-table table-responsive table-bordered table-hover mb-0">

                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Email</th>
                                            <th>Visit Count</th>
                                            <th>IP Address</th>
                                            <th>First Visited</th>
                                            <th>Last Visited</th>
                                            <th>Created At</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                    </tbody>

                                </table>

                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(function () {

    $('.visitor-data-table').DataTable({
        processing: true,
        serverSide: true,

        ajax: "{{ url('users/list-visitor') }}",

        columns: [
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                searchable: false,
                orderable: false
            },
            {
                data: 'email',
                name: 'email'
            },
            {
                data: 'visit_count',
                name: 'visit_count'
            },
            {
                data: 'ip_address',
                name: 'ip_address'
            },
            {
                data: 'first_visited_at',
                name: 'first_visited_at'
            },
            {
                data: 'last_visited_at',
                name: 'last_visited_at'
            },
            {
                data: 'created_at',
                name: 'created_at'
            }
        ],

        order: [[0, 'desc']]
    });

});
</script>

@endsection