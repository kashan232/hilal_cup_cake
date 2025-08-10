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

                        {{-- SECTION: Order Booker & Salesman --}}
                        <div class="row g-3">
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
                        </div>

                        <hr>

                        {{-- SECTION: Multiple Bills --}}
                        <div id="bill-fields-wrapper">
                            <div class="bill-entry row g-3 border p-3 mb-3 position-relative">
                                {{-- Close button for removing entry --}}
                                <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-bill" aria-label="Remove"></button>

                                <div class="col-md-6">
                                    <label>Invoice Number</label>
                                    <input type="number" name="bills[0][invoice_number]" class="form-control" required>
                                </div>

                                <div class="col-md-6">
                                    <label>Date</label>
                                    <input type="date" name="bills[0][date]" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label>Customer</label>
                                    <select name="bills[0][customer_id]" class="form-control customer-select" required>
                                        <option value="">Select customer</option>
                                        @foreach($Customers as $customer)
                                        <option value="{{ $customer->id }}" data-city="{{ $customer->city }}" data-area="{{ $customer->area }}">
                                            {{ $customer->customer_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label>City</label>
                                    <input type="text" class="form-control city-field" readonly>
                                </div>

                                <div class="col-md-3">
                                    <label>Area</label>
                                    <input type="text" class="form-control area-field" readonly>
                                </div>

                                <div class="col-md-6">
                                    <label>Amount</label>
                                    <input type="number" name="bills[0][amount]" class="form-control" required>
                                </div>

                                <div class="col-12 ledger-info-box" style="display: none;">
                                    <div class="alert alert-info mt-2">
                                        <strong>Previous Balance:</strong> <span class="prev-balance">0</span><br>
                                        <strong>Invoice Amount:</strong> <span class="bill-amount">0</span><br>
                                        <strong>New Closing Balance:</strong> <span class="new-balance">0</span>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="text-end mb-3">
                            <button type="button" id="add-more-bill" class="btn btn-secondary">+ Add More Bill</button>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-file-invoice"></i> Submit All Bills
                            </button>
                        </div>
                    </form>


                </div>
            </div>
        </div>
    </div>
</div>
@include('admin_panel.include.footer_include')
<script>
    let billIndex = 1;
    // Reusable function to bind events per bill-entry
    function bindBillEvents(entry) {
        // Customer change event
        entry.find('.customer-select').on('change', function() {
            const selected = $(this).find('option:selected');
            const city = selected.data('city') || '';
            const area = selected.data('area') || '';
            const customerId = $(this).val();
            const ledgerBox = entry.find('.ledger-info-box');
            const prevBalSpan = ledgerBox.find('.prev-balance');
            const newBalSpan = ledgerBox.find('.new-balance');
            const billAmountSpan = ledgerBox.find('.bill-amount');

            entry.find('.city-field').val(city);
            entry.find('.area-field').val(area);

            if (!customerId) {
                ledgerBox.hide();
                return;
            }

            // Get ledger info via AJAX
            $.ajax({
                url: "{{ route('customer.ledger.info', ':id') }}".replace(':id', customerId),
                method: 'GET',
                success: function(res) {
                    const previous = parseFloat(res.closing_balance || 0);
                    const billAmount = parseFloat(entry.find('input[name$="[amount]"]').val() || 0);

                    prevBalSpan.text(previous.toFixed(2));
                    billAmountSpan.text(billAmount.toFixed(2));
                    newBalSpan.text((previous + billAmount).toFixed(2));
                    ledgerBox.show();
                }
            });
        });

        // Amount input change
        entry.find('input[name$="[amount]"]').on('input', function() {
            const billAmount = parseFloat($(this).val() || 0);
            const ledgerBox = entry.find('.ledger-info-box');
            const previous = parseFloat(ledgerBox.find('.prev-balance').text() || 0);
            ledgerBox.find('.bill-amount').text(billAmount.toFixed(2));
            ledgerBox.find('.new-balance').text((previous + billAmount).toFixed(2));
        });
    }

    // On load: bind first entry
    bindBillEvents($('.bill-entry').first());

    // Add more bill
    $('#add-more-bill').on('click', function() {
        let newEntry = $('.bill-entry').first().clone();

        // Reset values
        newEntry.find('input, select').each(function() {
            let name = $(this).attr('name');
            if (name) {
                name = name.replace(/\d+/, billIndex);
                $(this).attr('name', name);
            }
            if ($(this).is('input')) $(this).val('');
            if ($(this).is('select')) $(this).val('');
        });

        newEntry.find('.city-field, .area-field').val('');
        newEntry.find('.ledger-info-box').hide();
        newEntry.find('.prev-balance, .bill-amount, .new-balance').text('0');

        $('#bill-fields-wrapper').append(newEntry);
        bindBillEvents(newEntry);
        billIndex++;
    });

    $(document).on('click', '.remove-bill', function() {
    // If only one bill-entry remains, don't allow removing
    if ($('.bill-entry').length > 1) {
        $(this).closest('.bill-entry').remove();
    } else {
        alert("You must keep at least one bill entry.");
    }
});
</script>