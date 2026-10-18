<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A delivered file; private (public/uploads/private), downloaded through the contract page only. */
class ContractDeliveryFile extends Model
{
    public const DISK = 'private_uploads';

    protected $guarded = [];

    public function delivery()
    {
        return $this->belongsTo(ContractDelivery::class, 'contract_delivery_id');
    }

    /** Files the client sent when answering a «waiting for the client» pause. */
    public function pause()
    {
        return $this->belongsTo(ContractPause::class, 'contract_pause_id');
    }

    public function dispute()
    {
        return $this->belongsTo(ContractDispute::class, 'contract_dispute_id');
    }

    /** The contract the file belongs to (a delivery's, a pause's or a dispute's). */
    public function contractId(): ?int
    {
        return $this->delivery?->stage?->contract_id ?? $this->pause?->stage?->contract_id ?? $this->dispute?->contract_id;
    }
}
