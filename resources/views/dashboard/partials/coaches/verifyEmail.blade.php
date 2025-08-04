<div class="modal fade" id="verifyEmail" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="#" method="post"
                  id="verifyEmailForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">{{__("translate.Verify")." ".__("translate.Email")}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <p>{{__('translate.AreYouSure')}}</p>
                    </div>
                </div>
                <div class="modal-footer">
                    {{ csrf_field() }}
                    <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">{{__('translate.Close')}}</button>
                    <button type="submit" class="btn btn-primary">{{__('translate.Verify')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>
