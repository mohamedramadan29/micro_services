       <!-- Modal -->
       <div dir="rtl" class="text-right">
           <div class="modal fade buy_services_model" id="FreeConsultModel" tabindex="-1"
               aria-labelledby="exampleModalLabel" aria-hidden="true">
               <div class="modal-dialog">
                   <div class="modal-content">
                       <div class="modal-header">
                           <h1 class="modal-title fs-5" id="exampleModalLabel"> الحصول علي استشارة مجانية </h1>
                           <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> X
                           </button>
                       </div>
                       <div class="modal-body">
                           @if (Auth::check())
                               {{-- <form id="categoryForm" style="width: 100%" method="post"> --}}
                               <div class="form-group">
                                   <label>حدد المجال</label>
                                   <select name="category" id="categorySelect" class="form-select" required>
                                       <option value="" selected disabled> -- حدد المجال -- </option>
                                       @foreach ($categories as $category)
                                           <option value="{{ $category['id'] }}" data-slug="{{ $category['slug'] }}">
                                               {{ $category['name'] }}
                                           </option>
                                       @endforeach
                                   </select>
                               </div>

                               @csrf

                               <!-- زر الانتقال إلى خدمات القسم -->
                               <button type="button" class="btn global_button" onclick="goToSelectedCategory()">
                                   <i class="bi bi-arrow-left"></i> خدمات القسم
                               </button>
                               {{-- </form> --}}

                               <script>
                                function goToSelectedCategory() {
                                    const select = document.getElementById('categorySelect');
                                    const selectedOption = select.options[select.selectedIndex];

                                    if (!selectedOption || !selectedOption.dataset.slug) {
                                        alert("يرجى تحديد المجال أولاً.");
                                        return;
                                    }

                                    const slug = selectedOption.dataset.slug;
                                    const url = `/services/${slug}`;
                                    window.location.href = url;
                                }
                            </script>

                               <!-- مكان الفورم الثاني -->
                               <div id="consultantForm" style="display: none;">
                                   <div class="consultant-list row">
                                       <!-- سيتم تحميل المستشارين باستخدام JavaScript -->
                                   </div>
                                   <button type="button" id="backToCategory" class="btn btn-danger mb-3">
                                       <i class="bi bi-arrow-right"></i> رجوع
                                   </button>
                               </div>
                               <div id="questionsForm" style="display: none;">
                                   <form id="finalForm" action="{{ url('consult_conversation/start') }}" method="POST">
                                       @csrf
                                       <input type="hidden" name="consultant" value="">
                                       <input type="hidden" name="category" value="">
                                       <div class="form-group">
                                           <label>ما هو استفسارك؟</label>
                                           <textarea name="question" class="form-control" rows="3" required></textarea>
                                       </div>
                                       <div class="d-flex justify-content-between align-items-center">
                                           <button type="submit" class="btn btn-primary btn-sm">أرسل وابدأ
                                               المحادثة</button>
                                           <button type="button" id="backToConsultant" class="btn btn-danger">
                                               <i class="bi bi-arrow-right"></i> رجوع
                                           </button>
                                       </div>
                                   </form>
                               </div>
                           @else
                               <div class="text-center">
                                   <a href="{{ url('login') }}" type="button" class="btn btn-primary">
                                       من فضلك سجل دخولك في البداية !
                                   </a>
                               </div>
                           @endif
                       </div>

                       <div class="modal-footer">
                       </div>
                   </div>
               </div>
           </div>
       </div>
       <script>
           document.getElementById('nextButton').addEventListener('click', function() {
               const categoryId = document.getElementById('categorySelect').value;
               if (!categoryId) {
                   alert('من فضلك اختر مجالاً');
                   return;
               }

               // إرسال الطلب لجلب المستشارين
               fetch(`/consultants?category_id=${categoryId}`)
                   .then(response => response.json())
                   .then(data => {
                       const consultantList = document.querySelector('.consultant-list');
                       consultantList.innerHTML = ''; // تفريغ القائمة القديمة

                       data.consultants.forEach(consultant => {
                           consultantList.innerHTML += `
                    <div class="col-md-12 mb-2">
                        <div class="card">
                            <img src="/assets/uploads/consultants/${consultant.image}" class="card-img-top" alt="${consultant.name}">
                            <div class="card-body">
                                <div>
                                    <h5 class="card-title">${consultant.name}</h5>
                                    <p class="card-text">${consultant.bio}</p>
                                </div>
                                <button class="btn btn-primary select-consultant" data-id="${consultant.id}">
                                    اختيار <i class='bi bi-check'></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                       });

                       // إظهار واجهة اختيار المستشار
                       document.getElementById('categoryForm').style.display = 'none';
                       document.getElementById('consultantForm').style.display = 'block';

                       // تخزين category_id في الفورم النهائي
                       document.querySelector('input[name="category"]').value = categoryId;

                       // إضافة حدث لاختيار مستشار
                       document.querySelectorAll('.select-consultant').forEach(button => {
                           button.addEventListener('click', function() {
                               const consultantId = this.getAttribute('data-id');
                               document.getElementById('consultantForm').style.display = 'none';
                               document.getElementById('questionsForm').style.display = 'block';

                               // تخزين consultant_id في الفورم النهائي
                               document.querySelector('input[name="consultant"]').value =
                                   consultantId;
                           });
                       });
                   })
                   .catch(error => console.error('Error fetching consultants:', error));
           });

           // زر الرجوع
           document.getElementById('backToCategory').addEventListener('click', function() {
               document.getElementById('consultantForm').style.display = 'none';
               document.getElementById('categoryForm').style.display = 'block';
           });
           document.getElementById('backToConsultant').addEventListener('click', function() {
               document.getElementById('questionsForm').style.display = 'none';
               document.getElementById('consultantForm').style.display = 'block';
           });
       </script>
