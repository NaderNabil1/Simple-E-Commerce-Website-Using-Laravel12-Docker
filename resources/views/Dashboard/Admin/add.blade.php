@extends('Dashboard.app')

@section('title', 'Add New Admin')
@section('breadcrumbs')
<li>-</li>
<li class="fw-medium"><a href="{{ route('admins') }}" class="d-flex align-items-center gap-1 hover-text-primary">Admins</a></li>
@endsection
@section('breadcrumb_title', 'Add New Admin')

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
                                    <input type="text" name='name' class="form-control radius-8 @error('name') is-invalid @enderror" id="name" placeholder="Name">
                                    @error('name')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                                </div>
                                <div class="col-sm-6">
                                    <label for="email" class="form-label fw-semibold text-primary-light text-md mb-8">Email</label>
                                    <input type="email" name='email' class="form-control radius-8 @error('email') is-invalid @enderror" id="email" placeholder="email">
                                    @error('email')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                                </div>
                            </div>
                            <br>
                            <div class="row gy-3">
                                <div class="col-sm-6">
                                    <label for="password" class="form-label fw-semibold text-primary-light text-md mb-8">Password</label>
                                    <input type="password" name='password' class="form-control radius-8 @error('password') is-invalid @enderror" id="password" placeholder="Password">
                                    @error('password')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                                </div>
                                <div class="col-sm-6">
                                    <label for="password_confirmation" class="form-label fw-semibold text-primary-light text-md mb-8">Confirm Password</label>
                                    <input type="password" name='password_confirmation' class="form-control radius-8" id="password_confirmation" placeholder="Confirm Password">
                                </div>
                            </div>

                            <div class="row gy-3 mt-1">
                                <div class="col-sm-4">
                                    <button type="submit" class="btn btn-primary border border-primary-600 text-md px-24 py-8 radius-8 w-100 text-center">
                                        Add Admin
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
                password: { required: true, minlength: 8 },
                password_confirmation: { required: true, equalTo: '#password' },
            },
            messages: {
                name: { required: "Please enter the Name", maxlength: "Maximum length is 255 characters" },
                email: { required: "Please enter an email address", email: "Please enter a valid email address", maxlength: "Maximum length is 255 characters" },
                password: { required: "Please enter a password", minlength: "Password must be at least 8 characters long" },
                password_confirmation: { required: "Please confirm your password", equalTo: "Passwords do not match" },
            },
        });
    });
</script>
@endsection
