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
