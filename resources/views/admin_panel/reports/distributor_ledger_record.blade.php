@include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="card p-4 shadow-lg">
                <div class="card-body">
                    <h3 class="card-title text-center fw-bold mb-4 text-primary">Distributor Ledger</h3>

                    <form id="ledgerSearchForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="fw-bold" for="distributor">Select Distributor</label>
                                <select id="distributor" class="form-control">
                                    <option value="">-- Select Distributor --</option>
                                    @foreach($Distributors as $distributor)
                                    <option value="{{ $distributor->id }}"
                                        data-contact="{{ $distributor->Contact }}"
                                        data-city="{{ $distributor->City }}"
                                        data-area="{{ $distributor->Area }}">
                                        {{ $distributor->Customer }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold">Contact</label>
                                <input type="text" id="contact" class="form-control bg-light" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="fw-bold">City</label>
                                <input type="text" id="city" class="form-control bg-light" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="fw-bold">Area</label>
                                <input type="text" id="area" class="form-control bg-light" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="fw-bold">Start Date</label>
                                <input type="date" id="start_date" name="start_date" class="form-control bg-light">
                            </div>

                            <div class="col-md-6">
                                <label class="fw-bold">End Date</label>
                                <input type="date" id="end_date" name="end_date" class="form-control bg-light">
                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <button type="button" id="searchLedger" class="btn btn-primary btn-lg px-5">
                                Search
                            </button>
                        </div>
                    </form>
                    <div class="text-end mt-2">
                        <button id="downloadPdf" class="btn btn-danger">
                            Download PDF
                        </button>
                    </div>


                </div>
            </div>
            <div id="ledgerResult" style="display: none;">
                <div class="ledger-container mt-4">
                    <div class="ledger-header">DISTRIBUTOR LEDGER</div>
                    <div class="ledger-info">
                        <span><strong>Distributor:</strong> <span id="distributorName"></span></span>
                        <span><strong>Duration:</strong> From <span id="startDate"></span> To <span id="endDate"></span></span>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>INV-No</th>
                                <th>Description</th>
                                <th>Debit</th>
                                <th>Credit</th>
                                <th>Balance</th>
                            </tr>
                            <tr>
                                <td colspan="5" class="opening-balance">Opening Balance:</td>
                                <td id="openingBalance">Rs. 0</td>
                            </tr>
                        </thead>
                        <tbody id="ledgerData"></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3"><strong>Totals:</strong></td>
                                <td id="totalDebit">0</td>
                                <td id="totalCredit">0</td>
                                <td id="closingBalance">0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function formatDate(dateString) {
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-based
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }

    $(document).ready(function() {
        $('#distributor').change(function() {
            var selected = $(this).find(':selected');
            $('#contact').val(selected.data('contact'));
            $('#city').val(selected.data('city'));
            $('#area').val(selected.data('area'));
        });

        $('#searchLedger').click(function() {
            var distributorId = $('#distributor').val();
            var distributorName = $('#distributor option:selected').text();
            let startDate = $('#start_date').val();
            let endDate = $('#end_date').val();
            if (!distributorId) {
                alert('Please select a distributor.');
                return;
            }

            $.ajax({
                url: "{{ route('fetch-distributor-ledger') }}",
                type: "GET",
                data: {
                    distributor_id: distributorId,
                    start_date: startDate,
                    end_date: endDate
                },
                success: function(response) {
                    const startDateObj = new Date(response.startDate);
                    const endDateObj = new Date(response.endDate);
                    // Format dates to 'dd/mm/yyyy'
                    const formattedStartDate = formatDate(response.startDate);
                    const formattedEndDate = formatDate(response.endDate);

                    $('#ledgerResult').show();
                    $('#distributorName').text(distributorName);
                    $('#startDate').text(formattedStartDate || "N/A");
                    $('#endDate').text(formattedEndDate || "N/A");

                    let openingBalance = parseFloat(response.opening_balance);
                    let balance = openingBalance;
                    let totalDebit = 0,
                        totalCredit = 0;
                    let ledgerHTML = "";

                    let allEntries = [];

                    // ✅ Opening Balance Entry
                    ledgerHTML += `
    <tr>
    
        <td>${formatDate(response.startDate)}</td>
        <td>-</td>
        <td class="fw-bold">Opening Balance</td>
        <td>-</td>
        <td>-</td>
        <td class="fw-bold text-primary">Rs. ${balance.toFixed(2)}</td>
    </tr>
`;


                    // Sales Entries
                    response.sales.forEach(entry => {
                        allEntries.push({
                            date: entry.Date,
                            type: 'sale',
                            invoice_number: entry.invoice_number,
                            booker: entry.Booker,
                            amount: parseFloat(entry.net_amount)
                        });
                    });

                    // Recovery Entries
                    response.recoveries.forEach(entry => {
                        allEntries.push({
                            date: entry.date,
                            type: 'recovery',
                            salesman: entry.salesman,
                            remarks: entry.remarks,
                            amount: parseFloat(entry.amount_paid)
                        });
                    });

                    // Sale Return Entries
                    response.sale_returns.forEach(entry => {
                        allEntries.push({
                            date: entry.created_at,
                            type: 'sale_return',
                            invoice_number: entry.invoice_number,
                            amount: parseFloat(entry.total_return_amount)
                        });
                    });

                    // ✅ Sort Entries by Date (Sales pehle, Recovery baad me agar date same ho)
                    allEntries.sort((a, b) => {
                        let dateA = new Date(a.date);
                        let dateB = new Date(b.date);
                        if (dateA - dateB === 0) {
                            return a.type === 'sale' ? -1 : 1; // Sale pehle ayegi, Recovery baad me
                        }
                        return dateA - dateB;
                    });

                    // ✅ Maintain Correct Ledger Balance
                    // Process Entries
                    allEntries.forEach(entry => {
                        if (entry.type === 'sale') {
                            let debit = entry.amount;
                            totalDebit += debit;
                            balance += debit;
                            ledgerHTML += `
            <tr>
                <td>${formatDate(entry.date)}</td>
                <td>${entry.invoice_number}</td>
                <td>To Sale A/c (${entry.booker})</td>
                <td>Rs. ${debit.toFixed(2)}</td>
                <td>-</td>
                <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">Rs. ${balance.toFixed(2)}</td>
            </tr>
        `;
                        } else if (entry.type === 'recovery') {
                            let credit = entry.amount;
                            totalCredit += credit;
                            balance -= credit;
                            ledgerHTML += `
            <tr>
                <td>${formatDate(entry.date)}</td>
                <td>-</td>
                <td>${entry.remarks} </td>
                <td>-</td>
                <td>Rs. ${credit.toFixed(2)}</td>
                <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">Rs. ${balance.toFixed(2)}</td>
            </tr>
        `;
                        } else if (entry.type === 'sale_return') {
                            let credit = entry.amount;
                            totalCredit += credit;
                            balance -= credit;
                            ledgerHTML += `
        <tr>
            <td>${formatDate(entry.date)}</td>
            <td>${entry.invoice_number}</td>
            <td class="text-danger fw-bold">Sale Return</td>
            <td>-</td>
            <td class="text-danger fw-bold">Rs. ${credit.toFixed(2)}</td>
            <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">Rs. ${balance.toFixed(2)}</td>
        </tr>
    `;
                        }
                    });


                    // ✅ Update Totals
                    $('#ledgerData').html(ledgerHTML);
                    $('#openingBalance').text(`Rs. ${openingBalance.toFixed(2)}`);
                    $('#totalDebit').text(`Rs. ${totalDebit.toFixed(2)}`);
                    $('#totalCredit').text(`Rs. ${totalCredit.toFixed(2)}`);
                    $('#closingBalance').text(`Rs. ${balance.toFixed(2)}`);
                }
            });
        });
    });
</script>
<script>
    document.getElementById("downloadPdf").addEventListener("click", function() {
        const element = document.querySelector(".ledger-container");

        const opt = {
            margin: 0,
            filename: 'Distributor-Ledger.pdf',
            image: {
                type: 'jpeg',
                quality: 1
            },
            html2canvas: {
                scale: 10,
                useCORS: true
            },
            jsPDF: {
                unit: 'in',
                format: 'a4',
                orientation: 'portrait'
            }
        };

        html2pdf().set(opt).from(element).save();
    });
</script>

<style>
    .ledger-container {
        border: 2px solid black;
        padding: 10px;
        max-width: 900px;
        margin: 20px auto;
        background: #fff;
    }

    .ledger-header {
        text-align: center;
        font-size: 20px;
        font-weight: bold;
        padding: 10px;
        border-bottom: 2px solid black;
    }

    .ledger-info {
        display: flex;
        justify-content: space-between;
        padding: 10px;
        border-bottom: 2px solid black;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        border: 1px solid black;
        padding: 8px;
        text-align: center;
    }

    thead th {
        background: #f2f2f2;
    }

    .opening-balance {
        text-align: right;
        font-weight: bold;
        padding: 8px;
        border: 1px solid black;
    }
</style>