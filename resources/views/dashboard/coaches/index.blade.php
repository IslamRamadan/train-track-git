@extends('dashboard.layouts.app')
@section('title')
    {{__('translate.Coaches')}}
@endsection
@section('content')
    <div id="currentRouteName" data-route-name="{{ Route::currentRouteName() }}"></div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body table-responsive">
                    <table class="table table-bordered data-table">
                        <thead>
                        <tr>
                            <th>{{__('translate.Num')}}</th>
                            <th>{{__('translate.Name')}}</th>
                            <th>{{__('translate.Email')}}</th>
                            <th>{{__('translate.Phone')}}</th>
                            <th>{{__('translate.ActiveClients')}}</th>
                            <th>{{__('translate.Action')}}</th>
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
@endsection
@section('script')
    <script>
        $(document).ready(function () {
            $('#tbody').on('click', '.blockCoach', function () {
                var url = '{{ route("coaches.block", [app()->getLocale(),":id"]) }}';
                url = url.replace(':id', $(this).attr('data-id'));
                $('#blockUser').attr('action', url);
                $('#status').val($(this).attr('data-status'));
            });
        });

    </script>
    <script type="text/javascript">
        $(function () {
            url = "{{ route('coaches.index',app()->getLocale()) }}"

            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: url,
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'name', name: 'name'},
                    {data: 'email', name: 'email'},
                    {data: 'phone', name: 'phone'},
                    {data: 'active_clients', name: 'active_clients'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ]
            });

        });

    </script>

@endsection
