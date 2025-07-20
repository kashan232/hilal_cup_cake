@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4>Create Bill Management</h4>
                    <h6>Manage Create Bill Efficiently</h6>
                </div>
            </div>
            <div class="card p-4">
                <div class="card-body">
                    @if (session()->has('success'))
                    <div class="alert alert-success">
                        <strong>Success!</strong> {{ session('success') }}.
                    </div>
                    @endif

                    <form action="{{ route('create.bill.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label for="invoice_number" class="form-label">Invoice Number (Manual)</label>
                                <input type="text" class="form-control" id="invoice_number" name="invoice_number" placeholder="Enter invoice number" required>
                            </div>

                            <div class="col-md-6">
                                <label for="date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="date" name="date" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label for="customer_id" class="form-label">Customer</label>
                                <select id="customer_id" name="customer_id" class="form-control" required>
                                    <option value="" selected disabled>Choose customer</option>
                                    @foreach($Customers as $customer)
                                    <option
                                        value="{{ $customer->id }}"
                                        data-city="{{ $customer->city }}"
                                        data-area="{{ $customer->area }}">
                                        {{ $customer->customer_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" id="city" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Area</label>
                                <input type="text" class="form-control" id="area" readonly>
                            </div>

                            <div class="col-md-6">
                                <label for="amount" class="form-label">Amount</label>
                                <input type="number" class="form-control" name="amount" id="invoice_amount" placeholder="Enter amount" required>
                            </div>

                            <div class="col-md-6">
                                <label for="order_booker_id" class="form-label">Order-Booker</label>
                                <select class="form-select" id="order_booker_id" name="order_booker_id" required>
                                    <option value="" selected disabled>Select order-booker</option>
                                    @foreach($OrderBookers as $booker)
                                    <option value="{{ $booker->id }}">{{ $booker->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="salesman_id" class="form-label">Salesman</label>
                                <select class="form-select" id="salesman_id" name="salesman_id" required>
                                    <option value="" selected disabled>Select salesman</option>
                                    @foreach($Salesmen as $salesman)
                                    <option value="{{ $salesman->id }}">{{ $salesman->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="closing_balance_box" style="display: none;" class="mt-4">
                                <div class="alert alert-danger">
                                    <strong>Previous Balance:</strong> <span id="previous_balance_text"></span><br>
                                    <strong>Invoice Amount:</strong> <span id="invoice_amount_text"></span><br>
                                    <strong>New Closing Balance:</strong> <span id="new_closing_balance_text"></span>
                                </div>
                            </div>


                            <div class="col-12 text-end pt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-file-invoice"></i> Create Bill
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@include('admin_panel.include.footer_include')
<script>
    let previousBalance = 0;

    $('#customer_id').on('change', function() {
        let customerId = $(this).val();
        if (!customerId) return;

        // Clear city/area/closing values
        $('#city').val('');
        $('#area').val('');
        $('#previous_balance_text').text('');
        $('#invoice_amount_text').text('');
        $('#new_closing_balance_text').text('');
        $('#closing_balance_box').hide();

        $.ajax({
            url: "{{ route('customer.ledger.info', ':id') }}".replace(':id', customerId),
            method: 'GET',
            success: function(res) {
                previousBalance = parseFloat(res.closing_balance) || 0;
                $('#city').val(res.city);
                $('#area').val(res.area);

                $('#previous_balance_text').text(previousBalance);
                $('#invoice_amount_text').text(0);
                $('#new_closing_balance_text').text(previousBalance);
                $('#closing_balance_box').show();
            }
        });
    });

    $('#invoice_amount').on('input', function() {
        let invoiceAmount = parseFloat($(this).val()) || 0;
        $('#invoice_amount_text').text(invoiceAmount);
        let newClosing = previousBalance + invoiceAmount;
        $('#new_closing_balance_text').text(newClosing);
    });
</script>