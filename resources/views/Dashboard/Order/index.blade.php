@extends('Dashboard.app')

@section('title', 'Orders')
@section('breadcrumb_title', 'Orders')

@section('stylesheets')
    <link rel="stylesheet" href="{{ asset('assets/js/sweetalert2@11.css') }}">
@endsection

@section('content')
    <div class="card h-100 p-0 radius-12 basic-data-table">
        <div class="card-body p-24">
            <div class="table-responsive scroll-sm">
                <table class="table bordered-table sm-table mb-0" id="dataTable" data-page-length='10'>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th class="text-center">Order Code</th>
                            <th class="text-center">Name</th>
                            <th class="text-center">Email</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-center">Total Price</th>
                            <th class="text-center">Date</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td>{{ $order->id }}</td>
                                <td class="text-center">{{ $order->order_code }}</td>
                                <td class="text-center">{{ $order->user->name ?? 'Deleted Account' }}</td>
                                <td class="text-center">{{ $order->user->email ?? 'Deleted Account' }}</td>
                                <td class="text-center">{{ $order->quantity }}</td>
                                <td class="text-center">{{ $order->total_price . ' EGP' }}</td>
                                <td class="text-center">{{ date('d/m/y', strtotime($order->created_at)) }}</td>
                                <td class="text-center">
                                    <span class="tag
                                        @if($order->status == 'delivered') tag-success
                                        @elseif($order->status == 'pending' || $order->status == 'shipped') tag-primary
                                        @elseif($order->status == 'cancelled') tag-danger
                                        @endif">
                                        {{ $order->status }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center gap-10 justify-content-center">
                                        <a href="{{ route('edit-order', $order->id) }}" class="bg-success-100 text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle">
                                            <iconify-icon icon="lucide:eye" class="menu-icon"></iconify-icon>
                                        </a>
                                        @if(Auth::user()->role == 'admin')
                                            <button type="button" class="remove-item-button bg-danger-focus bg-hover-danger-200 text-danger-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle delete-btn" data-id="{{ $order->id }}" data-route="{{ route('delete-order', $order->id) }}">
                                                <iconify-icon icon="fluent:delete-24-regular" class="menu-icon"></iconify-icon>
                                            </button>
                                        @endif
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
    <script src="{{ asset('assets/js/sweetalert2@11.js') }}"></script>
    <script>
        let table = new DataTable("#dataTable");

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
