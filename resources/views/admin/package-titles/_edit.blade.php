<div class="modal" id="edit_title_{{ $title->id }}">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h5 class="modal-title"> تعديل التصنيف  </h5>
                <button aria-label="Close" class="close" data-dismiss="modal"
                        type="button"><span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{url('admin/package-title/edit/'.$title->id)}}" method="post">
                @csrf
                <div class="modal-body">
                    <label for="">  العنوان </label>
                    <input type="text" name="title" class="form-control" value="{{ $title->title }}">
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-secondary" data-dismiss="modal"
                            type="button">رجوع
                    </button>
                    <button type="submit" class="btn btn-danger"> تعديل </button>
                </div>
            </form>
        </div>
    </div>
</div>
