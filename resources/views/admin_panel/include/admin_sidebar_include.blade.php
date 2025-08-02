<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            @if(Auth::check() && Auth::user()->usertype == 'admin')
            <ul>
                <li class="active">
                    <a href="{{ route('home') }}"><i class="fas fa-home"></i><span> Dashboard</span> </a>
                </li>

                <li>
                    <a href="{{ route('city') }}"><i class="fas fa-city"></i><span> City</span> </a>
                </li>
                <li>
                    <a href="{{ route('Area') }}"><i class="fas fa-building"></i><span> Areas</span> </a>
                </li>


                <!-- <li class="submenu">
                    <a href="javascript:void(0);"><i class="fas fa-users"></i><span> Distributor</span> <span class="menu-arrow"></span></a>
                    <ul>
                        <li><a href="{{ route('Distributor') }}">Distributor</a></li>
                        <li><a href="{{ route('Distributor-ledger') }}">Distributor Ledger </a></li>
                        <li><a href="{{ route('Distributor-recovery') }}">Distributor Recoveries </a></li>
                    </ul>
                </li> -->

                <!-- <li class="submenu">
                    <a href="javascript:void(0);"><i class="fas fa-user-friends"></i><span> Vendors</span> <span class="menu-arrow"></span></a>
                    <ul>
                        <li><a href="{{ route('vendors') }}">Vendors</a></li>
                        <li><a href="{{ route('vendors-ledger') }}">Vendors Ledger </a></li>
                        <li><a href="{{ route('amount-paid-vendors') }}">Vendors Payments </a></li>
                        <li><a href="{{ route('vendors-builty') }}">Vendors Builty </a></li>
                    </ul>
                </li> -->

                <!-- <li>
                    <a href="{{ route('category') }}"><i class="fas fa-box"></i><span> Category</span> </a>
                </li> -->

                <!-- <li>
                    <a href="{{ route('sub-category') }}"><i class="fas fa-boxes"></i><span> Sub-Category</span> </a>
                </li> -->

                <!-- <li>
                    <a href="{{ route('size') }}"><i class="fas fa-wine-bottle"></i> <span> Size </span> </a>
                </li> -->

                <li>
                    <a href="{{ route('business_type') }}"><i class="fas fa-business-time"></i> <span> Business Type </span> </a>
                </li>

                <!-- <li>
                    <a href="{{ route('product') }}"><i class="fas fa-box-open"></i> <span> Product </span> </a>
                </li> -->

                <!-- <li class="submenu">
                    <a href="javascript:void(0);"><i class="fas fa-shopping-basket"></i><span> Purchase</span> <span class="menu-arrow"></span></a>
                    <ul>
                        <li><a href="{{ route('Purchase') }}">Add Purchase</a></li>
                        <li><a href="{{ route('all-Purchases') }}">All Purchase</a></li>
                        <li><a href="{{ route('all-purchase-return') }}"> Purchase Returns</a></li>
                    </ul>
                </li> -->

                <!-- <li class="submenu">
                    <a href="javascript:void(0);"><i class="fas fa-store"></i><span> Distributor Sale</span> <span class="menu-arrow"></span></a>
                    <ul>
                        <li><a href="{{ route('add-sale') }}">Add Sale</a></li>
                        <li><a href="{{ route('all-sale') }}">Sales</a></li>
                    </ul>
                </li> -->

                <!-- <li class="submenu">
                    <a href="javascript:void(0);"><i class="fas fa-shopping-bag"></i><span> Local Sale</span> <span class="menu-arrow"></span></a>
                    <ul>
                        <li><a href="{{ route('local-sale') }}">Add Sale</a></li>
                        <li><a href="{{ route('all-local-sale') }}">Sales</a></li>
                    </ul>
                </li> -->
                <!-- <li class="submenu">
                    <a href="javascript:void(0);"><i class="fas fa-shopping-bag"></i><span> Sale Return</span> <span class="menu-arrow"></span></a>
                    <ul>
                        <li><a href="{{ route('add-sale-return') }}">Add Sale Return</a></li>
                        <li><a href="{{ route('all-sale-return') }}">Sales Return</a></li>
                    </ul>
                </li> -->
                <li class="submenu">
                    <a href="javascript:void(0);"><i class="fas fa-user-tie"></i><span> Staff Management</span> <span class="menu-arrow"></span></a>
                    <ul>
                        <li><a href="{{ route('salesmen') }}">Add Staff</a></li>

                    </ul>
                </li>
                <!-- <li class="submenu">
                    <a href="javascript:void(0);"><i class="fas fa-money-bill-wave"></i><span> Expenses</span> <span class="menu-arrow"></span></a>
                    <ul>
                        <li><a href="{{ route('expense') }}">Add Expense Categroy</a></li>
                        <li><a href="{{ route('add-expenses') }}">Add Expenss</a></li>
                    </ul>
                </li> -->
                <li class="submenu">
                    <a href="javascript:void(0);"><i class="fas fa-address-book"></i><span>Customer Management</span> <span class="menu-arrow"></span></a>
                    <ul>
                        <li><a href="{{ route('customer') }}">Add Cutomers </a></li>
                        <li><a href="{{ route('customer-ledger') }}">Cutomers Payments </a></li>
                        <li><a href="{{ route('customer-recovery') }}">Cutomers Recoveries </a></li>
                    </ul>
                </li>

                <li class="submenu">
                    <a href="javascript:void(0);"><i class="fas fa-address-book"></i><span>Create Bills</span> <span class="menu-arrow"></span></a>
                    <ul>
                        <li><a href="{{ route('create-bill') }}">Create Bill </a></li>
                        <li><a href="{{ route('bills') }}"> Bills </a></li>
                    </ul>
                </li>
                <li class="submenu">
                    <a href="javascript:void(0);"><i class="fas fa-address-book"></i><span> Bills Asigns</span> <span class="menu-arrow"></span></a>
                    <ul>
                        <li><a href="{{ route('bill-asign') }}">Bills Asigns </a></li>
                    </ul>
                </li>
                <li class="submenu">
                    <a href="javascript:void(0);"><i class="fas fa-address-book"></i><span>Payments Management</span> <span class="menu-arrow"></span></a>
                    <ul>
                        <li><a href="{{ route('customer-payments') }}">Customer Payments </a></li>
                    </ul>
                </li>

                <!-- <li class="submenu">
                    <a href="javascript:void(0);"><i class="fas fa-chart-pie"></i><span>Reporting</span> <span class="menu-arrow"></span></a>
                    <ul>
                        <li><a href="{{ route('stock-Record') }}">Item Stock Report </a></li>
                        <li><a href="{{ route('Distributor-Ledger-Record') }}">Distributor Ledger Record </a></li>
                        <li><a href="{{ route('vendor-Ledger-Record') }}">Vendor Ledger Record </a></li>
                        <li><a href="{{ route('Customer-Ledger-Record') }}">Customer Ledger Record </a></li>
                        <li><a href="{{ route('date-wise-recovery-report') }}">Date Wise Recovery Report </a></li>
                        <li><a href="{{ route('date-wise-purcahse-report') }}">Date wise Purchase Report </a></li>
                        <li><a href="{{ route('vendor-wise-purcahse-report') }}">Vendor wise Purchase Report </a></li>
                        <li><a href="{{ route('Area-wise-Customer-payments') }}">Area wise Customer Report </a></li>

                    </ul>
                </li> -->
            </ul>
            @endif

            @if(Auth::check() && Auth::user()->usertype == 'orderbooker')
            @php
            $userId = Auth::user()->user_id;

            $overdueBillsCount = \App\Models\CreateBill::where(function ($query) use ($userId) {
            $query->where('order_booker_id', $userId)
            ->orWhere(function ($q) use ($userId) {
            $q->where('assign_type', 'booker')
            ->where('assign_user_id', $userId);
            });
            })
            ->where('payment_status', '!=', 'paid')
            ->whereDate('asigned_date', '<', \Carbon\Carbon::today()->subDays(5))
                ->count();
                @endphp

                <ul>
                    <li class="active">
                        <a href="{{ route('home') }}"><i class="fas fa-home"></i><span> Dashboard</span></a>
                    </li>

                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <i class="fas fa-user-tie"></i>
                            <span>Salemans</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            <li><a href="{{ route('salesmen') }}">Saleman</a></li>
                        </ul>
                    </li>

                    <li class="submenu">
                        <a href="javascript:void(0);">
                            <i class="fas fa-address-book"></i>
                            <span>Bookers Bills</span>
                            <span class="menu-arrow"></span>
                            @if($overdueBillsCount > 0)
                            <span class="badge bg-danger ms-2 text-white">{{ $overdueBillsCount }}</span>
                            @endif
                        </a>
                        <ul>
                            <li><a href="{{ route('bills') }}"> Bills </a></li>
                        </ul>
                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-address-book"></i><span>Payments Management</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('customer-payments') }}">Customer Payments </a></li>
                        </ul>
                    </li>

                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-address-book"></i><span> Bills Asigns</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('bill-asign') }}">Bills Asigns </a></li>
                        </ul>
                    </li>

                </ul>
                @endif
                @if(Auth::check() && Auth::user()->usertype == 'saleman')
                @php
                $user = Auth::user();
                $userId = $user->user_id;

                $overdueBillsCount = 0;

                if ($user->usertype === 'saleman') {
                $overdueBillsCount = \App\Models\CreateBill::where('assign_type', 'salesman')
                ->where('assign_user_id', $userId)
                ->where('payment_status', '!=', 'paid')
                ->whereDate('asigned_date', '<', \Carbon\Carbon::today()->subDays(5))
                    ->count();
                    }
                    @endphp
                    <ul>
                        <li class="active">
                            <a href="{{ route('home') }}"><i class="fas fa-home"></i><span> Dashboard</span> </a>
                        </li>

                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="fas fa-user-tie"></i>
                                <span>Order Bookers</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="{{ route('salesmen') }}">Order Bookers</a></li>
                            </ul>
                        </li>

                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="fas fa-address-book"></i>
                                <span>Bookers Bills</span>
                                <span class="menu-arrow"></span>
                                @if($overdueBillsCount > 0)
                                <span class="badge bg-danger ms-2 text-white">{{ $overdueBillsCount }}</span>
                                @endif
                            </a>
                            <ul>
                                <li><a href="{{ route('bills') }}"> Bills </a></li>
                            </ul>
                        </li>

                    </ul>
                    @endif
                    @if(Auth::check() && Auth::user()->usertype == 'accountant')
                    <ul>
                        <li class="active">
                            <a href="{{ route('home') }}"><i class="fas fa-home"></i><span> Dashboard</span> </a>
                        </li>

                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="fas fa-user-tie"></i>
                                <span>Bookers & Salemans</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="{{ route('salesmen') }}">Bookers & Salemans</a></li>
                            </ul>
                        </li>

                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="fas fa-address-book"></i>
                                <span> Bills</span>
                            </a>
                            <ul>
                                <li><a href="{{ route('bills') }}"> Bills </a></li>
                            </ul>
                        </li>

                    </ul>
                    @endif
        </div>
    </div>
</div>