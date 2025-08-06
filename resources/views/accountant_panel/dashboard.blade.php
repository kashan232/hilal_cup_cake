@include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            <div class="row mb-4">
                <div class="col-12">
                    <h3 class="fw-bold text-dark">
                        {{ Auth::user()->name }} <small class="text-muted">(Accountant Dashboard)</small>
                    </h3>
                    <hr>
                </div>
            </div>

            <div class="row g-4">
                <!-- Dashboard Card Component -->
                @php
                    $cards = [
                        ['title' => 'Total Order Bookers', 'value' => $totalOrderBookers, 'icon' => 'fa-users', 'color' => 'primary'],
                        ['title' => 'Total Salesmen', 'value' => $totalSalesmen, 'icon' => 'fa-user-tie', 'color' => 'success'],
                        ['title' => 'Total Assigned Bills', 'value' => $totalAssignedBills, 'icon' => 'fa-file-invoice', 'color' => 'info'],
                        ['title' => 'Total Assigned Amount', 'value' => 'Rs. ' . number_format($totalAssignedAmount, 2), 'icon' => 'fa-wallet', 'color' => 'warning'],
                        ['title' => 'Total Paid Amount', 'value' => 'Rs. ' . number_format($totalPaidAmount, 2), 'icon' => 'fa-money-bill-wave', 'color' => 'success'],
                        ['title' => 'Total Due Amount', 'value' => 'Rs. ' . number_format($totalDueAmount, 2), 'icon' => 'fa-exclamation-circle', 'color' => 'danger'],
                    ];
                @endphp

                @foreach($cards as $card)
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="mb-2">
                                <i class="fas {{ $card['icon'] }} fa-2x text-{{ $card['color'] }}"></i>
                            </div>
                            <h6 class="text-uppercase fw-semibold text-muted">{{ $card['title'] }}</h6>
                            <h3 class="fw-bold text-{{ $card['color'] }}">{{ $card['value'] }}</h3>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')
