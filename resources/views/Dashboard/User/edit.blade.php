@extends('Dashboard.app')

@section('title', 'Edit User')
@section('breadcrumbs')
<li>-</li>
<li class="fw-medium"><a href="{{ route('users') }}" class="d-flex align-items-center gap-1 hover-text-primary">Users</a></li>
@endsection
@section('breadcrumb_title', 'Edit User')

@section('content')
<div class="card h-100 p-0 radius-12">
    <div class="card-body p-24">
        <div class="row gy-4">
            <div class="col-xxl-12">
                <div class="card radius-12 shadow-none border overflow-hidden">
                    <div class="card-body p-24">
                        <form method="post" id="form">
                            @csrf
                           <div class="row gy-3">
                                <div class="col-sm-6">
                                    <label for="name" class="form-label fw-semibold text-primary-light text-md mb-8">Name <span class="text-danger-600">*</span></label>
                                    <input type="text" name='name' class="form-control radius-8 @error('name') is-invalid @enderror" id="name" placeholder="Name" value="{{ $user->name }}">
                                    @error('name')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                                </div>
                                <div class="col-sm-6">
                                    <label for="email" class="form-label fw-semibold text-primary-light text-md mb-8">Email</label>
                                    <input type="email" name='email' class="form-control radius-8 @error('email') is-invalid @enderror" id="email" placeholder="email" value="{{ $user->email }}">
                                    @error('email')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                                </div>
                            </div>
                            <br>
                            <div class="row gy-3">
                                <div class="col-sm-2">
                                    <button type="submit" class="btn btn-primary border border-primary-600 text-md px-24 py-8 radius-8 w-100 text-center">
                                        Update User
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
<script type="text/javascript">
    $(document).ready(function() {
        $('#form').validate({
            rules: {
                name: { required: true, maxlength: 255 },
                email: { required: true, email: true, maxlength: 255 },
            },
            messages: {
                name: { required: "Please enter the Name", maxlength: "Maximum length is 255 characters" },
                email: { required: "Please enter an email address", email: "Please enter a valid email address", maxlength: "Maximum length is 255 characters" },
            },
        });
    });
</script>
@endsection
