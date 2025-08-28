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
                            <div class="col-md-2">
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

                            <div class="col-md-2">
                                <label class="form-label">Usertype</label>
                                <select name="usertype" class="form-select">
                                    <option value="">-- Select Usertype --</option>
                                    <option value="admin" {{ request('usertype') == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="orderbooker" {{ request('usertype') == 'orderbooker' ? 'selected' : '' }}>orderbooker</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">-- Select Status --</option>
                                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
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
                    @if(auth()->user()->usertype == 'admin')
                    <button type="button" class="btn btn-success mt-2 mb-3" id="bulkConfirmBtn">
                        Confirm Selected Payments
                    </button>
                    @endif



                    <div class="table-responsive">
                        @if($totalAmount > 0)
                        <div class="alert alert-danger fs-4">
                            <strong>Total Recovery:</strong> Rs. {{ number_format($totalAmount, 0) }}
                        </div>
                        @endif
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll">
                                    </th>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Usertype</th>
                                    <th>Shopname</th>
                                    <th>OrderBooker</th>
                                    <th>Amount Paid</th>
                                    <th>Remarks</th>
                                    <th>Difference</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($Recoveries as $key => $recovery)
                                <tr id="recovery-row-{{ $recovery->id }}">
                                    <td>
                                        @if(auth()->user()->usertype == 'admin' && $recovery->usertype == 'orderbooker' && $recovery->status != 'paid')
                                        <input type="checkbox" class="selectRecovery" value="{{ $recovery->id }}">
                                        @endif
                                    </td>
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
                                        @if(empty($recovery->status))
                                        <span class="badge bg-danger">Unpaid</span>
                                        @elseif($recovery->status == 'paid')
                                        <span class="badge bg-success">Paid</span>
                                        @else
                                        <span class="badge bg-secondary">{{ ucfirst($recovery->status) }}</span>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {

        // Select All functionality
        document.getElementById("selectAll").addEventListener("change", function() {
            document.querySelectorAll(".selectRecovery").forEach(cb => cb.checked = this.checked);
        });

        // Bulk Confirm
        document.getElementById("bulkConfirmBtn").addEventListener("click", function() {
            let selected = [];
            document.querySelectorAll(".selectRecovery:checked").forEach(cb => {
                selected.push(cb.value);
            });

            if (selected.length === 0) {
                Swal.fire("Warning!", "Please select at least one recovery.", "warning");
                return;
            }

            Swal.fire({
                title: "Are you sure?",
                text: "You want to mark selected recoveries as Paid?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#28a745",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, confirm!"
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("{{ route('customer_recovery.bulkConfirm') }}", {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                "Content-Type": "application/json"
                            },
                            body: JSON.stringify({
                                ids: selected
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire("Confirmed!", data.message, "success");

                                // Update rows to show Paid
                                selected.forEach(id => {
                                    let row = document.querySelector(`#recovery-row-${id}`);
                                    if (row) {
                                        row.classList.add("table-success");
                                        row.querySelector("td:nth-child(9)").innerHTML = '<span class="badge bg-success">Paid</span>';
                                    }
                                });
                            }
                        });
                }
            });
        });

    });
</script>