@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Customer Recoveries</h4>
                    <h6>Track all recoveries from salesmen</h6>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    @if (session()->has('success'))
                    <div class="alert alert-success">
                        <strong>Success!</strong> {{ session('success') }}.
                    </div>
                    @endif
                    <form method="GET" action="{{ route('customer-recovery') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Order Booker</label>
                                <select name="booker_id" class="form-select">
                                    <option value="">-- Select Booker --</option>
                                    @foreach($bookers as $booker)
                                    <option value="{{ $booker->id }}" {{ request('booker_id') == $booker->id ? 'selected' : '' }}>
                                        {{ $booker->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Usertype</label>
                                <select name="usertype" class="form-select">
                                    <option value="">-- Select Usertype --</option>
                                    <option value="admin" {{ request('usertype') == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="booker" {{ request('usertype') == 'booker' ? 'selected' : '' }}>Booker</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">From Date</label>
                                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">To Date</label>
                                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                            </div>

                            <div class="col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('customer-recovery') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </form>




                    <div class="table-responsive">
                        @if($totalAmount > 0)
                        <div class="alert alert-danger fs-4">
                            <strong>Total Recovery:</strong> Rs. {{ number_format($totalAmount, 0) }}
                        </div>
                        @endif
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Usertype</th>
                                    <th>Shopname</th>
                                    <th>OrderBooker</th>
                                    <th>Amount Paid</th>
                                    <th>Remarks</th>
                                    <th>Difference Details</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($Recoveries as $key => $recovery)
                                <tr id="recovery-row-{{ $recovery->id }}">
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $recovery->date }}</td>
                                    <td>{{ $recovery->usertype }}</td>
                                    <td>{{ $recovery->customer->shop_name ?? 'N/A' }}</td>
                                    <td>{{ $recovery->salesmanRelation->name ?? 'N/A' }}</td>
                                    <td class="amount_paid">{{ number_format($recovery->amount_paid, 0) }}</td>
                                    <td class="remarks">{{ $recovery->remarks }}</td>
                                    <td>
                                        @php
                                        $differences = json_decode($recovery->difference_details, true);
                                        @endphp

                                        @if(!empty($differences))
                                        <ul class="mb-0 ps-3">
                                            @foreach($differences as $diff)
                                            <li>
                                                <strong>Bill #{{ $diff['bill_id'] }}:</strong>
                                                Rs. {{ number_format($diff['amount'], 0) }}
                                                ({{ $diff['reason'] ?? 'No reason' }})
                                            </li>
                                            @endforeach
                                        </ul>
                                        @else
                                        <span class="text-muted">N/A</span>
                                        @endif
                                    </td>

                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary text-white" data-bs-toggle="modal" data-bs-target="#editRecoveryModal{{ $recovery->id }}">
                                            Edit
                                        </button>

                                        <!-- Modal -->
                                        <div class="modal fade" id="editRecoveryModal{{ $recovery->id }}" tabindex="-1" aria-labelledby="editRecoveryModalLabel{{ $recovery->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{ route('customer_recovery.update', $recovery->id) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="editRecoveryModalLabel{{ $recovery->id }}">Edit Customer Recovery</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>

                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Customer</label>
                                                                <input type="text" class="form-control" value="{{ $recovery->customer->customer_name ?? 'N/A' }}" readonly>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label">Salesman</label>
                                                                <input type="text" class="form-control" value="{{ $recovery->salesman }}" readonly>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label">Current Amount Paid</label>
                                                                <input type="text" class="form-control" value="{{ number_format($recovery->amount_paid, 0) }}" readonly>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label">Adjustment Type</label>
                                                                <select name="adjust_type" class="form-select" required>
                                                                    <option value="">Select Type</option>
                                                                    <option value="plus">Plus (+)</option>
                                                                    <option value="minus">Minus (-)</option>
                                                                </select>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label">Adjustment Amount</label>
                                                                <input type="number" name="adjust_amount" class="form-control" min="0" step="any" required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label">Date</label>
                                                                <input type="date" name="date" class="form-control" value="{{ $recovery->date }}" required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label">Remarks</label>
                                                                <textarea name="remarks" class="form-control">{{ $recovery->remarks }}</textarea>
                                                            </div>

                                                            <div class="alert alert-danger d-none" id="editRecoveryError{{ $recovery->id }}"></div>
                                                        </div>

                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-primary">Update Recovery</button>
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')