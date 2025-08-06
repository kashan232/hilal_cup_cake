@include('admin_panel.include.header_include')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
    body {
        background-color: #f5f7fa;
        font-family: 'Segoe UI', sans-serif;
    }

    .dashboard-card {
        padding: 20px;
        background-color: #fff;
        color: #333;
        border-radius: 12px;
        border-left: 6px solid #007bff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        transition: 0.3s;
    }

    .dashboard-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .dashboard-icon {
        font-size: 36px;
        color: #007bff;
        opacity: 0.9;
    }

    .card-title {
        font-size: 15px;
        margin-bottom: 6px;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .card-number {
        font-size: 26px;
        font-weight: bold;
        color: #333;
    }

    .section-heading {
        font-size: 20px;
        font-weight: 700;
        padding: 10px 20px;
        color: #444;
        background-color: #ffffff;
        border-left: 5px solid #007bff;
        border-radius: 4px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
        margin-bottom: 24px;
    }

    .customer-card {
        background-color: #fff;
        border-left: 6px solid #28a745;
        border-radius: 14px;
        padding: 20px;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
        transition: 0.3s;
    }

    .customer-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .customer-icon {
        font-size: 32px;
        padding: 10px;
        border-radius: 50%;
        background-color: #f1f3f5;
        color: #28a745;
    }

    .customer-title {
        font-size: 14px;
        text-transform: uppercase;
        color: #777;
        margin-bottom: 4px;
    }

    .customer-value {
        font-size: 24px;
        font-weight: 700;
        color: #333;
    }

    /* Color variants for border-lefts and icons */
    .border-primary {
        border-left-color: #007bff;
    }

    .border-success {
        border-left-color: #28a745;
    }

    .border-warning {
        border-left-color: #ffc107;
    }

    .border-danger {
        border-left-color: #dc3545;
    }

    .border-purple {
        border-left-color: #6f42c1;
    }

    .border-info {
        border-left-color: #17a2b8;
    }

    .icon-primary {
        color: #007bff;
    }

    .icon-success {
        color: #28a745;
    }

    .icon-warning {
        color: #ffc107;
    }

    .icon-danger {
        color: #dc3545;
    }

    .icon-purple {
        color: #6f42c1;
    }

    .icon-info {
        color: #17a2b8;
    }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            {{-- Section: Top Stat Cards --}}
            <div class="row">
                <div class="col-12">
                    <div class="section-heading">General Statistics</div>
                </div>
                <!-- Total Cities -->
                <div class="col-md-4">
                    <div class="dashboard-card border-primary">
                        <div>
                            <div class="card-title">Total Cities</div>
                            <div class="card-number">{{ $totalCities }}</div>
                        </div>
                        <div class="dashboard-icon icon-primary">
                            <i class="fas fa-city"></i>
                        </div>
                    </div>
                </div>


                <!-- Total Areas -->
                <div class="col-md-4">
                    <div class="dashboard-card border-success">
                        <div>
                            <div class="card-title">Total Areas</div>
                            <div class="card-number">{{ $totalAreas }}</div>
                        </div>
                        <div class="dashboard-icon icon-success">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                    </div>
                </div>

                <!-- Business Types -->
                <div class="col-md-4">
                    <div class="dashboard-card border-warning">
                        <div>
                            <div class="card-title">Business Types</div>
                            <div class="card-number">{{ $totalBusinessTypes }}</div>
                        </div>
                        <div class="dashboard-icon icon-warning">
                            <i class="fas fa-briefcase"></i>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Section: Staff Summary --}}
            <div class="row mt-4">
                <div class="col-12">
                    <div class="section-heading">Staff Summary</div>
                </div>
                <!-- Order Bookers -->
                <div class="col-md-4">
                    <div class="dashboard-card border-danger">
                        <div>
                            <div class="card-title">Order Bookers</div>
                            <div class="card-number">{{ $totalOrderBookers }}</div>
                        </div>
                        <div class="dashboard-icon icon-danger">
                            <i class="fas fa-user-edit"></i>
                        </div>
                    </div>
                </div>

                <!-- Salesmen -->
                <div class="col-md-4">
                    <div class="dashboard-card border-info">
                        <div>
                            <div class="card-title">Salesmen</div>
                            <div class="card-number">{{ $totalSalesmen }}</div>
                        </div>
                        <div class="dashboard-icon icon-info">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>

                <!-- Accountants -->
                <div class="col-md-4">
                    <div class="dashboard-card border-purple">
                        <div>
                            <div class="card-title">Accountants</div>
                            <div class="card-number">{{ $totalAccountants }}</div>
                        </div>
                        <div class="dashboard-icon icon-purple">
                            <i class="fas fa-calculator"></i>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Section: Customer Summary --}}
            <div class="row mt-4">
                <div class="col-12">
                    <div class="section-heading">Customer Management</div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="customer-card border-start bg-gradient-primary">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="customer-title">Total Customers</div>
                                <div class="customer-value">{{ ($totalCustomers) }}</div>
                            </div>
                            <div class="customer-icon bg-gradient-dark">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="customer-card border-start bg-gradient-success">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="customer-title">Total Ledger Amount</div>
                                <div class="customer-value">PKR {{ $totalLedgerAmount }}</div>
                            </div>
                            <div class="customer-icon bg-gradient-dark">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="customer-card border-start bg-gradient-warning">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="customer-title">Total Recoveries</div>
                                <div class="customer-value">PKR {{ $totalRecoveries }}</div>
                            </div>
                            <div class="customer-icon bg-gradient-dark">
                                <i class="fas fa-hand-holding-usd"></i>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

{{-- Feather Icons --}}
<script>
    feather.replace()
</script>