@include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            <!-- Page Heading -->
            <div class="row mb-4">
                <div class="col-12">
                    <h3 class="fw-bold text-dark">
                        {{ Auth::user()->name }} <span class="badge bg-primary fs-6">Salesman</span>
                    </h3>
                </div>
            </div>

            <!-- Dashboard Summary Cards -->
            <div class="row g-4">

                <!-- Assigned Bills -->
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm p-3 h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Total Assigned Bills</h6>
                                <h2 class="text-primary fw-bold">{{ $totalAssignedBills }}</h2>
                            </div>
                            <div class="bg-light rounded-circle p-3">
                                <i class="fa fa-file-invoice-dollar fs-4 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assigned Amount -->
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm p-3 h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Total Assigned Amount</h6>
                                <h2 class="text-success fw-bold">Rs. {{ number_format($totalAssignedAmount) }}</h2>
                            </div>
                            <div class="bg-light rounded-circle p-3">
                                <i class="fa fa-wallet fs-4 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Paid Amount -->
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm p-3 h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Total Paid Amount</h6>
                                <h2 class="text-info fw-bold">Rs. {{ number_format($totalPaidAmount) }}</h2>
                            </div>
                            <div class="bg-light rounded-circle p-3">
                                <i class="fa fa-check-circle fs-4 text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Due Amount -->
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm p-3 h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Total Due Amount</h6>
                                <h2 class="text-danger fw-bold">Rs. {{ number_format($totalDueAmount) }}</h2>
                            </div>
                            <div class="bg-light rounded-circle p-3">
                                <i class="fa fa-exclamation-circle fs-4 text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>

            </div> <!-- /row -->
        </div> <!-- /content -->
    </div> <!-- /page-wrapper -->
</div> <!-- /main-wrapper -->

@include('admin_panel.include.footer_include')
