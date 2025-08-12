@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4>Bills Assign Management</h4>
                    <h6>Assign bills to Booker or Salesman efficiently</h6>
                </div>
            </div>

            <div class="card p-4">
                <div class="card-body">
                    <form action="{{ route('assign.bills') }}" method="POST">
                        @if (session()->has('success'))
                        <div class="alert alert-success">
                            <strong>Success!</strong> {{ session('success') }}.
                        </div>
                        @endif
                        @csrf
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label for="assign_to" class="form-label">Assign To</label>
                                @if(Auth::check() && Auth::user()->usertype == 'admin')
                                <select class="form-select" id="assign_to" name="assign_to">
                                    <option value="" disabled selected>-- Select Role --</option>
                                    <option value="booker">Booker</option>
                                    <option value="salesman">Salesman</option>
                                </select>
                                @elseif(Auth::check() && Auth::user()->usertype == 'orderbooker')
                                <select class="form-select" id="assign_to" name="assign_to">
                                    <option value="" disabled selected>-- Select Role --</option>
                                    <option value="salesman">Salesman</option>
                                </select>
                                @endif

                            </div>

                            <div class="col-md-3">
                                <label for="user_id" class="form-label">Select User</label>
                                <select class="form-select" id="user_id" name="user_id">
                                    <option value="">-- Select User --</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="user_id" class="form-label">Asigned Date </label>
                                <input type="date" name="asigned_date" class="form-control" required>
                            </div>

                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Assign Selected Bills</button>
                            </div>
                        </div>

                        <div class="table-responsive mt-3">
                            <table class="table table-bordered table-striped">
                                <thead class="text-white">
                                    <tr>
                                        <th><input type="checkbox" id="select_all"></th>
                                        <th>Invoice No</th>
                                        <th>Customer</th>
                                        <th>Bill Date</th>
                                        <th>Bill Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin_panel.include.footer_include')

<script>
    // Assign To change → load users
    $('#assign_to').on('change', function() {
        let role = $(this).val();

        $('#user_id').html('<option>Loading...</option>');

        $.ajax({
            url: '{{ route("fetch-users-by-role") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                role: role
            },
            success: function(data) {
                let options = '<option value="">-- Select User --</option>';
                data.forEach(function(user) {
                    options += `<option value="${user.id}">${user.name}</option>`;
                });
                $('#user_id').html(options);
            }
        });
    });

    // When Select User changes → load bills
    $('#user_id').on('change', function() {
        let role = $('#assign_to').val();
        let userId = $(this).val();

        if (!role || !userId) return;

        $.ajax({
            url: '{{ route("fetch-unassigned-bills") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                role: role,
                user_id: userId
            },
            success: function(bills) {
                let rows = '';
                bills.forEach(function(bill) {
                    rows += `
                    <tr>
                        <td><input type="checkbox" name="bill_ids[]" value="${bill.id}"></td>
                        <td>${bill.invoice_number}</td>
                        <td>${bill.customer?.shop_name ?? 'N/A'}</td>
                        <td>${bill.date}</td>
                        <td>Rs. ${parseFloat(bill.amount).toLocaleString()}</td>
                    </tr>`;
                });
                $('tbody').html(rows);
            }
        });
    });

    // Select All Checkbox Logic
    document.getElementById('select_all').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('input[name="bill_ids[]"]');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Form submit validation
    $('form').on('submit', function(e) {
        let checkedBills = $('input[name="bill_ids[]"]:checked').length;

        if (checkedBills === 0) {
            e.preventDefault(); // stop the form from submitting

            Swal.fire({
                icon: 'warning',
                title: 'Kindly check the bills',
                text: 'Please select at least one bill before assigning.',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });

            return false;
        }
    });
</script>