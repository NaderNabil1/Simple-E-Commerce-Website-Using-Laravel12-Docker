@extends('Dashboard.app')

@section('title', 'Add Product')
@section('breadcrumbs')
<li>-</li>
<li class="fw-medium"><a href="{{ route('products') }}" class="d-flex align-items-center gap-1 hover-text-primary">Products</a></li>
@endsection
@section('breadcrumb_title', 'Add Product')

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
                                    <input type="text" name='name' class="form-control radius-8 @error('name') is-invalid @enderror" id="name" placeholder="Product Name">
                                    @error('name')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                                </div>

                                <div class="col-sm-6">
                                    <label for="category_id" class="form-label fw-semibold text-primary-light text-md mb-8">Category</label>
                                    <select name="category_id" class="form-select form-control radius-8" id="category_id">
                                        @foreach($categories as $category)
                                        <option value="{{ $category->id }}"{{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-sm-6">
                                    <label for="description" class="form-label fw-semibold text-primary-light text-md mb-8">Description</label>
                                    <textarea name="description" class="form-control" id="description" placeholder="Enter a Description..."></textarea>
                                    @error('description')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                                </div>



                                <div class="row gy-3">
                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold text-primary-light text-md mb-8" for="price">Price</label>
                                        <input name="price" id="price" type="number" step="0.01" class="form-control radius-8 @error('price') is-invalid @enderror" placeholder="0.00" value="{{ old('price') }}">
                                        @error('price')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                    <div class="col-sm-6">
                                        <label  class="form-label fw-semibold text-primary-light text-md mb-8" for="">Stock</label>
                                        <input name="stock_quantity" type="number" step="1" class="form-control radius-8 @error('stock_quantity') is-invalid @enderror" placeholder="0" value="{{ old('stock_quantity') }}">
                                    </div>
                                </div>
                                <div class="row gy-3">
                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold text-primary-light text-md mb-8" for="sale_price">Sale Price</label>
                                        <input id="sale_price" name="sale_price" type="number" step="0.01" class="form-control @error('sale_price') is-invalid @enderror" placeholder="0.00" value="{{ old('sale_price') }}">
                                        @error('sale_price')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold text-primary-light text-md mb-8" for="sale_end_date">Sale End Date</label>
                                        <input name="sale_end_date" type="date" class="form-control @error('sale_end_date') is-invalid @enderror" value="{{ old('sale_end_date') }}">
                                        @error('sale_end_date')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <label class="form-check-label">
                                        <input type="hidden" name="status" value="0">
                                        <input class="form-check-input" type="checkbox" name="status" value="1"> Publish
                                    </label>
                                </div>

                                <div class="col-sm-2">
                                    <button type="submit" class="btn btn-primary border border-primary-600 text-md px-24 py-8 radius-8 w-100 text-center">
                                        Add Product
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
    // === Form Validation ===
        $('#form').validate({
            rules: {
                name: { required: true },
                price: { required: true},
                stock_quantity: { required: true},
                category_id: { required: true},
            },
            messages: {
                name: { required: "Product Name is Required" },
                price: { required: "Price is Required" },
                stock_quantity: { required: "Stock is Required" },
                category_id: { required: "Category is Required" },
            },
        });
});
</script>
@endsection
