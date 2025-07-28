@include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')

    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="row">
                <div class="col-12 col-lg-12 col-md-12">
                    <h3>{{ Auth::user()->name }} Order Booker</h3>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')