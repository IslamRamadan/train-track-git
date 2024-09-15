<div class="modal fade" id="updateOrderStatus" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="#" method="post"
                  id="updateOrderStatusForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">{{__("translate.UpdateOrderStatus")}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="row">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="order_status_select">{{__('translate.Status')}}</label>
                                <select name="order_status" class="form-control" id="order_status_select"
                                        data-validation="required">
                                    <option value="0">{{__('translate.Cancelled')}}</option>
                                    <option value="1">{{__('translate.UnPaid')}}</option>
                                    <option value="2">{{__('translate.Paid')}}</option>
                                </select>
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
