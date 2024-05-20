<div class="modal" id="edit_model_{{$category['id']}}">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title"> تعديل القسم  </h6>
                <button aria-label="Close" class="close" data-dismiss="modal"
                        type="button"><span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="{{url('admin/category/update/'.$category['id'])}}" autocomplete="off" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="cat_id" value="{{$category['id']}}">
                    <div class="form-group">
                        <label> اسم القسم </label>
                        <input required type="text" name="name" class="form-control" value="{{$category['name']}}">
                    </div>

                    <div class="form-group">
                        <label> نوع القسم </label>
                        <select class="form-control" name="parent_id">
                            <option value=""> -- حدد نوع القسم --</option>
                            <option @if($category['parent_id'] == 0) selected @endif value="0"> رئيسي</option>
                            @foreach($categories as $cat)
                                <option @if($cat['id'] == $category['parent_id']) selected @endif value="{{$cat['id']}}"> {{ $cat['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label> صورة القسم </label>
                        <input type="file" name="image" class="form-control">
                        <img width="60px" height="60px" src="{{asset('assets/uploads/service_category/'.$category['image'])}}" alt="">
                    </div>
                    <div class="form-group">
                        <label> حالة القسم </label>
                        <select class="form-control" name="status">
                            <option value=""> -- حدد حالة القسم --</option>
                            <option @if($category['status'] == 1) selected @endif value="1"> فعال</option>
                            <option @if($category['status'] == 0) selected @endif value="0"> غير فعال</option>
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
