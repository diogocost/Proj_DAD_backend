<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vcard extends Model
{
    use HasFactory;

    protected $table = 'vcards';
    protected $primaryKey = 'phone_number';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'phone_number',
        'name',
        'email',
        'photo_url',
        'password',
        'confirmation_code',
        'blocked',
        'balance',
        'max_debit'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'confirmation_code',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'confirmation_code' => 'hashed',
        'password' => 'hashed',
    ];

    // Define the relationship to the Users model
    public function user()
    {
        return $this->belongsTo(Users::class, 'user_type', 'user_type')->where('user_type', 'V');
    }

    public function categories() {
        return $this->hasMany(Category::class);
    }

    public function transactions() {
        return $this->hasMany(Transaction::class);
    }

    public function initializeDefaultCategories() {
        $defaultCategories = DefaultCategory::all();

        foreach ($defaultCategories as $defaultCategory) {
            $this->categories()->create([
                'name' => $defaultCategory->name,
                'type' => $defaultCategory->type,
                'vcard' => $this->phone_number,
            ]);
        }
    }
}
