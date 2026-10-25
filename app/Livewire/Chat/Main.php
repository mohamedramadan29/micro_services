<?php

namespace App\Livewire\Chat;

use Livewire\Attributes\Locked;
use Livewire\Component;
use App\Models\front\Conversation;
use App\Models\admin\Service;

class Main extends Component
{

    #[Locked]
    public $conversation_id;
    public $service = null;

    public function mount($conversation_id)
    {
        // تمرير المعرف إلى بقية المكونات
        $this->conversation_id = $conversation_id;
        $conversation = Conversation::forParticipant($conversation_id, auth()->id());
        if ($conversation && $conversation->service_id) {
            $this->service = Service::find($conversation->service_id);
        }
    }
    public function render()
    {
        return view('livewire.chat.main', [
            'conversation_id' => $this->conversation_id,
            'service' => $this->service,
        ]);
    }

}
