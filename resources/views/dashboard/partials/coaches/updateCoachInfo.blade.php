<div class="modal fade" id="updateCoachInfo" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="#" method="post"
                  id="updateCoachInfoForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">{{__("translate.UpdateCoachInfo")}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="row">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="phone">{{__('translate.Phone')}}</label>
                                <input type="text" name="phone" class="form-control" id="coachPhone"
                                       placeholder="{{__('translate.Phone')}}"
                                       data-validation="required"
                                >
                            </div>
                            <div class="form-group">
                                <label for="email">{{__('translate.Email')}}</label>
                                <input type="email" name="email" class="form-control" id="coachEmail"
                                       placeholder="{{__('translate.Email')}}"
                                       data-validation="required"
                                >
                            </div>
                            <div class="form-group">
                                <label for="merchant_id">Merchant ID</label>
                                <input type="number" name="merchant_id" class="form-control" id="coachMerchantId"
                                       placeholder="Merchant ID"
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
