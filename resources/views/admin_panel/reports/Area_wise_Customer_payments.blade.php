@include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <style>
        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin-top: 20px;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #000 !important;
            padding: 6px;
            text-align: center;
        }

        .section-title {
            font-weight: bold;
            background: #f0f0f0;
            padding: 6px 10px;
            margin-top: 20px;
        }

        .summary-row {
            font-weight: bold;
            background-color: #e9ecef;
        }
    </style>

    <div class="page-wrapper">
        <div class="content">
            <div class="card p-4 shadow-lg">
                <div class="card-body">
                    <h3 class="text-center fw-bold text-primary">RECEIVABLE REPORT</h3>

                    <form id="ledgerSearchForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Select City</label>
                                <select class="form-control" name="city" id="citySelect">
                                    <option value="">Select City</option>
                                    @foreach($cities as $city)
                                    <option value="{{ $city->city_name }}">{{ $city->city_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-9" id="areaCheckboxes">
                                <label class="form-label d-block">Select Areas</label>
                                <div class="row" id="areasContainer">
                                    <!-- Dynamic Area Checkboxes -->
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Start Date</label>
                                <input type="date" id="start_date" name="start_date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Date</label>
                                <input type="date" id="end_date" name="end_date" class="form-control">
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="button" id="searchLedger" class="btn btn-primary btn-lg px-5">Search</button>
                        </div>
                    </form>

                    <div class="text-end mt-3">
                        <button id="downloadPdf" class="btn btn-danger">Download PDF</button>
                    </div>

                    <hr>
                    <div id="reportResults"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<script>
    $(document).ready(function() {
        $('#citySelect').change(function() {
            let city = $(this).val();
            $('#areasContainer').html('<p class="text-muted">Loading areas...</p>');

            if (city) {
                $.ajax({
                    url: "{{ route('fetch-areas') }}",
                    method: "GET",
                    data: {
                        city_id: city
                    },
                    success: function(data) {
                        $('#areasContainer').html('');
                        if (data.length > 0) {
                            $.each(data, function(key, area) {
                                $('#areasContainer').append(`
                                    <div class="col-md-2">
                                        <div class="form-check">
                                            <input class="form-check-input area-checkbox" type="checkbox" name="area[]" value="${area.area_name}" id="area_${key}">
                                            <label class="form-check-label" for="area_${key}">${area.area_name}</label>
                                        </div>
                                    </div>
                                `);
                            });
                        } else {
                            $('#areasContainer').html('<p class="text-danger">No areas found.</p>');
                        }
                    },
                    error: function() {
                        $('#areasContainer').html('<p class="text-danger">Error fetching areas.</p>');
                    }
                });
            } else {
                $('#areasContainer').html('<p class="text-danger">Please select a city.</p>');
            }
        });

        $('#searchLedger').click(function() {
            let city = $('#citySelect').val();
            let area = [];
            $('.area-checkbox:checked').each(function() {
                area.push($(this).val());
            });
            let startDate = $('#start_date').val();
            let endDate = $('#end_date').val();

            if (!city || area.length === 0 || !startDate || !endDate) {
                alert('Please fill all fields!');
                return;
            }

            $.ajax({
                url: "{{ route('fetch.receivable.report') }}",
                method: "GET",
                data: {
                    city: city,
                    area: area,
                    start_date: startDate,
                    end_date: endDate
                },
                success: function(response) {
                    $('#reportResults').html('');

                    let groupedByArea = {};
                    response.data.forEach(entry => {
                        if (!groupedByArea[entry.address]) {
                            groupedByArea[entry.address] = [];
                        }
                        groupedByArea[entry.address].push(entry);
                    });

                    for (let area in groupedByArea) {
                        let customers = groupedByArea[area];
                        let totalBalance = 0;
                        let html = `
                            <div class="section-title">${area.toUpperCase()}</div>
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>PCode</th>
                                        <th>Customer Name</th>
                                        <th>Address</th>
                                        <th>Contact</th>
                                        <th>Balance</th>
                                        <th>Cash Rec</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;

                        customers.forEach(c => {
                            totalBalance += parseFloat(c.balance);
                            html += `
                                <tr>
                                    <td>${c.pcode}</td>
                                    <td>${c.customer_name}</td>
                                    <td>${c.address}</td>
                                    <td>${c.contact}</td>
                                    <td>${parseFloat(c.balance).toLocaleString()}</td>
                                    <td>${c.cash_rec ? parseFloat(c.cash_rec).toLocaleString() : ''}</td>
                                    <td>${c.remarks ?? ''}</td>
                                </tr>
                            `;
                        });

                        html += `
                            <tr class="summary-row">
                                <td colspan="4">Total Count: ${customers.length}</td>
                                <td>${totalBalance.toLocaleString()}</td>
                                <td colspan="2"></td>
                            </tr>
                            </tbody></table>
                        `;

                        $('#reportResults').append(html);
                    }
                },
                error: function() {
                    alert('Failed to load data');
                }
            });
        });
    });
</script>
<script>
    document.getElementById("downloadPdf").addEventListener("click", function() {
        const element = document.querySelector(".ledger-container");

        html2canvas(element).then(canvas => {
            const imgData = canvas.toDataURL("image/png");
            const pdf = new jspdf.jsPDF("p", "mm", "a4");

            const imgProps = pdf.getImageProperties(imgData);
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

            pdf.addImage(imgData, "PNG", 0, 0, pdfWidth, pdfHeight);
            pdf.save("Vendor_ledger .pdf");
        });
    });

    // Show PDF button only when result appears
    $('#searchLedger').click(function() {
        setTimeout(() => {
            $('#downloadPdf').removeClass('d-none');
        }, 500);
    });
</script>