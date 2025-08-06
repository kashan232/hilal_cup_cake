@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')
    <style>
        .badge {
            font-size: 13px;
        }

        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4>Customer Payments Management</h4>
                    <h6>Manage Customer Payments Efficiently</h6>
                </div>
            </div>

            <div class="card p-4">
                <div class="card-body">
                    <form action="{{ route('customer.payment.store') }}" method="POST">
                        @csrf
                        @if (session()->has('success'))
                        <div class="alert alert-success">
                            <strong>Success!</strong> {{ session('success') }}.
                        </div>
                        @endif
                        {{-- Customer Dropdown --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Customer</label>
                                <select name="customer_id" id="customer" class="form-control" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">
                                        {{ $customer->customer_name }} - {{ $customer->shop_name }} ({{ $customer->area }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- ordbker Dropdown --}}
                            <div class="col-md-6">
                                <label>Booker</label>
                                <select name="ordbker_id" class="form-control" required>
                                    <option value="">Select Order Booker</option>
                                    @foreach($orderbooker as $ordbker)
                                    <option value="{{ $ordbker->id }}">{{ $ordbker->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <label>Payment Method</label>
                                <select name="payment_method" class="form-control" required>
                                    <option value="">Select Method</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Bank">Bank</option>
                                    <option value="Cheque">Cheque</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label>Payment Date</label>
                                <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                            </div>
                        </div>

                        {{-- Closing Balance Display --}}
                        <div class="alert alert-danger mt-3" id="closing_balance_box" style="display:none;">
                            <strong>Closing Balance:</strong> Rs. <span id="closing_balance">0</span>
                        </div>




                        {{-- Bill Table --}}
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered" id="bill_recovery_table">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="select_all"></th>
                                        <th>Invoice No.</th>
                                        <th>Date</th>
                                        <th>Customer Name</th>
                                        <th>Total Amount</th>
                                        <th>Remaining</th>
                                        <th>Payment Status</th>
                                        <th>Amount Received</th>
                                        <th>Difference Amount</th> <!-- NEW -->
                                        <th>Reason</th> <!-- NEW -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="6" class="text-center">Select a customer to view unpaid bills.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>


                        {{-- Total Selected Amount --}}
                        <div class="alert alert-success mt-3" id="total_box" style="display:none;">
                            <strong>Total Selected Amount:</strong> Rs. <span id="total_amount">0</span>
                        </div>

                        {{-- Submit --}}
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin_panel.include.footer_include')
<script>
    const balanceRoute = "{{ route('get.customer.balance', ['id' => '__id__']) }}";
    const billRoute = "{{ route('get.customer.bills', ['id' => '__id__']) }}";

    function fetchCustomerData(customerId) {
        const route = balanceRoute.replace('__id__', customerId);
        fetch(route)
            .then(res => res.json())
            .then(data => {
                $('#closing_balance').text(data.closing_balance);
                $('#closing_balance_box').show();
            });
    }

    function fetchCustomerBills(customerId) {
        const route = billRoute.replace('__id__', customerId);
        fetch(route)
            .then(res => res.json())
            .then(bills => {
                let tbody = $('#bill_recovery_table tbody');
                tbody.empty();

                if (bills.length === 0) {
                    tbody.html('<tr><td colspan="5" class="text-center">No unpaid or partial bills found.</td></tr>');
                } else {
                    bills.forEach(bill => {
                        let statusClass = '';
                        if (bill.payment_status === 'Unpaid') {
                            statusClass = 'bg-danger';
                        } else if (bill.payment_status === 'Partially Paid') {
                            statusClass = 'bg-warning text-dark';
                        } else if (bill.payment_status === 'Paid') {
                            statusClass = 'bg-success';
                        }

                        tbody.append(`
<tr>
    <td>
        <input type="checkbox" name="bill_ids[]" value="${bill.id}" class="bill_checkbox" data-amount="${bill.amount}">
        <input type="hidden" name="bill_amounts[${bill.id}]" value="${bill.amount}">
    </td>
    <td>${bill.invoice_number}</td>
    <td>${bill.date}</td>
    <td>${bill.customer?.customer_name ?? 'N/A'}</td>
    <td>Rs. ${parseFloat(bill.amount).toLocaleString()}</td>
    <td>Rs. ${parseFloat(bill.remaining_amount ?? bill.amount).toLocaleString()}</td>
    <td><span class="badge ${statusClass} p-2">${bill.payment_status}</span></td>
    <td>
        <input type="number" name="amount_received[${bill.id}]" step="0.01" min="0" class="form-control">
    </td>
    <td>
        <input type="number" name="difference_amount[${bill.id}]" step="0.01" min="0" class="form-control" placeholder="If any">
    </td>
    <td>
        <input type="text" name="difference_reason[${bill.id}]" class="form-control" placeholder="e.g. Sale return" style="width:150px!important;">
    </td>
</tr>
`);
                    });

                }

                $('#total_box').hide();
                $('#total_amount').text(0);
            });
    }

    $(document).on('change', '.bill_checkbox', function() {
        // Optional: you can auto-fill full amount if you want
        const row = $(this).closest('tr');
        const amountInput = row.find('input[name^="amount_received"]');

        if (this.checked && !amountInput.val()) {
            amountInput.val($(this).data('amount')); // Auto-fill default
        }
    });

    $(document).ready(function() {
        $('#customer').change(function() {
            const customerId = $(this).val();
            if (customerId) {
                fetchCustomerData(customerId);
                fetchCustomerBills(customerId);
            }
        });

        // Total Calculation
        $(document).on('change', '.bill_checkbox', function() {
            let total = 0;
            $('.bill_checkbox:checked').each(function() {
                total += parseFloat($(this).data('amount'));
            });

            $('#total_amount').text(total.toLocaleString());
            $('#total_box').toggle(total > 0);
        });

        // Select All
        $(document).on('change', '#select_all', function() {
            $('.bill_checkbox').prop('checked', this.checked).trigger('change');
        });
    });
</script>