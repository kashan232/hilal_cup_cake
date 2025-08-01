    @include('admin_panel.include.header_include')
    <div class="main-wrapper">
        @include('admin_panel.include.navbar_include')
        @include('admin_panel.include.admin_sidebar_include')

        <div class="page-wrapper">
            <div class="content">
                <div class="page-header">
                    <div class="page-title">
                        <h4>
                            @if(Auth::user()->usertype === 'admin')
                            Staff Management List
                            @elseif(Auth::user()->usertype === 'orderbooker')
                            Manage Salesmen
                            @elseif(Auth::user()->usertype === 'saleman')
                            Order Bookers
                            @endif
                        </h4>

                        <h6>
                            @if(Auth::user()->usertype === 'admin')
                            Manage Staff Management
                            @elseif(Auth::user()->usertype === 'orderbooker')
                            Area-wise assigned salesmen
                            @elseif(Auth::user()->usertype === 'saleman')
                            Area-wise assigned order bookers
                            @endif
                        </h6>
                    </div>

                    <div class="page-btn">
                        @if(Auth::user()->usertype === 'admin')
                        <button class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addSalesmanModal">
                            <img src="assets/img/icons/plus.svg" class="me-1" alt="img">Add Staff
                        </button>
                        @else
                        <button class="btn btn-danger" disabled>
                            No Right
                        </button>
                        @endif
                    </div>

                </div>

                <div class="card">
                    <div class="card-body">
                        @if (session()->has('success'))
                        <div class="alert alert-success">
                            <strong>Success!</strong> {{ session('success') }}.
                        </div>
                        @endif
                        <div class="table-responsive">
                            <table class="table datanew">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Designation</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>City</th>
                                        <th>Area</th>
                                        <th>Address</th>
                                        <th>Salary</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($salesmen as $key => $salesman)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $salesman->designation }}</td>
                                        <td>{{ $salesman->name }}</td>
                                        <td>{{ $salesman->phone }}</td>
                                        <td>{{ $salesman->city }}</td>
                                        <td>
                                            @php
                                            $areas = json_decode($salesman->area, true);
                                            @endphp

                                            @if(is_array($areas) && count($areas))
                                            @foreach($areas as $area)
                                            <span class="badge bg-primary">{{ $area }}</span>
                                            @endforeach
                                            @else
                                            <span class="text-muted">N/A</span>
                                            @endif
                                        </td>


                                        <td>{{ $salesman->address }}</td>
                                        <td> @if(Auth::user()->usertype === 'admin')
                                            {{ number_format($salesman->salary, 2) }}
                                            @else
                                            ---
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm toggle-status 
        {{ $salesman->status == 1 ? 'btn-success' : 'btn-danger' }}"
                                                data-id="{{ $salesman->id }}"
                                                data-status="{{ $salesman->status }}">
                                                {{ $salesman->status == 1 ? 'Active' : 'Inactive' }}
                                            </button>
                                        </td>

                                        <td>
                                            @if(Auth::user()->usertype === 'admin')
                                            <button class="btn btn-sm btn-primary editSalesmanBtn"
                                                data-id="{{ $salesman->id }}"
                                                data-name="{{ $salesman->name }}"
                                                data-phone="{{ $salesman->phone }}"
                                                data-city="{{ $salesman->city }}"
                                                data-area="{{ $salesman->area }}"
                                                data-address="{{ $salesman->address }}"
                                                data-salary="{{ $salesman->salary }}"
                                                data-status="{{ $salesman->status }}"
                                                data-bs-toggle="modal" data-bs-target="#editSalesmanModal">
                                                Edit
                                            </button>
                                            @else
                                            <button class="btn btn-sm btn-danger">
                                                No Right
                                            </button>
                                            @endif
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
    <!-- Add Salesman Modal -->
    <!-- Add Salesman Modal -->
    <div class="modal fade" id="addSalesmanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg"> <!-- Increased modal size -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Staff </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('store-salesman') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <!-- Name -->
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>

                            <!-- Phone -->
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" name="phone" required>
                            </div>
                        </div>

                        <div class="row">
                            <!-- City -->
                            <div class="col-md-6 mb-3">
                                <label for="citySelect" class="form-label">City</label>
                                <select class="form-control" name="city" id="citySelect" required>
                                    <option value="">Select City</option>
                                    @foreach($city as $city)
                                    <option value="{{ $city->city_name }}">{{ $city->city_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Area -->
                            <!-- Area as Checkboxes -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Areas</label>
                                <div id="areaCheckboxes" class="form-control" style="height:auto; max-height: 150px; overflow-y:auto;">
                                    <small class="text-muted">Please select a city first</small>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <!-- Address -->
                            <div class="col-md-6 mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" name="address" required>
                            </div>

                            <!-- Salary -->
                            <div class="col-md-6 mb-3">
                                <label for="salary" class="form-label">Salary</label>
                                <input type="number" class="form-control" name="salary" required>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Designation -->
                            <div class="col-md-6 mb-3">
                                <label for="designationSelect" class="form-label">Designation</label>
                                <select class="form-control" name="designation" id="designationSelect" required>
                                    <option value="orderbooker">Order Booker</option>
                                    <option value="saleman">Saleman</option>
                                    <option value="accountant">Accountant</option>
                                </select>
                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" name="status" required>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <!-- Designation -->
                            <div class="col-md-6 mb-3">
                                <label for="designationSelect" class="form-label">Email</label>
                                <input type="text" name="email" class="form-control" placeholder="Email Here">
                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Password Here">
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Edit Salesman Modal -->
    <div class="modal fade" id="editSalesmanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form action="{{ route('update-salesman') }}" method="POST">
                    @csrf
                    <input type="hidden" name="salesman_id" value="{{ $salesman->id ?? 'N/A' }}">
                    <input type="hidden" id="edit_salesman_id" name="salesman_id">

                    <div class="modal-body">
                        <label>Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>

                        <label>Phone</label>
                        <input type="text" class="form-control" id="edit_phone" name="phone" required>

                        <label>City</label>
                        <input type="text" class="form-control" id="edit_city" name="city" readonly>

                        <label>Area</label>
                        <div id="editAreaCheckboxes" class="form-group">
                            <small class="text-muted">Please select area(s)</small>
                        </div>

                        <label>Address</label>
                        <input type="text" class="form-control" id="edit_address" name="address" required>

                        <label>Salary</label>
                        <input type="number" class="form-control" id="edit_salary" name="salary" required>


                        <label>Designation</label>
                        <select class="form-control" name="designation" id="edit_designation" required>
                            <option value="orderbooker">Order Booker</option>
                            <option value="saleman">Saleman</option>
                            <option value="accountant">Accountant</option>
                        </select>


                        <label>Status</label>
                        <select class="form-control" id="edit_status" name="status" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('admin_panel.include.footer_include')

    <script>
        $(document).ready(function() {
            // Add Product Modal: Fetch areas on Category Change
            $('#citySelect').change(function() {
                var cityId = $(this).val();
                $('#areaCheckboxes').html('<small class="text-muted">Loading areas...</small>');

                if (cityId) {
                    $.ajax({
                        url: "{{ route('fetch-areas') }}",
                        type: "GET",
                        data: {
                            city_id: cityId
                        },
                        success: function(data) {
                            $('#areaCheckboxes').html('');
                            if (data.length > 0) {
                                $.each(data, function(key, area) {
                                    $('#areaCheckboxes').append(`
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="areas[]" value="${area.area_name}" id="area_${area.id}">
                                <label class="form-check-label" for="area_${area.area_name}">${area.area_name}</label>
                            </div>
                        `);
                                });
                            } else {
                                $('#areaCheckboxes').html('<small class="text-danger">No areas found for selected city.</small>');
                            }
                        },
                        error: function() {
                            $('#areaCheckboxes').html('<small class="text-danger">Error fetching areas.</small>');
                        }
                    });
                } else {
                    $('#areaCheckboxes').html('<small class="text-muted">Please select a city first</small>');
                }
            });
        });

        $(document).on("click", ".editSalesmanBtn", function() {
            $("#edit_salesman_id").val($(this).data("id"));
            $("#edit_name").val($(this).data("name"));
            $("#edit_phone").val($(this).data("phone"));
            $("#edit_city").val($(this).data("city")); // readonly field
            $("#edit_address").val($(this).data("address"));
            $("#edit_salary").val($(this).data("salary"));
            $("#edit_status").val($(this).data("status"));
            $("#edit_designation").val($(this).data("designation")); // added designation

            var selectedCity = $(this).data("city");
            var areaJson = $(this).data("area");

            let selectedAreas = [];
            try {
                selectedAreas = JSON.parse(areaJson);
            } catch (e) {
                console.error("Area JSON decode error", e);
            }

            $("#editAreaCheckboxes").html('<small class="text-muted">Loading areas...</small>');

            $.ajax({
                url: "{{ route('fetch-areas') }}",
                type: "GET",
                data: {
                    city_id: selectedCity
                },
                success: function(data) {
                    $("#editAreaCheckboxes").html('');
                    if (data.length > 0) {
                        $.each(data, function(key, area) {
                            let isChecked = selectedAreas.includes(area.area_name) ? 'checked' : '';
                            $("#editAreaCheckboxes").append(`
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="areas[]" value="${area.area_name}" id="edit_area_${area.id}" ${isChecked}>
                            <label class="form-check-label" for="edit_area_${area.id}">${area.area_name}</label>
                        </div>
                    `);
                        });
                    } else {
                        $("#editAreaCheckboxes").html('<small class="text-danger">No areas found for this city.</small>');
                    }
                },
                error: function() {
                    $("#editAreaCheckboxes").html('<small class="text-danger">Error loading areas.</small>');
                }
            });
        });


        $(".toggle-status").click(function() {
            var button = $(this);
            var salesmanId = button.data("id");
            var currentStatus = button.data("status");

            $.ajax({
                url: "{{ route('toggle-salesman-status') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    salesman_id: salesmanId,
                    status: currentStatus == 1 ? 0 : 1
                },
                success: function(response) {
                    if (response.success) {
                        let newStatus = currentStatus == 1 ? 0 : 1;
                        button.data("status", newStatus);
                        button.text(newStatus == 1 ? "Active" : "Inactive");

                        if (newStatus == 1) {
                            button.removeClass("btn-danger").addClass("btn-success");
                        } else {
                            button.removeClass("btn-success").addClass("btn-danger");
                        }
                    }
                }
            });
        });



        // When editing, fetch areas based on selected city
        function fetchAreas(cityId, selectedAreaId) {
            if (cityId) {
                $.ajax({
                    url: '/get-areas/' + cityId,
                    type: 'GET',
                    success: function(response) {
                        $('#edit_area').html('<option value="">Select Area</option>');
                        $.each(response, function(key, area) {
                            var selected = area.id == selectedAreaId ? 'selected' : '';
                            $('#edit_area').append('<option value="' + area.id + '" ' + selected + '>' + area.area_name + '</option>');
                        });
                    }
                });
            }
        }
    </script>