<?php

namespace App\Livewire\Chat;

use Livewire\Component;

class Main extends Component
{

    public $conversation_id;

    public function mount($conversation_id)
    {
        // تمرير المعرف إلى بقية المكونات
        $this->conversation_id = $conversation_id;
    }
    public function render()
    {
        return view('livewire.chat.main', [
            'conversation_id' => $this->conversation_id,
        ]);
    }

}
