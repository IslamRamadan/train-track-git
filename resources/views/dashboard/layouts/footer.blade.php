<!-- /.content -->
</div>
<!-- /.content-wrapper -->

<footer class="main-footer">
    <strong>Copyright &copy; 2024 <a target="_blank" href="https://traintrackcoach.com/">Train Track</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
        <b>Version</b> 1.0.0
    </div>
</footer>

<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
</aside>
<!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="{{asset('dashboard/plugins/jquery/jquery.min.js')}}"></script>
<!-- jQuery UI 1.11.4 -->
<script src="{{asset('dashboard/plugins/jquery-ui/jquery-ui.min.js')}}"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
    $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="{{asset('dashboard/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<!-- ChartJS -->
<script src="{{asset('dashboard/plugins/chart.js/Chart.min.js')}}"></script>
<!-- Sparkline -->
<script src="{{asset('dashboard/plugins/sparklines/sparkline.js')}}"></script>
<!-- JQVMap -->
<script src="{{asset('dashboard/plugins/jqvmap/jquery.vmap.min.js')}}"></script>
<script src="{{asset('dashboard/plugins/jqvmap/maps/jquery.vmap.usa.js')}}"></script>
<!-- jQuery Knob Chart -->
<script src="{{asset('dashboard/plugins/jquery-knob/jquery.knob.min.js')}}"></script>
<!-- daterangepicker -->
<script src="{{asset('dashboard/plugins/moment/moment.min.js')}}"></script>
<script src="{{asset('dashboard/plugins/daterangepicker/daterangepicker.js')}}"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="{{asset('dashboard/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js')}}"></script>
<!-- Summernote -->
<script src="{{asset('dashboard/plugins/summernote/summernote-bs4.min.js')}}"></script>
<!-- overlayScrollbars -->
<script src="{{asset('dashboard/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js')}}"></script>
<!-- AdminLTE App -->
<script src="{{asset('dashboard/dist/js/custom.js')}}"></script>
<script src="{{asset('dashboard/dist/js/adminlte.js')}}"></script>
<!-- AdminLTE for demo purposes -->
{{--<script src="{{asset('dashboard/dist/js/demo.js')}}"></script>--}}
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="{{asset('dashboard/dist/js/pages/dashboard.js')}}"></script>
{{--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />--}}
{{--<link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet">--}}
<link href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
{{--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>--}}
{{--<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>--}}
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
{{--<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>--}}
<script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>
@yield('script')
</body>
</html>
