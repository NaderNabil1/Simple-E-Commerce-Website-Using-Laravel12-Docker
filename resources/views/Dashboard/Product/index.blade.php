@extends('Dashboard.app')

@section('title', 'Products')
@section('breadcrumb_title', 'Products')

@section('stylesheets')
@endsection

@section('content')
<div class="card h-100 p-0 radius-12 basic-data-table">
    <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center flex-wrap gap-3 justify-content-between">
        <a href="{{ route('add-product') }}" class="btn btn-primary text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2">
            <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
            Add New Product
        </a>
    </div>
    <div class="card-body p-24">
        <div class="table-responsive scroll-sm">
            <table class="table bordered-table sm-table mb-0" id="dataTable" data-page-length='10'>
                <thead>
                    <tr>
                        <th scope="col"> #</th>
                        <th scope="col" class="text-center">Name</th>
                        <th scope="col" class="text-center">Category</th>
                        <th scope="col" class="text-center">Price</th>
                        <th scope="col" class="text-center">Stock</th>
                        <th scope="col" class="text-center">Status</th>
                        <th scope="col" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td class="text-center">{{ $product->name }}</td>
                        <td class="text-center">{{ $product->category->name ?? '-' }}</td>
                        <td class="text-center">{{ $product->price  }}</td>
                        <td class="text-center">{{ $product->stock_quantity }}</td>
                        <td class="text-center">
                            @if( $product->status == 1 )
                                <span class="tag tag-success"><i class="ti-check"></i> Active</span>
                            @else
                                <span class="tag tag-danger"><i class="ti-close"></i> Inactive</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex align-items-center gap-10 justify-content-center">
                                <a href="{{ route('edit-product', $product->id) }}" class="bg-success-100 text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle">
                                    <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                </a>
                                <button type="button" class="remove-item-button bg-danger-focus bg-hover-danger-200 text-danger-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle delete-btn" data-id="{{ $product->id }}" data-route="{{ route('delete-product', $product->id) }}">
                                    <iconify-icon icon="fluent:delete-24-regular" class="menu-icon"></iconify-icon>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('javascripts')
<script>
    let table = new DataTable("#dataTable");
</script>
<script src="{{ asset('assets/js/sweetalert2@11.js') }}"></script>
<script>
$(document).ready(function() {
    $('.gallery').magnificPopup({
        delegate: 'a',
        type: 'image',
        gallery: {
            enabled: true
        },
        zoom: {
            enabled: true, // Zoom enabled
            duration: 300, // Duration of zoom animation in ms
            easing: 'ease-in-out', // CSS transition easing function
            opener: function(element) {
              return element.find('img');
            }
        }
    });
});
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const deleteButtons = document.querySelectorAll('.delete-btn');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function () {
                const itemId = this.getAttribute('data-id');
                const itemDeleteRoute = this.getAttribute('data-route');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This action cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Create and submit a form
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = itemDeleteRoute;

                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = '{{ csrf_token() }}';

                        const methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'DELETE';

                        form.appendChild(csrfInput);
                        form.appendChild(methodInput);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endsection
