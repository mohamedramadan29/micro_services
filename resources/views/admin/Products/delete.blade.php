<div class="modal" id="delete_category_{{$product['id']}}">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h5 class="modal-title"> هل انت متاكد من حذف المنتج </h5>
                <button aria-label="Close" class="close" data-dismiss="modal"
                        type="button"><span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{url('admin/product/delete/'.$product['id'])}}" method="post">
                @csrf
                <div class="modal-body">
                    <label for=""> اسم المنتج </label>
                    <input type="text" name="name" class="form-control" disabled readonly value="{{$product['name']}}">
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-secondary" data-dismiss="modal"
                            type="button">رجوع
                    </button>
                    <button type="submit" class="btn btn-danger">حذف</button>
                </div>
            </form>
        </div>
    </div>
</div>
