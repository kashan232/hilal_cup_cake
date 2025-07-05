<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ env('APP_URL') }}public/assets/js/jquery-3.6.0.min.js"></script>
<script src="{{ env('APP_URL') }}public/assets/js/feather.min.js"></script>
<script src="{{ env('APP_URL') }}public/assets/js/jquery.slimscroll.min.js"></script>
<script src="{{ env('APP_URL') }}public/assets/js/jquery.dataTables.min.js"></script>
<script src="{{ env('APP_URL') }}public/assets/js/dataTables.bootstrap4.min.js"></script>
<script src="{{ env('APP_URL') }}public/assets/js/bootstrap.bundle.min.js"></script>
<script src="{{ env('APP_URL') }}public/assets/plugins/apexchart/apexcharts.min.js"></script>
<script src="{{ env('APP_URL') }}public/assets/plugins/apexchart/chart-data.js"></script>
<script src="{{ env('APP_URL') }}public/assets/js/script.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let globalLoader = document.getElementById("global-loader");
        if (globalLoader) {
            globalLoader.style.display = "none";
        }
    });
</script>
</body>
</html>
