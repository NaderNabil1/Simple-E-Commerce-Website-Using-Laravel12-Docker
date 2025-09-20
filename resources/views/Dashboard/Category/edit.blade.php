@extends('Dashboard.app')

@section('title', 'Edit Category')
@section('breadcrumbs')
<li>-</li>
<li class="fw-medium"><a href="{{ route('be-categories') }}" class="d-flex align-items-center gap-1 hover-text-primary">Categories</a></li>
@endsection
@section('breadcrumb_title', 'Edit Category')

@section('content')
<div class="card h-100 p-0 radius-12">
    <div class="card-body p-24">
        <div class="row gy-4">
            <div class="col-xxl-12">
                <div class="card radius-12 shadow-none border overflow-hidden">
                    <div class="card-body p-24">
                        <form method="post" id="form" enctype="multipart/form-data">
                            @csrf
                            <div class="row gy-3">
                                <div class="col-sm-6">
                                    <label for="name" class="form-label fw-semibold text-primary-light text-md mb-8">Name <span class="text-danger-600">*</span></label>
                                    <input type="text" name='name' class="form-control radius-8 @error('name') is-invalid @enderror" id="name" placeholder="Name" required value="{{ $category->name }}">
                                    @error('name')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                                </div>

                                <div class="col-sm-12">
                                    <label class="form-check-label">
                                        <input type="hidden" name="status" value="0">
                                        <input class="form-check-input" type="checkbox" name="status" value="1" {{ $category->status == 1 ? 'checked' : '' }}> Publish
                                    </label>
                                </div>

                                <div class="col-sm-3">
                                    <button type="submit" class="btn btn-primary border border-primary-600 text-md px-24 py-8 radius-8 w-100 text-center">
                                        Update Category
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascripts')
<script type="text/javascript" src="{{ asset('Dashboard/js/jquery.validate.js') }}"></script>
<script>

    $(document).ready(function() {
        $('#form').validate({
            rules: {
                name: { required: true, maxlength: 255 },
            },
            messages: {
                name: { required: "Category Name is required", maxlength: "Maximum length is 255 characters" },
            },
        });
    });
</script>
@endsection

