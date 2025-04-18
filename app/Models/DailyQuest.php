<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyQuest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'order',
        'is_completed',
        'date'
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'date' => 'date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
