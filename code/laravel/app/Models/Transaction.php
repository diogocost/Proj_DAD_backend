<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    public function sender() {
        return $this->belongsTo(vCard::class, 'vcard');
    }

    public function receiver() {
        return $this->belongsTo(vCard::class, 'pair_vcard');
    }

    public function category() {
        return $this->belongsTo(Category::class);
    }

    protected $fillable = [
        'vcard',
        'pair_vcard',
        'type',
        'category_id',
        'value',
        'description',
        'payment_type',
        'payment_reference',
        'date',
        'datetime',
        'old_balance',
        'new_balance',
    ];
}
