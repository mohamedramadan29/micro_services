<div class="modal fade" id="delete_category_{{$product['id']}}" tabindex="-1" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">  هل انت متاكد من حذف المنتج   </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{url('admin/product/delete/'.$product['id'])}}" method="post">
                @csrf
                <div class="modal-body">
                    <label for="">  اسم المنتج   </label>
                    <input type="text" name="name" class="form-control" disabled readonly value="{{$product['name']}}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> رجوع</button>
                    <button type="submit" class="btn btn-danger">حذف</button>
                </div>
            </form>
        </div>
    </div>
</div>
