<div class="modal" id="delete_model_{{$project['id']}}">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title"> هل انت متاكد من حذف المشروع     </h6>
                <button aria-label="Close" class="close" data-dismiss="modal"
                        type="button"><span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post"
                  action="{{url('admin/project/delete/'.$project['id'])}}">
                @csrf
                <div class="modal-body">
                    <input class="form-control" type="text" readonly
                           value="{{$project['title']}}">
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-danger" type="submit"> حذف
                    </button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal"
                            type="button">رجوع
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
