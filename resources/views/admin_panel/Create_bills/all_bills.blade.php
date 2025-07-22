@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4>Bills Management</h4>
                    <h6>Manage Bills Efficiently</h6>
                </div>
            </div>

            <div class="modal fade" id="editBillModal" tabindex="-1" aria-labelledby="editBillModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form id="editBillForm">
                        @csrf
                        <input type="hidden" name="id" id="edit_bill_id">
                        <input type="hidden" name="old_amount" id="old_amount">

                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Bill</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body row">
                                <div class="col-12 mb-2">
                                    <label>Customer</label>
                                    <select class="form-control" name="customer_id" id="edit_customer_id">
                                        @foreach($Customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->customer_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 mb-2">
                                    <label>Order Booker</label>
                                    <select class="form-control" name="order_booker_id" id="edit_order_booker_id">
                                        @foreach($OrderBookers as $ob)
                                        <option value="{{ $ob->id }}">{{ $ob->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 mb-2">
                                    <label>Salesman</label>
                                    <select class="form-control" name="salesman_id" id="edit_salesman_id">
                                        @foreach($Salesmen as $sm)
                                        <option value="{{ $sm->id }}">{{ $sm->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 mb-2">
                                    <label>Invoice Number</label>
                                    <input type="text" class="form-control" name="invoice_number" id="edit_invoice_number">
                                </div>

                                <div class="col-12 mb-2">
                                    <label>Date</label>
                                    <input type="date" class="form-control" name="date" id="edit_date">
                                </div>

                                <div class="col-12 mb-2">
                                    <label>Amount</label>
                                    <input type="number" class="form-control" name="amount" id="edit_amount">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Update Bill</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>


            <div class="card p-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Customer</th>
                                    <th>Invoice No</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>OrderBooker</th>
                                    <th>Salesman</th>
                                    <th>Status</th>
                                    <th>Assigned & Date</th>
                                    <th>Payment Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bills as $index => $bill)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $bill->customer->customer_name ?? 'N/A' }}</td>
                                    <td>{{ $bill->invoice_number }}</td>
                                    <td>{{ \Carbon\Carbon::parse($bill->date)->format('d-m-Y') }}</td>
                                    <td>Rs. {{ number_format($bill->amount, 2) }}</td>
                                    <td>{{ $bill->orderBooker->name ?? 'N/A' }}</td>
                                    <td>{{ $bill->salesman->name ?? 'N/A' }}</td>
                                    <!-- Status Badge -->
                                    <td>
                                        <span class="badge p-2 {{ $bill->status == 'unassigned' ? 'bg-dark' : 'bg-success' }}">
                                            {{ ucfirst($bill->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($bill->assign_type && $bill->assign_user_id)
                                        <span class="badge bg-info">
                                            {{ ucfirst($bill->assign_type) }} - {{ $bill->assignUser->name ?? 'Unknown' }}
                                            <br>
                                        </span>
                                        <br>
                                        <span>{{ \Carbon\Carbon::parse($bill->asigned_date)->format('d M Y') }}</span>
                                        @else
                                        <span class="badge bg-secondary">Not Assigned</span>
                                        @endif
                                    </td>


                                    <!-- Payment Status Badge -->
                                    <td>
                                        <span class="badge p-2 {{ $bill->payment_status == 'unpaid' ? 'bg-success' : 'bg-danger' }}">
                                            {{ ucfirst($bill->payment_status) }}
                                        </span>
                                    </td>

                                    <td>
                                        <a href="javascript:void(0);"
                                            class="btn btn-sm btn-primary text-white edit-bill-btn"
                                            data-id="{{ $bill->id }}"
                                            data-customer_id="{{ $bill->customer_id }}"
                                            data-invoice_number="{{ $bill->invoice_number }}"
                                            data-date="{{ $bill->date }}"
                                            data-amount="{{ $bill->amount }}"
                                            data-order_booker_id="{{ $bill->order_booker_id }}"
                                            data-salesman_id="{{ $bill->salesman_id }}">
                                            Edit
                                        </a>

                                        <a href="javascript:void(0);"
                                            class="btn btn-sm btn-danger text-white delete-bill-btn"
                                            data-id="{{ $bill->id }}">
                                            Delete
                                        </a>

                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@include('admin_panel.include.footer_include')
<script>
    $(document).on('click', '.delete-bill-btn', function() {
        var billId = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This bill will be permanently deleted and customer ledger will be updated!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("delete-bill") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: billId
                    },
                    success: function(response) {
                        Swal.fire('Deleted!', response.message, 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    },
                    error: function() {
                        Swal.fire('Error!', 'Something went wrong!', 'error');
                    }
                });
            }
        });
    });

    $(document).on('click', '.edit-bill-btn', function() {
        $('#edit_bill_id').val($(this).data('id'));
        $('#old_amount').val($(this).data('amount'));
        $('#edit_customer_id').val($(this).data('customer_id'));
        $('#edit_order_booker_id').val($(this).data('order_booker_id'));
        $('#edit_salesman_id').val($(this).data('salesman_id'));
        $('#edit_invoice_number').val($(this).data('invoice_number'));
        $('#edit_date').val($(this).data('date'));
        $('#edit_amount').val($(this).data('amount'));

        $('#editBillModal').modal('show');
    });

    $('#editBillForm').submit(function(e) {
        e.preventDefault();

        $.ajax({
            url: '{{ route("update-bill") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                Swal.fire('Updated!', res.message, 'success');
                $('#editBillModal').modal('hide');
                setTimeout(() => location.reload(), 1000);
            },
            error: function() {
                Swal.fire('Error!', 'Update failed!', 'error');
            }
        });
    });
</script>