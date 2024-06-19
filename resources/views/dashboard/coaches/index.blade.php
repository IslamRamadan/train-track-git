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
                    <table class="table table-responsive table-bordered data-table">
                        <thead>
                        <tr>
                            <th>{{__('translate.Num')}}</th>
                            <th>{{__('translate.Name')}}</th>
                            <th>{{__('translate.Email')}}</th>
                            <th>{{__('translate.Phone')}}</th>
                            <th>{{__('translate.ActiveClients')}}</th>
                            <th>{{__('translate.ProgramsNumber')}}</th>
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
    <!-- Modal -->
    <div class="modal fade" id="blockCoach" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="#" method="post"
                      id="blockUser">
                    <div class="modal-header">
                        <h5 class="modal-title" id="myModalLabel">{{__("translate.BlockUnblockCoach")}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" id="status" name="status" value="">
                            <p>{{__('translate.AreYouSure')}}</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        {{ csrf_field() }}
                        <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">{{__('translate.Close')}}</button>
                        <button type="submit" class="btn btn-primary">{{__('translate.Confirm')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="updateDueDate" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="#" method="post"
                      id="updateDueDateForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="myModalLabel">{{__("translate.UpdateDueDate")}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="row">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="DueDate">{{__('translate.DueDate')}}</label>
                                    <input type="date" name="due_date" class="form-control" id="DueDate"
                                           placeholder="{{__('translate.DueDate')}}"
                                           data-validation="required"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        {{ csrf_field() }}
                        <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">{{__('translate.Close')}}</button>
                        <button type="submit" class="btn btn-primary">{{__('translate.Update')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
        });

    </script>
    <script type="text/javascript">
        $(function () {
            url = "{{ route('coaches.index',app()->getLocale()) }}"

            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                "columnDefs": [
                    {"width": "5%", "targets": 0}, // Set 10% width for the first column
                    {"width": "15%", "targets": 1}, // Set 150px width for the second column
                    {"width": "10%", "targets": 2}, // Set 150px width for the second column
                    {"width": "10%", "targets": 3}, // Set 150px width for the second column
                    {"width": "5%", "targets": 4}, // Set 150px width for the second column
                    {"width": "5%", "targets": 5}, // Set 150px width for the second column
                    {"width": "10%", "targets": 6}, // Set 150px width for the second column
                    {"width": "10%", "targets": 7}, // Set 150px width for the second column
                    {"width": "20%", "targets": 8}, // Set 150px width for the second column
                    // ... define widths for other columns
                ],
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
                    {data: 'creation_date', name: 'creation_date'},
                    {data: 'due_date_tab', name: 'due_date_tab'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ]
            });

        });

    </script>

@endsection
