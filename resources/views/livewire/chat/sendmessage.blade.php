<div>
    @if($selectedConversation)
        <form action="" method="post" wire:submit.prevent="SendMessage">
        <textarea style="height: 110px" wire:model="body" cols="40" rows="3" class="form-control with-light"
                  placeholder=" اكتب رسالتك ... "></textarea>
            <br>
            <button type="submit" class="btn dark-2"> ارسال  <i class="fa fa-plane"></i> </button>
        </form>
    @endif
</div>
