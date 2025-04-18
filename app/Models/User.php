<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\World;
use App\Models\WorldEntry;
use App\Models\DailyQuest;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * このユーザーのワールドエントリー
     */
    public function worldEntries()
    {
        return $this->hasMany(WorldEntry::class);
    }

    /**
     * 今日のワールドエントリーを取得
     */
    public function getTodayEntry()
    {
        return $this->worldEntries()
            ->whereDate('entry_time', today())
            ->whereNull('exit_time')
            ->first();
    }

    /**
     * 今日のワールドエントリーが存在するか確認
     */
    public function hasTodayEntry()
    {
        return $this->worldEntries()
            ->whereDate('entry_time', today())
            ->whereNull('exit_time')
            ->exists();
    }

    public function dailyQuests()
    {
        return $this->hasMany(DailyQuest::class);
    }

    /**
     * このユーザーが参加しているワールド
     */
    public function worlds()
    {
        return $this->belongsToMany(World::class, 'user_world')
            ->withPivot('is_selected')
            ->withTimestamps();
    }

    /**
     * このユーザーのデフォルトワールド
     */
    public function defaultWorld()
    {
        return $this->belongsToMany(World::class, 'user_world')
            ->wherePivot('is_selected', true)
            ->first();
    }

    /**
     * このユーザーが参加しているワールドにエントリーできるかどうか
     */
    public function canEnterWorld($worldId)
    {
        return $this->worlds()->where('worlds.id', $worldId)->exists();
    }
}
