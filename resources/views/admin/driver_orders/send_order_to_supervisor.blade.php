<div class="modal" id="send_order_to_supervisor_model_{{$order['id']}}">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title"> تسليم الشحنة الي المندوب   </h6>
                <button aria-label="Close" class="close" data-dismiss="modal"
                        type="button"><span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post"
                  action="{{url('admin.php/orders/driver_orders/send_order_to_supervisor/'.$order['id'])}}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <input type="hidden" name="order_id" value="{{$order['id']}}">
                        <label for="">  حدد المندوب   </label>
                        <select class="form-control" name="super_id">
                            <option> -- حدد المندوب   -- </option>
                            @foreach($supervisors as $super)
                                <option value="{{$super['id']}}"> {{$super['name']}} </option>

                            @endforeach

                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="submit"> تاكيد ارسال
                    </button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal"
                            type="button">رجوع
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
