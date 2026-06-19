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
                        <div class="card ">
                            <div class="card-body card-breadcrumb">
                                <div class="page-title-box mb-4">
                                    <h3 class="mb-0 ct_fs_22">Visitor Content</h3>
                                </div>
                                <form action="{{url('cms/update-visitor-content')}}" method="POST" id="visitorContentForm" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="id" value="{{$visitorContent->id ?? null}}">
                                    <div class="mb-3">
                                        <label for=""><strong>Title</strong></label>
                                        <input name="title" type="text" class="form-control ct_input" placeholder="Visitor Title" value="{{ old('title', $visitorContent->title ?? '') }}">
                                        @error('title')
                                        <div class="text text-danger mt-2">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="description"><strong>Visitor Content</strong></label>
                                        <textarea name="description" id="description" class="form-control" cols="30" rows="5" placeholder="Visitor Description">{{ old('description', $visitorContent->description ?? '') }}</textarea>
                                        @error('description')
                                        <div class="text text-danger mt-2">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="text-center mt-5">
                                        <button type="submit" class="ct_custom_btn1 mx-auto">Save</button>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('script')

<script>
    $(document).ready(function() {
        $('#visitorContentForm').validate({
            ignore: [],
            rules: {
                title: {
                    required: true,
                    maxlength: 150,
                },
                description: {
                    required: true,
                    maxlength: 255,
                }
            },
            messages: {
                title: {
                    required: "Please enter visitor title.",
                    maxlength: "The visitor title must not exceed 150 characters.",
                },
                description: {
                    required: "Please enter visitor description.",
                    maxlength: "The visitor description must not exceed 255 characters."
                }
            },
            submitHandler: function(form) {
                form.submit();
            }
        });
    });
</script>
@endsection