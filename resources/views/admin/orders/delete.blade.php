<div class="modal" id="delete_order_{{$order['id']}}">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h5 class="modal-title"> هل انت متاكد من حذف الطلب </h5>
                <button aria-label="Close" class="close" data-dismiss="modal"
                        type="button"><span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{url('admin/order/delete/'.$order['id'])}}" method="post">
                @csrf
                <div class="modal-body">
                    <label for=""> رقم الطلب  </label>
                    <input type="text" name="name" class="form-control" disabled readonly value="{{$order['id']}}">
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
