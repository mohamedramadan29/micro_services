<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Service 3: the team asks the client and the freelancer for a reply by a date (outside a dispute). */
class ContractReplyRequest extends Model
{
    protected $guarded = [];

    protected $casts = [
        'due_at' => 'datetime',
        'client_replied_at' => 'datetime',
        'freelancer_replied_at' => 'datetime',
        'overdue_alerted_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function isOpen(): bool
    {
        return $this->closed_at === null;
    }

    public function answered(): bool
    {
        return $this->client_replied_at && $this->freelancer_replied_at;
    }
}
