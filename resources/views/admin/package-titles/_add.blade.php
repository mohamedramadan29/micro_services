<div class="modal" id="add_title">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h5 class="modal-title"> اضافة تصنيف جديد  </h5>
                <button aria-label="Close" class="close" data-dismiss="modal"
                        type="button"><span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{url('admin/package-title/add')}}" method="post">
                @csrf
                <div class="modal-body">
                    <label for="">  العنوان </label>
                    <input type="text" name="title" class="form-control" value="">
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-secondary" data-dismiss="modal"
                            type="button">رجوع
                    </button>
                    <button type="submit" class="btn btn-danger">اضافة </button>
                </div>
            </form>
        </div>
    </div>
</div>
