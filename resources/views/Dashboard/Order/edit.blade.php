@extends('Dashboard.app')
@section('title', 'Show Order #' . $order->order_code )
@section('breadcrumbs')
<li>-</li>
<li class="fw-medium"><a href="{{ route('orders') }}" class="d-flex align-items-center gap-1 hover-text-primary">Orders</a></li>
@endsection
@section('breadcrumb_title', 'Show Order #' . $order->order_code )

@section('stylesheets')
@endsection

@section('content')
<div class="card h-100 p-0 radius-12 basic-data-table">
    <div class="card-body p-24">
        <div class="p-20 d-flex flex-wrap justify-content-between gap-3 border-bottom">
            <div>
                <h3 class="text-xl">Order #{{ $order->order_code  }}</h3>
                <p class="mb-1 text-sm">Order Date: {{ $order->created_at }}</p>
            </div>
        </div>
        <div class="py-28 px-20">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <h6 class="text-md">Customer Details:</h6>
                    <table class="text-sm text-secondary-light">
                        <tbody>
                            <tr>
                                <td>Name</td>
                                <td class="ps-8">:{{ $order->user->name }}</td>
                            </tr>
                            <tr>
                                <td>Email</td>
                                <td class="ps-8">: {{ $order->user->email }}</td>
                            </tr>
                            <tr>
                                <td>Order Status</td>
                                <td class="ps-8">: {{ $order->status }}</td>
                            </tr>
                            <tr>
                                <td>Order Code</td>
                                <td class="ps-8">: {{ $order->order_code }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-3">
                <h6 class="text-md">Assignment:</h6>
                <table class="text-sm text-secondary-light">
                    <tbody>
                        <tr>
                            <td>Assigned To</td>
                            <td class="ps-8">
                                :
                                @if($order->assigned_to && isset($employees))
                                    @php
                                        $currentAssignee = $employees->firstWhere('id', $order->assigned_to);
                                    @endphp
                                    {{ $currentAssignee ? $currentAssignee->name : 'Employee #'.$order->assigned_to }}
                                @else
                                    <em>Unassigned</em>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <form method="post" id="form">
            @csrf
                @if(auth()->user()->role === 'admin')
                    @if($order->status != 'cancelled' && $order->status != 'delivered')
                        <div class="mt-3">
                            <label class="form-label"><strong>Assign to employee</strong></label>
                            <div class="d-flex gap-2 align-items-start">
                                <select name="employee_id" id="employee_id" class="form-select" style="min-width:260px">
                                    <option value="">-- Select employee --</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ $order->assigned_to == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->name }} @if($emp->email) ({{ $emp->email }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-primary"
                                        onclick="document.getElementById('action_field').value='assign'">
                                    <i class="fas fa-user-check"></i> Assign
                                </button>
                            </div>
                        </div>
                    @endif
                @endif


                <div class="mt-24">
                    <div class="table-responsive scroll-sm">
                        <table class="table bordered-table text-sm">
                            <thead>
                                <tr>
                                    <th scope="col" class="text-sm">#ID</th>
                                    <th scope="col" class="text-sm">Product</th>
                                    <th scope="col" class="text-sm">Price</th>
                                    <th scope="col" class="text-sm">Quantity</th>
                                    <th scope="col" class="text-end text-sm">Total Price</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach( $order_items as $order_item )
                                <tr>
                                    <td>{{ $order_item->id }}</td>
                                    <td>{{ $order_item->product->name  }}</td>
                                    <td>
                                        @if($order_item->discount >  0)
                                        <span class="text-decoration-line-through"><small>{{ number_format($order_item->old_price) }} EGP</small></span> <span class="text-danger"><strong>{{ number_format($order_item->price) }} EGP </strong></span>
                                        @else
                                        {{ number_format($order_item->price) }} EGP
                                        @endif
                                    </td>
                                    <td>{{ $order_item->quantity }}</td>
                                    <td class="text-end">
                                        @if($order_item->discount > 0)
                                        <span class="text-decoration-line-through"><small>{{ number_format($order_item->total_old_price) }} EGP</small></span> <span class="text-danger"><strong>{{ number_format($order_item->total_price) }} EGP </strong></span>
                                        @else
                                        {{ number_format($order_item->total_price) }} EGP
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex flex-wrap justify-content-between gap-3">
                        <div>
                            <table class="text-sm">
                                <tbody>
                                    <tr>
                                        <td class="pe-64">Subtotal:</td>
                                        <td class="pe-16">
                                            <span class="text-primary-light fw-semibold">{{ number_format( $order->total_old_price) . ' EGP' }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="pe-64">Discount:</td>
                                        <td class="pe-16">
                                            <span class="text-danger fw-semibold">(-{{ number_format( $order->discount ) . ' EGP' }})</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="pe-64 pt-4">
                                            <span class="text-primary-light fw-semibold">Total:</span>
                                        </td>
                                        <td class="pe-16 pt-4">
                                            <span class="text-primary-light fw-semibold">
                                                @if($order->discount != '' && $order->discount > 0 )
                                                <span class="text-decoration-line-through"><small>{{ number_format($order->total_old_price ) }} EGP </small></span> <span class="text-success"><strong>{{ number_format($order->total_price ) }}  EGP </strong></span>
                                                @else
                                                <span class="text-success"><strong>{{ number_format($order->total_price) }} EGP </strong></span>
                                                @endif
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <hr/>

            <div class="card-body p-24">
                <div class="row gy-3">
                    <div class="col-sm-12">
                        <h3 class="text-xl">Order Status & log</h3>
                        <div class="table-responsive">
                        @php
                            $statuses = ['pending', 'shipped', 'delivered'];
                            $logsByStatus = $order_logs->keyBy('status');
                            if ($order->status === 'cancelled') {
                                $statuses[] = 'cancelled';
                            }
                        @endphp

                        <table class="table table-bordered table-striped">
                            <thead class="thead-inverse">
                            <tr scope="col">
                                <th scope="col" class="text-center">Status</th>
                                <th scope="col" class="text-center">Description</th>
                                <th scope="col" class="text-center">Created by</th>
                                <th scope="col" class="text-center">Created At</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($statuses as $status)
                                @if($status === 'cancelled')
                                    <tr class="table-danger">
                                @elseif($status === 'delivered')
                                    <tr class="table-success" >
                                @else
                                <tr scope="col">
                                    @endif
                                    <td class="text-center">{{ $status }}</td>
                                    @if(isset($logsByStatus[$status]))
                                        @php $log = $logsByStatus[$status]; @endphp
                                        <td class="text-center">{{ $log->description }}</td>
                                        <td class="text-center">{{ $log->created_by ? $log->createdBy->name : $log->name}}</td>
                                        <td class="text-center">{{ $log->created_at }}</td>
                                    @else
                                        @if($nextStatus == $status && $order->status != 'cancelled')
                                            <td colspan="3">
                                                <textarea name="description" rows="2" id="description" class="form-control" placeholder="Enter description for {{ $status }}"></textarea>
                                                <input type="hidden" name="status" value="{{ $status }}">
                                            </td>
                                        @else
                                            <td colspan="3" class="text-muted">Pending</td>
                                        @endif
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
                @if($order->status != 'cancelled' && $order->status != 'delivered')
                    <div class="form-group">
                        <label for="cancel_reason"><strong>Cancel Order</strong></label>
                        <textarea name="cancel_description" id="cancel_description" class="form-control" placeholder="Enter reason for cancellation"></textarea>
                        <input type="hidden" name="action" value="" id="action_field">
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-success" onclick="document.getElementById('action_field').value='update'">
                            <i class="fas fa-check-circle"></i> Update Order
                        </button>

                        <button type="button" class="btn btn-danger" id="cancelOrderBtn" onclick="handleCancelOrder()">
                            <i class="fas fa-times-circle"></i> Cancel Order
                        </button>
                    </div>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection

@section('javascripts')
<script type="text/javascript" src="{{ asset('Dashboard/js/jquery.validate.js') }}"></script>
<script>
    function handleCancelOrder() {
        $('#action_field').val('cancel');
        const validator = $('#form').validate();
        const $field = $('#cancel_description');

        if ($field.hasClass('is-invalid') && !$field.val().trim()) {
            $('html, body').animate({ scrollTop: $field.offset().top - 100 }, 300);
            $field.focus();
            return;
        }

        if (validator.element($field)) {
            $('#form').submit();
        }
    }

    $(document).ready(function () {
        $('#form').validate({
            ignore: [],
            rules: {
                cancel_description: {
                    required: function () { return $('#action_field').val() === 'cancel'; }
                },
                employee_id: {
                    required: function () { return $('#action_field').val() === 'assign'; }
                },
                assign_note: {
                    maxlength: 500
                }
            },
            messages: {
                cancel_description: {
                    required: "Please provide a reason for cancellation."
                },
                employee_id: {
                    required: "Please select an employee to assign."
                }
            },
            errorPlacement: function (error, element) {
                error.insertAfter(element);
            },
            highlight: function (element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element) {
                $(element).removeClass('is-invalid');
            }
        });
    });
</script>
@endsection
