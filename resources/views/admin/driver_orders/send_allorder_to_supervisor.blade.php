<div class="modal" id="send_allorder_to_supervisor">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title"> تسليم الشحنة الي المندوب  </h6>
                <button aria-label="Close" class="close" data-dismiss="modal"
                        type="button"><span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="change_orders_status_form" class="change_orders_status_form" method="post" action="{{url('admin.php/orders/driver_orders/send_all_to_supervisors')}}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="orders" id="selected_orders" class="selected_orders" value="">
                    <select class="form-control" name="super_id">
                        <option> -- حدد المندوب   -- </option>
                        @foreach($supervisors as $super)
                            <option value="{{$super['id']}}"> {{$super['name']}} </option>

                        @endforeach

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
