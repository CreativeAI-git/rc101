@extends('admin.layouts.app')
@section('content')

<div class="main-content introduction-farm">
    <div class="content-wraper-area">
        <div class="dashboard-area">
            <div class="container-fluid">
                <div class="row g-4">
                    @if(Session::has('error_msg'))
                    <div class="alert alert-danger"> {{ Session::get('error_msg') }} </div>
                    @endif

                    @if (Session::has('success_msg'))
                    <div class="alert alert-success"> {{ Session::get('success_msg') }} </div>
                    @endif
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title border-bootom-none mb-30 d-flex align-items-center justify-content-between">
                                    <h6 class="mb-0">All Knowledgebase Articles</h6>
                                    <a href="{{ url('cms/embeds-add') }}">
                                        <button class="ct_custom_btn1 mx-auto">Add New Knowledgebase Article</button>
                                    </a>
                                </div>

                                <div class="">
                                    <table class="table embeds-data-table table-responsive table-bordered table-hover mb-0" id="embedsTable">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Image</th>
                                                <th>Title</th>
                                                <th>Type (Doc/Slide/Link)</th>
                                                <th>Menu</th>
                                                <th>Embed Link/Linked Name</th>
                                                <th width="100px">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- geting this result from dataTable -->
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
</div>

<script type="text/javascript">
    $(function() {
        var table = $('.embeds-data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ url('cms/embeds-list') }}",
                data: function(d) {
                    d.menu_type = $('#menuFilter').val(); // send filter value to backend
                }
            },
            dom: '<"d-flex align-items-center justify-content-between mb-3 gap-3"lf>rtip',
            columns: [{
                    data: 'serial_number',
                    name: 'serial_number'
                },
                {
                    data: 'image',
                    name: 'image'
                },
                {
                    data: 'title',
                    name: 'title'
                },
                {
                    data: 'type',
                    name: 'type'
                },
                {
                    data: 'menu_type',
                    name: 'menu_type'
                },
                {
                    data: 'embed_link',
                    name: 'embed_link'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ],
            initComplete: function() {

                // Create filter dropdown
                var filterHtml = `
                    <div class="dataTables_length menu-filter">
                        <label>Filter by Menu:</label>
                        <select id="menuFilter" class="form-control form-control-sm">
                            <option value="">All Menus</option>
                            <option value="lexicon">Lexicon</option>
                            <option value="weight_classes">Weight Classes</option>
                            <option value="vendors">Vendors</option>
                            <option value="youtube_channel">Youtube Channel</option>
                            <option value="tools">Tools</option>
                            <option value="robot_combat_starter_kits">Robot Combat Starter Kits</option>
                        </select>
                    </div>
                `;

                // Insert filter BEFORE the search box
                $('.dataTables_filter').before(filterHtml);

                // Reload table on filter change
                $('#menuFilter').on('change', function() {
                    table.ajax.reload();
                });
            }
        });
    });
</script>
<script>
    function deleteConfirm(id) {
        bootbox.confirm({
            closeButton: false,
            message: '<p class="text-center mb-0" style="font-size: 20px;">Are you sure you want to delete?</p>',
            buttons: {
                'cancel': {
                    label: 'No',
                    className: 'btn-danger'
                },
                'confirm': {
                    label: 'Yes',
                    className: 'btn-success'
                }
            },
            callback: function(result) {
                if (result) {
                    $.ajax({
                        url: "{{ url('cms/embeds-delete') }}",
                        type: "POST",
                        cache: false,
                        data: {
                            _token: '{{ csrf_token() }}',
                            id: id
                        },
                        success: function(response) {
                            if (response.error) {
                                toastr.error(response.error);
                            } else {
                                toastr.success(response.message);
                                $('#embedsTable').DataTable().ajax.reload(null, false);
                            }
                        },
                        error: function(xhr, textStatus, errorThrown) {
                            if (xhr.status === 422) {
                                toastr.error(xhr.responseJSON.error);
                            } else if (xhr.status === 404) {
                                toastr.error(xhr.responseJSON.error);
                            } else {
                                toastr.error('An error occurred while processing your request.');
                            }
                        }

                    });
                }
            }
        });
    }
</script>
@endsection