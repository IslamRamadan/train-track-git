@extends('dashboard.layouts.app')
@section('title')
    {{__('translate.CoachesPayments')}}
@endsection
@section('content')
    @include('dashboard.layouts.message')
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
                            <th>{{__('translate.Phone')}}</th>
                            <th>{{__('translate.OrderId')}}</th>
                            <th>{{__('translate.Amount')}}</th>
                            <th>{{__('translate.PaymentStatus')}}</th>
                            <th>{{__('translate.OrderDate')}}</th>
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

    <!-- Update Order Status Modal -->
    @include('dashboard.partials.payments.updateOrderStatus')

@endsection
@section('script')
    <script src="{{ asset('front/js/jquery.form-validator.min.js') }}"></script>

    <script type="text/javascript">
        $(function () {
            url = "{{ route('payments.index',app()->getLocale()) }}"

            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: true,
                ajax: url,
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'user_name', name: 'user_name'},
                    {data: 'user_phone', name: 'user_phone'},
                    {data: 'order_id', name: 'order_id'},
                    {data: 'amount', name: 'amount'},
                    {data: 'status_tab', name: 'status_tab'},
                    {data: 'creation_date', name: 'creation_date'},
                    {data: 'action', name: 'action', orderable: false, searchable: false, class: "action-buttons"},

                ]
            });
        });

        $(document).ready(function () {
            const tbody = $('#tbody');

            tbody.on('click', '.updateOrderStatus', function () {
                console.log("gdfd")
                let url = '{{ route("coach.update.order.status", [app()->getLocale(),":id"]) }}';
                url = url.replace(':id', $(this).attr('data-id'));
                $('#updateOrderStatusForm').attr('action', url);
                $('#order_status_select').val($(this).attr('data-status'));
            });
        });

    </script>

@endsection
