@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')
    <style>
        .blink {
            animation: blink-animation 1s steps(5, start) infinite;
            -webkit-animation: blink-animation 1s steps(5, start) infinite;
        }

        @keyframes blink-animation {
            to {
                visibility: hidden;
            }
        }
    </style>
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

            <!-- Modal -->
            <!-- Modal -->
            <div class="modal fade" id="extendDateModal" tabindex="-1" aria-labelledby="extendDateModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form id="extend-date-form" method="POST" action="{{ route('bills.extendDate') }}">
                        @csrf
                        <input type="hidden" name="bill_id" id="modal_bill_id">

                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Extend Assigned Date</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <label for="current_assigned_date">Current Assigned Date</label>
                                <input type="text" id="current_assigned_date" class="form-control mb-3" readonly>

                                <label for="new_assigned_date">New Assigned Date</label>
                                <input type="date" name="new_assigned_date" id="new_assigned_date" class="form-control" required>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Update Date</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>



            <div class="card p-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('bills') }}" class="mb-3">
                        <div class="row g-2">
                            @if(Auth::check() && Auth::user()->usertype === 'admin')
                            <div class="col-md-3">
                                <label>Select OrderBooker</label>
                                <select name="booker_id" class="form-control">
                                    <option value="">-- Select Order Booker --</option>
                                    @foreach($OrderBookers as $booker)
                                    <option value="{{ $booker->id }}" {{ request('booker_id') == $booker->id ? 'selected' : '' }}>
                                        {{ $booker->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            <div class="col-md-3">
                                <label>Start Date</label>
                                <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label>End Date</label>
                                <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">
                            </div>
                            <div class="col-md-3 mt-4">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('bills') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Usertype</th>
                                    <th>Invoice No | Customer | Date</th>
                                    <th>Amount</th>
                                    <th>Booker / Saleman</th>
                                    <th>Status</th>
                                    <th>Assigned & Date</th>
                                    <th>Bill Aging</th>
                                    <th>Payment Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bills as $index => $bill)
                                @php
                                $status = strtolower($bill->payment_status);
                                $billDate = $bill->date ? \Carbon\Carbon::parse($bill->date) : null; // <-- Changed here
                                    $now=\Carbon\Carbon::now();
                                    $daysPassed=$billDate ? $billDate->diffInDays($now) : null;
                                    $showAlert = $bill->status === 'assigned' && $status !== 'paid' && $daysPassed >= 5;
                                    $rowBg = $showAlert ? 'style=background-color:#ffcccc;' : '';
                                    @endphp
                                    <tr {!! $rowBg !!}>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $bill->usertype }}</td>
                                        <td> {{ $bill->invoice_number }} <br> {{ $bill->customer->shop_name ?? 'N/A' }} <br>{{ \Carbon\Carbon::parse($bill->date)->format('d-m-Y') }}</td>

                                        <td>
                                            Rs. {{ number_format($bill->amount, 2) }}
                                        </td>
                                        <td>{{ $bill->orderBooker->name ?? 'N/A' }} / <br> {{ $bill->salesman->name ?? 'N/A' }}</td>
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

                                        <td>
                                            @if($billDate)
                                            <span class="badge bg-danger text-white p-2">
                                                {{ $daysPassed }} {{ Str::plural('day', $daysPassed) }}
                                            </span>
                                            @else
                                            <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <!-- Payment Status Badge -->
                                        <!-- Payment Status Badge with Remaining Amount -->
                                        <td>
                                            @php
                                            $paymentStatus = $bill->payment_status;

                                            $colorClass = $paymentStatus === 'Paid' ? 'bg-success text-white'
                                            : ($paymentStatus === 'Partially Paid' ? 'bg-warning text-dark' : 'bg-danger text-white');
                                            @endphp

                                            <span class="badge p-2 {{ $colorClass }}">
                                                {{ $paymentStatus }}
                                            </span>

                                            @if($paymentStatus !== 'Paid')
                                            <div class="text-muted small mt-1">
                                                Remaining: Rs. {{ $bill->remaining_amount }}
                                            </div>
                                            @endif

                                            @if($showAlert)
                                            <div class="mt-1 text-danger fw-bold blink">
                                                ⚠ Payment Overdue!
                                            </div>
                                            @endif
                                        </td>


                                        <td>
                                            @if(Auth::user()->usertype === 'admin')
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

                                            <a href="javascript:void(0);"
                                                class="btn btn-sm btn-warning text-white extend-date-btn"
                                                data-id="{{ $bill->id }}"
                                                data-current_date="{{ $bill->asigned_date }}">
                                                Extend Date
                                            </a>

                                            <a href="javascript:void(0);"
                                                class="btn btn-sm btn-secondary text-white unassign-bill-btn"
                                                data-id="{{ $bill->id }}">
                                                Unassign
                                            </a>
                                            @else
                                            <span class="badge bg-secondary">No Action</span>
                                            @endif
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
<!-- jQuery (required for your jQuery code to work) -->

<!-- Bootstrap 5 JS (already in your code) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


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

    $(document).on('click', '.extend-date-btn', function() {
        const billId = $(this).data('id');
        const currentDate = $(this).data('current_date');

        $('#modal_bill_id').val(billId);
        $('#new_assigned_date').val(''); // Clear previous entry if any
        $('#current_assigned_date').val(currentDate); // <-- Show current assigned date

        $('#extendDateModal').modal('show');
    });

    function formatDate(dateStr) {
        const date = new Date(dateStr);
        if (!isNaN(date)) {
            return date.toLocaleDateString('en-GB'); // dd/mm/yyyy
        }
        return dateStr; // fallback
    }

    $('#current_assigned_date').val(formatDate(currentDate));
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).on('click', '.unassign-bill-btn', function() {
        let billId = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This bill will be unassigned!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, unassign it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('bills.unassign') }}", // ✅ Backend route
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        bill_id: billId
                    },
                    success: function(response) {
                        Swal.fire(
                            'Unassigned!',
                            'The bill has been unassigned.',
                            'success'
                        );
                        location.reload(); // Reload table
                    },
                    error: function() {
                        Swal.fire('Error!', 'Something went wrong.', 'error');
                    }
                });
            }
        });
    });
</script>