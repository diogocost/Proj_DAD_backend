<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Users;


class Vcard extends Model
{
    use HasFactory;
    use SoftDeletes;

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
        return $this->belongsTo(User::class, 'user_type', 'user_type')->where('user_type', 'V');
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'vcard');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'vcard', 'phone_number');
    }

    public function initializeDefaultCategories()
    {
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
