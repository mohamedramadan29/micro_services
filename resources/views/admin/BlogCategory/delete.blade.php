<div class="modal fade" id="delete_category_{{ $category['id'] }}" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"> هل انت متاكذ من حذف القسم </h5>
                <button aria-label="Close" class="close" data-dismiss="modal"
                type="button"><span aria-hidden="true">&times;</span>
        </button>
            </div>
            <form action="{{ url('admin/blog_category/delete/' . $category['id']) }}" method="post">
                @csrf
                <div class="modal-body">
                    <label for=""> العنوان </label>
                    <input type="text" name="name" class="form-control" disabled readonly
                        value="{{ $category['name'] }}">
                </div>

                <div class="modal-footer">
                    <button class="btn ripple btn-danger" type="submit"> حذف
                    </button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal"
                            type="button">رجوع
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
