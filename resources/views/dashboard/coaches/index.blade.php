@extends('dashboard.layouts.app')
@section('title')
    {{__('translate.Coaches')}}
@endsection
@section('css')
    <style>
        .dropdown-toggle::after {
            display: none !important;
        }
    </style>
    @endsection
@section('content')
    @include('dashboard.layouts.message')
    <div id="currentRouteName" data-route-name="{{ Route::currentRouteName() }}"></div>
    <div class="row">
        <div class="col-12">
            <div class="card">

                <!-- /.card-header -->
                <div class="card-body table-responsive">
                    <div class="mb-4">
                        <form method="POST" action="{{ route('users.excel.export', app()->getLocale()) }}"
                              class="p-3 bg-light rounded shadow-sm">
                            @csrf
                            <div class="row align-items-center">

                                <!-- Checkbox 1 -->
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="export[]"
                                               id="filterActiveClients" value="0">
                                        <label class="form-check-label font-weight-bold" for="filterActiveClients">
                                            Export coaches
                                        </label>
                                    </div>
                                </div>

                                <!-- Checkbox 2 -->
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="export[]"
                                               id="filterPrograms" value="1">
                                        <label class="form-check-label font-weight-bold" for="filterPrograms">
                                            Export clients
                                        </label>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-success btn-block">
                                        <i class="fas fa-file-excel"></i> Export to Excel
                                    </button>
                                </div>

                            </div>
                        </form>
                    </div>


                    <table class="table table-responsive table-bordered data-table table-scrollable">
                        <thead>
                        <tr>
                            <th>{{__('translate.Num')}}</th>
                            <th>{{__('translate.Name')}}</th>
                            <th>{{__('translate.Email')}}</th>
                            <th>{{__('translate.Phone')}}</th>
                            <th>{{__('translate.Verified')}}</th>
                            <th>{{__('translate.ActiveClients')}}</th>
                            <th>{{__('translate.ProgramsNumber')}}</th>
                            <th>{{__('translate.Package')}}</th>
                            <th>{{__('translate.CreationDate')}}</th>
                            <th>{{__('translate.DueDate')}}</th>
                            <th >{{__('translate.Action')}}</th>
                        </tr>
                        </thead>
                        <tbody id="tbody">
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
    </div>
    <!-- Block Coach Modal -->
    @include('dashboard.partials.coaches.blockCoach')
    <!-- Update Due date Modal -->
    @include('dashboard.partials.coaches.updateDueDate')
    <!-- Update Package Modal -->
    @include('dashboard.partials.coaches.updatePackage',['packages'=>$packages])
    <!-- Verify Email Modal -->
    @include('dashboard.partials.coaches.verifyEmail')

@endsection
@section('script')
    <script src="{{ asset('front/js/jquery.form-validator.min.js') }}"></script>
    <script>
        $.validate({
            lang: 'en'
        });
        $(document).ready(function () {
            const tbody = $('#tbody');
            tbody.on('click', '.blockCoach', function () {
                var url = '{{ route("coaches.block", [app()->getLocale(),":id"]) }}';
                url = url.replace(':id', $(this).attr('data-id'));
                $('#blockUser').attr('action', url);
                $('#status').val($(this).attr('data-status'));
            });
            tbody.on('click', '.updateDueDate', function () {
                var url = '{{ route("coach.update.due.date", [app()->getLocale(),":id"]) }}';
                url = url.replace(':id', $(this).attr('data-id'));
                $('#updateDueDateForm').attr('action', url);
            });
            tbody.on('click', '.updatePackage', function () {
                let url = '{{ route("coach.update.package", [app()->getLocale(),":id"]) }}';
                url = url.replace(':id', $(this).attr('data-id'));
                $('#updatePackageForm').attr('action', url);
                $('#packages_select').val($(this).attr('data-package'));
            });
            tbody.on('click', '.verifyEmail', function () {
                var url = '{{ route("coaches.verify", [app()->getLocale(),":id"]) }}';
                url = url.replace(':id', $(this).attr('data-id'));
                $('#verifyEmailForm').attr('action', url);
            });
        });

    </script>
    <script type="text/javascript">
        $(document).ready(function () {

        $(function () {
            url = "{{ route('coaches.index',app()->getLocale()) }}"

            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                scrollX: "1000px",
                scrollCollapse: true,
                ajax: url,
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'name', name: 'name'},
                    {data: 'email', name: 'email'},
                    {data: 'phone', name: 'phone'},
                    {data: 'is_verified', name: 'is_verified'},
                    {data: 'active_clients', name: 'active_clients'},
                    {data: 'programs_number', name: 'programs_number'},
                    {data: 'package_name', name: 'package_name'},
                    {data: 'creation_date', name: 'creation_date'},
                    {data: 'due_date_tab', name: 'due_date_tab'},
                    {data: 'action', name: 'action', orderable: false, searchable: false, class: "action-buttons"},
                ],
                drawCallback: function () {
                    // Re-init dropdowns for Bootstrap 4
                    $('.dropdown-toggle').dropdown();
                }
            });

        });
        });

    </script>

@endsection
