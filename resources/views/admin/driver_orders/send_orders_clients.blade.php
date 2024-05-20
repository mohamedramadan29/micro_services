<div class="modal" id="send_order_to_client">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title"> تسليم الشحنة الي العميل  </h6>
                <button aria-label="Close" class="close" data-dismiss="modal"
                        type="button"><span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="change_orders_status_form" class="change_orders_status_form" method="post" action="{{url('admin.php/orders/driver_orders/send_all')}}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="orders" id="selected_orders" class="selected_orders" value="">
                    <select class="form-control" name="change_status">
                        <option> -- اختر الحالة  -- </option>
                        <option @if($order['status'] == 'تسليم الشحنة فقط') selected @endif value="تسليم الشحنة فقط"> تسليم الشحنة فقط   </option>
                        <option @if($order['status'] == 'تسليم الشحنة واستلام القيمة') selected @endif value="تسليم الشحنة واستلام القيمة"> تسليم الشحنة واستلام القيمة </option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="change_orders_status2" class="btn ripple btn-primary change_orders_status2">تأكيد
                    </button>
                    <button type="button" class="btn ripple btn-secondary" data-dismiss="modal">رجوع</button>
                </div>
            </form>

        </div>
    </div>
</div>
