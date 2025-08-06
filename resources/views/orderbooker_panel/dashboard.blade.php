@include('admin_panel.include.header_include')

<style>
    body {
        background-color: #f1f3f5;
    }

    .dashboard-title {
        font-weight: 600;
        font-size: 22px;
        margin-bottom: 20px;
        color: #343a40;
    }

    .dashboard-card {
        background: #ffffff;
        border: none;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.06);
        padding: 20px;
        position: relative;
        transition: 0.3s ease-in-out;
        overflow: hidden;
    }

    .dashboard-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
    }

    .dashboard-card h5 {
        font-size: 14px;
        color: #6c757d;
        margin-bottom: 5px;
    }

    .dashboard-card h3 {
        font-size: 26px;
        font-weight: 700;
        color: #212529;
    }

    .dashboard-icon {
        position: absolute;
        top: 20px;
        right: 20px;
        font-size: 32px;
        opacity: 0.2;
    }

    /* Custom Border Colors */
    .border-customers {
        border-left: 4px solid #0d6efd;
    }

    .border-bills {
        border-left: 4px solid #20c997;
    }

    .border-paid {
        border-left: 4px solid #198754;
    }

    .border-unpaid {
        border-left: 4px solid #dc3545;
    }

    .border-total-amount {
        border-left: 4px solid #6610f2;
    }

    .border-paid-amount {
        border-left: 4px solid #17a2b8;
    }

    .border-unpaid-amount {
        border-left: 4px solid #ffc107;
    }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="row mb-4">
                <div class="col-md-12">
                    <h3 class="dashboard-title">{{ Auth::user()->name }} – Order Booker Dashboard</h3>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="dashboard-card border-customers">
                        <h5>Total Customers</h5>
                        <h3>{{ $totalCustomers }}</h3>
                        <i class="fas fa-users dashboard-icon text-primary"></i>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <div class="dashboard-card border-bills">
                        <h5>Total Bills</h5>
                        <h3>{{ $totalBills }}</h3>
                        <i class="fas fa-file-invoice-dollar dashboard-icon text-success"></i>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <div class="dashboard-card border-paid">
                        <h5>Paid Bills</h5>
                        <h3>{{ $paidBills }}</h3>
                        <i class="fas fa-check-circle dashboard-icon text-success"></i>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <div class="dashboard-card border-unpaid">
                        <h5>Unpaid Bills</h5>
                        <h3>{{ $unpaidBills }}</h3>
                        <i class="fas fa-times-circle dashboard-icon text-danger"></i>
                    </div>
                </div>
            </div>

            {{-- Row 2 --}}
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="dashboard-card border-total-amount">
                        <h5>Total Amount</h5>
                        <h3>Rs {{ number_format($totalAmount, 2) }}</h3>
                        <i class="fas fa-coins dashboard-icon text-purple"></i>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="dashboard-card border-paid-amount">
                        <h5>Paid Amount</h5>
                        <h3>Rs {{ number_format($paidAmount, 2) }}</h3>
                        <i class="fas fa-hand-holding-usd dashboard-icon text-info"></i>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="dashboard-card border-unpaid-amount">
                        <h5>Unpaid Amount</h5>
                        <h3>Rs {{ number_format($unpaidAmount, 2) }}</h3>
                        <i class="fas fa-money-bill-wave dashboard-icon text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')