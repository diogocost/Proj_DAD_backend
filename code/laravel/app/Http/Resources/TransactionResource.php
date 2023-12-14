<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'datetime' => $this->datetime,
            'type' => $this->type,
            'value' => $this->value,
            'old_balance' => $this->old_balance,
            'new_balance' => $this->new_balance,
            'payment_type' => $this->payment_type,
            'payment_reference' => $this->payment_reference,
            'vcard' => $this->vcard,
            'pair_vcard' => $this->pair_vcard,
            'category_id' => $this->category_id,
            'category_name' => $this->category ? $this->category->name : '-- No Category --',
            'description' => $this->description,
        ];
    }
}
