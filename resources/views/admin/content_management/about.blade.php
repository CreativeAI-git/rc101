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
                                    <h3 class="mb-0 ct_fs_22">About Section</h3>
                                </div>
                                <form action="{{url('cms/update-about')}}" method="POST" id="aboutSectionForm" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="id" value="{{$aboutSection->id ?? null}}">
                                    <div class="mb-3">
                                        <label for=""><strong>Title</strong></label>
                                        <input name="about_title" type="text" class="form-control ct_input" placeholder="About Title" value="{{ old('about_title', $aboutSection->about_title ?? '') }}">
                                        @error('about_title')
                                        <div class="text text-danger mt-2">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="about_content"><strong>About Content</strong></label>
                                        <textarea name="about_content" id="about_content" class="form-control" cols="30" rows="5" placeholder="About Description">{{ old('about_content', $aboutSection->about_content ?? '') }}</textarea>
                                        @error('about_content')
                                        <div class="text text-danger mt-2">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3 col-md-12">
                                        <label for="about_banner" class="mb-2"><strong>Banner Image</strong></label>
                                        <input name="about_banner" type="file" class="form-control ct_input" onchange="loadBannerImage(event)" accept="image/*">

                                        <img
                                            id="about_banner"
                                            src="{{ $aboutSection && $aboutSection->about_banner ? asset('cms_images/' . $aboutSection->about_banner) : '' }}"
                                            style="width: 100px; height: 100px; border-radius: 8px; display: <?= (!empty($aboutSection) && !empty($aboutSection->about_banner)) ? 'block' : 'none'; ?>"
                                            class="mt-2">

                                        @error('about_banner')
                                        <div class="text-danger mt-2">{{ $message }}</div>
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
    const {
        ClassicEditor,
        Essentials,
        Bold,
        Italic,
        Underline,
        Font,
        Paragraph,
        List,
        Link,
        Table,
        TableToolbar,
        Image,
        ImageUpload,
        ImageStyle,
        ImageResize,
        ImageToolbar,
        Heading,
        Alignment,
        CKFinderUploadAdapter
    } = CKEDITOR;

    ClassicEditor.create(document.querySelector('#about_content'), {
        plugins: [
            Essentials,
            Paragraph,
            Heading,
            Bold,
            Italic,
            Underline,
            Font,
            List,
            Link,
            Alignment,

            Table,
            TableToolbar,

            Image,
            ImageUpload,
            ImageStyle,
            ImageResize,
            ImageToolbar,

            CKFinderUploadAdapter
        ],

        toolbar: [
            'heading',
            '|',
            'undo', 'redo',
            '|',
            'bold', 'italic', 'underline',
            '|',
            'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor',
            '|',
            'alignment:left',
            'alignment:center',
            'alignment:right',
            'alignment:justify',
            '|',
            'link',
            'insertTable',
            'imageUpload',
            'imageResize',
            '|',
            'bulletedList', 'numberedList'
        ],

        image: {
            styles: [
                'alignLeft',
                'alignCenter',
                'alignRight'
            ],
            resizeOptions: [{
                    name: 'resizeImage:original',
                    label: 'Original',
                    value: null
                },
                {
                    name: 'resizeImage:25',
                    label: '25%',
                    value: '25'
                },
                {
                    name: 'resizeImage:50',
                    label: '50%',
                    value: '50'
                },
                {
                    name: 'resizeImage:75',
                    label: '75%',
                    value: '75'
                },
                {
                    name: 'resizeImage:100',
                    label: '100%',
                    value: '100'
                }
            ],
            toolbar: [
                'imageStyle:alignLeft',
                'imageStyle:alignCenter',
                'imageStyle:alignRight',
                '|',
                'resizeImage'
            ]
        },

        heading: {
            options: [{
                    model: 'paragraph',
                    title: 'Normal text',
                    class: 'ck-heading_paragraph'
                },
                {
                    model: 'heading1',
                    view: 'h1',
                    title: 'Title',
                    class: 'ck-heading_heading1'
                },
                {
                    model: 'heading2',
                    view: 'h2',
                    title: 'Subtitle',
                    class: 'ck-heading_heading2'
                },
                {
                    model: 'heading3',
                    view: 'h3',
                    title: 'Heading 1',
                    class: 'ck-heading_heading3'
                },
                {
                    model: 'heading4',
                    view: 'h4',
                    title: 'Heading 2',
                    class: 'ck-heading_heading4'
                },
                {
                    model: 'heading5',
                    view: 'h5',
                    title: 'Heading 3',
                    class: 'ck-heading_heading5'
                },
                {
                    model: 'heading6',
                    view: 'h6',
                    title: 'Heading 4',
                    class: 'ck-heading_heading6'
                }
            ]
        },

        fontSize: {
            options: [10, 12, 14, 'default', 18, 20, 24, 28, 32, 36]
        },

        table: {
            contentToolbar: [
                'tableColumn',
                'tableRow',
                'mergeTableCells'
            ]
        },
        
        ckfinder: {
            uploadUrl: "{{ route('aboutus.content.image') }}?_token={{ csrf_token() }}"
        },
    }).catch(console.error);
</script>

<script>
    $(document).ready(function() {
        $.validator.addMethod("filesize", function(value, element, param) {
            // Validate only if a file is selected
            if (element.files.length > 0) {
                var fileSizeInKB = element.files[0].size / 1024; // Size in KB
                return fileSizeInKB <= param;
            }
            return true;
        }, 'File size must not exceed 2MB.');

        $('#aboutSectionForm').validate({
            ignore: [],
            rules: {
                about_title: {
                    required: true,
                    maxlength: 100,
                },
                about_content: {
                    required: true,
                },
                about_banner: {
                    extension: "jpg|jpeg|png|gif", // Allowed file extensions
                    filesize: 2048 // File size in KB
                },
            },
            messages: {
                about_title: {
                    required: "Please enter about title.",
                    maxlength: "The about title must not exceed 100 characters.",
                },
                about_content: "Please enter about content.",
                about_banner: {
                    extension: 'Allowed file types are JPG, JPEG, PNG, GIF.',
                    filesize: 'File size must not exceed 2MB.'
                }
            },
            errorPlacement: function(error, element) {
                if (element.attr("name") == "about_content") {
                    error.appendTo(element.next());
                } else {
                    error.insertAfter(element);
                }
            },
            submitHandler: function(form) {
                form.submit();
            }
        });
    });
</script>
<script>
    var loadBannerImage = function(event) {
        var image = document.getElementById('about_banner');
        if (event.target.files && event.target.files[0]) {
            image.src = URL.createObjectURL(event.target.files[0]);
            image.style.display = 'block';
        }
    };
</script>
@endsection