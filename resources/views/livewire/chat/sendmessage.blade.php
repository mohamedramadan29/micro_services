<div>
    @if($conversation_id)
        <form action="" method="post" wire:submit.prevent="SendMessage">
       <textarea id="messageInput" style="height: 110px" wire:model="body" cols="30" rows="3" class="form-control with-light"
                 placeholder="اكتب رسالتك ..."></textarea>

            <script>
                window.addEventListener('clearMessageInput', () => {
                    document.getElementById('messageInput').value = '';
                });
            </script>


            <br>
            <button type="submit" class="btn dark-2"> ارسال  <i class="fa fa-plane"></i> </button>
        </form>
    @endif
</div>
