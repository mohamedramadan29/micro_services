<div class="modal" id="change_status_order_model_{{$order['id']}}">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title"> تغير حالة الشحنة  </h6>
                <button aria-label="Close" class="close" data-dismiss="modal"
                        type="button"><span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post"
                  action="{{url('admin.php/orders/driver_orders/change_status/'.$order['id'])}}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for=""> تغير حاله الشحنة  </label>
                        <select class="form-control" name="change_status">
                            <option> -- اختر الحالة  -- </option>
                            <option @if($order['status'] == 'تسليم الشحنة فقط') selected @endif value="تسليم الشحنة فقط"> تسليم الشحنة فقط   </option>
                            <option @if($order['status'] == 'تسليم الشحنة واستلام القيمة') selected @endif value="تسليم الشحنة واستلام القيمة"> تسليم الشحنة واستلام القيمة </option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="submit"> تاكيد تغير الحالة
                    </button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal"
                            type="button">رجوع
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
