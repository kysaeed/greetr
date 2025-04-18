<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class World extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 代入可能な属性
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'user_id',
    ];

    /**
     * このワールドを作成したユーザー
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * このワールドへのエントリー
     */
    public function entries()
    {
        return $this->hasMany(WorldEntry::class);
    }

    /**
     * このワールドに参加しているユーザー
     */
    public function participants()
    {
        return $this->belongsToMany(User::class, 'user_world')
            ->withPivot('is_selected')
            ->withTimestamps();
    }

    /**
     * このワールドをデフォルトとして選択しているユーザー
     */
    public function defaultUsers()
    {
        return $this->belongsToMany(User::class, 'user_world')
            ->wherePivot('is_selected', true)
            ->withTimestamps();
    }
}