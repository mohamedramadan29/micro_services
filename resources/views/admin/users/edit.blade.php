<div class="modal" id="edit_model_{{$user['id']}}">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title"> تعديل المستخدم  </h6>
                <button aria-label="Close" class="close" data-dismiss="modal"
                        type="button"><span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="{{url('admin/users/edit')}}" autocomplete="off">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <input type="hidden" name="user_id" value="{{$user['id']}}">
                        <label>  الاسم  </label>
                        <input required type="text" name="name" class="form-control" value="{{$user['name']}}">
                    </div>
                    <div class="form-group">
                        <label>   البريد الالكتروني   </label>
                        <input required type="email" name="email" class="form-control" value="{{$user['email']}}">
                    </div>
                    <div class="form-group">
                        <label>   رقم الهاتف   </label>
                        <input required type="text" name="phone" class="form-control" value="{{$user['phone']}}">
                    </div>
                    <div class="form-group">
                        <label>  تعديل كلمه المرور  </label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>  الحاله  </label>
                        <select required class="form-control" name="status">
                            <option> -- حدد حالة    --  </option>
                            <option @if($user['status'] == 1) selected @endif value="1"> فعال </option>
                            <option @if($user['status'] == 0) selected @endif value="0">غير فعال </option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="submit">  تعديل
                    </button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal"
                            type="button">رجوع
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
