@extends('admin.layouts.app')
@section('content')

<!-- Main Content Area -->
<div class="main-content introduction-farm">
    <div class="content-wraper-area">
        <div class="dashboard-area">
            <div class="container-fluid">
                <div class="row g-4">

                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                @if(Session::has('error_msg'))
                                <div class="alert alert-danger"> {{ Session::get('error_msg') }} </div>
                                @endif

                                @if (Session::has('success_msg'))
                                <div class="alert alert-success"> {{ Session::get('success_msg') }} </div>
                                @endif
                                <div
                                    class="card-title border-bootom-none mb-30 d-flex align-items-center justify-content-between">
                                    <h3 class="mb-0 ct_fs_22">Add Knowledgebase Article</h3>
                                    <a href="{{url('cms/embeds-list')}}"> <button class="ct_custom_btn1 mx-auto"> Back to List </button> </a>
                                </div>
                                <form action="{{url('cms/embeds-save')}}" method="POST" id="addEmbeds" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group mb-3">
                                                <label for="title" class="mb-2">Title</label>
                                                <input type="text" class="form-control ct_input" name="title" placeholder="Title" value="{{ old('title')}}">
                                                @error('title')
                                                <div class="text text-danger mt-2">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group mb-3">
                                                <label for="type" class="mb-2">Select Type</label>
                                                <select name="type" class="form-control ct_input">
                                                    <option value="" disabled selected>Select Doc/Slide/Link</option>
                                                    <option value="doc" {{ old('type') == 'doc' ? 'selected' : '' }}>Doc</option>
                                                    <option value="slide" {{ old('type') == 'slide' ? 'selected' : '' }}>Slide</option>
                                                    <option value="link" {{ old('type') == 'link' ? 'selected' : '' }}>Link</option>
                                                </select>
                                                @error('type')
                                                <div class="text text-danger mt-2">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group mb-3">
                                                <label for="menu_type" class="mb-2">Select Menu Type</label>
                                                <select name="menu_type" class="form-control ct_input">
                                                    <option value="" disabled selected>Select Menu</option>
                                                    <option value="lexicon" {{ old('menu_type') == 'lexicon' ? 'selected' : '' }}>Lexicon</option>
                                                    <option value="weight_classes" {{ old('menu_type') == 'weight_classes' ? 'selected' : '' }}>Weight Classes</option>
                                                    <option value="vendors" {{ old('menu_type') == 'vendors' ? 'selected' : '' }}>Vendors</option>
                                                    <option value="youtube_channel" {{ old('menu_type') == 'youtube_channel' ? 'selected' : '' }}>Youtube Channel</option>
                                                    <option value="tools" {{ old('menu_type') == 'tools' ? 'selected' : '' }}>Tools</option>
                                                    <option value="robot_combat_starter_kits" {{ old('menu_type') == 'robot_combat_starter_kits' ? 'selected' : '' }}>Robot Combat Starter Kits</option>
                                                </select>
                                                @error('menu_type')
                                                <div class="text text-danger mt-2">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group mb-3">
                                                <label for="linked_name" class="mb-2">Linked Name</label>
                                                <input type="text" class="form-control ct_input" name="linked_name" placeholder="Linked Name" value="{{ old('linked_name')}}">
                                                @error('linked_name')
                                                <div class="text text-danger mt-2">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group mb-3">
                                                <label for="embed_link" class="mb-2">Embed Link/URL</label>
                                                <input type="text" class="form-control ct_input" name="embed_link" placeholder="Embed Link" value="{{ old('embed_link')}}">
                                                @error('embed_link')
                                                <div class="text text-danger mt-2">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group mb-3">
                                                <label for="image" class="mb-2">Image</label>
                                                <input name="image" type="file" class="form-control ct_input" onchange="loadImage(event)" accept="image/*">

                                                <!-- Display the selected image preview here -->
                                                <div id="imagePreview" class="mt-2" style="display: none;">
                                                    <img id="image" src="" alt="Image Preview" style="width: 100px; height: 100px; border-radius: 8px;">
                                                </div>
                                                @error('image')
                                                <div class="text text-danger mt-2">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center mt-4">
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
        $('#addEmbeds').validate({
            ignore: [],
            rules: {
                title: {
                    required: true,
                    maxlength: 150, // Ensure the length is within 150 characters
                },
                linked_name: {
                    maxlength: 100, // Ensure the length is within 100 characters
                },
            },
            messages: {
                title: {
                    required: "The title is required.",
                    maxlength: "The title must not exceed 150 characters.",
                },
                linked_name: {
                    maxlength: "The linked name must not exceed 100 characters.",
                },
            },
            submitHandler: function(form) {
                form.submit();
            }
        });
    });
</script>
<script>
    var loadImage = function(event) {
        var image = document.getElementById('image');
        var imagePreview = document.getElementById('imagePreview');

        if (event.target.files && event.target.files[0]) {
            image.src = URL.createObjectURL(event.target.files[0]);
            image.style.display = 'block';
            imagePreview.style.display = 'block';
        }
    };
</script>
@endsection