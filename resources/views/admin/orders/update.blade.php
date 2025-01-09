<div class="modal" id="update_order_{{ $order['id'] }}">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h5 class="modal-title"> تعديل حالة الطلب </h5>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                        aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ url('admin/order/update/' . $order['id']) }}" method="post">
                @csrf
                <div class="modal-body">
                    <label for=""> رقم الطلب </label>
                    <input type="text" name="name" class="form-control" disabled readonly
                        value="{{ $order['id'] }}">
                    <div class="mt-3">
                        <label for=""> تعديل حالة الطلب </label>
                        <select name="status_value" id="" class="form-control">
                            <option value="" disabled> - حدد حالة الطلب - </option>
                            <option @if ($order['order_status'] == 'لم يبدا') selected @endif value="لم يبدا">لم يبدا</option>
                            <option @if ($order['order_status'] == 'قيد التنفيذ') selected @endif value="قيد التنفيذ">قيد التنفيذ
                            </option>
                            <option @if ($order['order_status'] == 'تم التنفيذ') selected @endif value="تم التنفيذ">تم التنفيذ
                            </option>
                            <option @if ($order['order_status'] == 'تم الغاء') selected @endif value="تم الغاء">تم الغاء</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">رجوع
                    </button>
                    <button type="submit" class="btn btn-primary">تعديل</button>
                </div>
            </form>
        </div>
    </div>
</div>
