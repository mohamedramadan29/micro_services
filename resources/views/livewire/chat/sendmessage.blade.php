<div>
    @if ($conversation_id)
        <form action="" method="post" wire:submit.prevent="SendMessage">
            <textarea id="messageInput" style="height: 110px" wire:model="body" cols="30" rows="3"
                class="form-control with-light" placeholder="اكتب رسالتك ..."></textarea>

            <script>
                window.addEventListener('clearMessageInput', () => {
                    document.getElementById('messageInput').value = '';
                });
            </script>


            <br>
            <button type="submit" class="btn dark-2"> ارسال <i class="fa fa-plane"></i> </button>
        </form>
        <div style="background-color: #FFEED4;padding: 10px;margin-top: 11px;border-radius: 10px;">
            <ul>
                <li class="pb-2"> <i class="fa fa-exclamation-triangle"></i> لا تستخدم وسائل تواصل خارجية وأحرص على
                    ابقاء تواصلك داخل
                    منصة نفذها فقط
                    حتى لا تتعرض للنصب أو
                    الاحتيال</li>

                <li class="pb-2"> <i class="fa fa-exclamation-triangle"></i>
                    لا تقم بتحويل أي مبالغ مالية خارج منصة نفذها فمنصة نفذلي تضمن فقط المعاملات المالية التي تتم داخلها
                    فقط
                </li>
                <li class="pb-2"> <i class="fa fa-exclamation-triangle"></i> تواصل معنا مباشرةً إذا وجدت أي طلبات
                    غريبة </li>
                <li class="pb-2"> <i class="fa fa-exclamation-triangle"></i> نحن معك على مدار الساعة لتقديم الدعم
                    الفني </li>
            </ul>

        </div>
    @endif
</div>
