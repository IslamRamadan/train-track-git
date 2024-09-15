@extends('dashboard.layouts.app')
@section('title')
    {{__('translate.Coaches')}}
@endsection
@section('content')
    @include('dashboard.layouts.message')
    <div id="currentRouteName" data-route-name="{{ Route::currentRouteName() }}"></div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body table-responsive">
                    <table class="table table-responsive table-bordered data-table table-scrollable">
                        <thead>
                        <tr>
                            <th>{{__('translate.Num')}}</th>
                            <th>{{__('translate.Name')}}</th>
                            <th>{{__('translate.Email')}}</th>
                            <th>{{__('translate.Phone')}}</th>
                            <th>{{__('translate.ActiveClients')}}</th>
                            <th>{{__('translate.ProgramsNumber')}}</th>
                            <th>{{__('translate.Package')}}</th>
                            <th>{{__('translate.CreationDate')}}</th>
                            <th>{{__('translate.DueDate')}}</th>
                            <th style="width: 900%">{{__('translate.Action')}}</th>
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
        });

    </script>
    <script type="text/javascript">
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
                    {data: 'active_clients', name: 'active_clients'},
                    {data: 'programs_number', name: 'programs_number'},
                    {data: 'package_name', name: 'package_name'},
                    {data: 'creation_date', name: 'creation_date'},
                    {data: 'due_date_tab', name: 'due_date_tab'},
                    {data: 'action', name: 'action', orderable: false, searchable: false, class: "action-buttons"},
                ]
            });

        });

    </script>

@endsection
