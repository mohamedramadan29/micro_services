<div class="modal" id="add_model">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">  اضافه مستخدم جديد  </h6>
                <button aria-label="Close" class="close" data-dismiss="modal"
                        type="button"><span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="{{url('admin.php/users/add')}}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>  الاسم  </label>
                        <input required type="text" name="name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>   البريد الالكتروني   </label>
                        <input required type="email" name="email" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>   رقم الهاتف   </label>
                        <input required type="text" name="phone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>   كلمه المرور   </label>
                        <input required type="password" name="password" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>  الحاله  </label>
                        <select required class="form-control" name="status">
                            <option> -- حدد حالة    --  </option>
                            <option value="1"> فعال </option>
                            <option value="0">غير فعال </option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="submit">  إضافة
                    </button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal"
                            type="button">رجوع
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
