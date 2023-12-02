<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    public $timestamps = false;


    protected $fillable = [
        'name',
        'type',
        'vcard'
    ];

    public function vCard() {
        return $this->belongsTo(Vcard::class);
    }

    public function transactions() {
        return $this->hasMany(Transaction::class);
    }
}
