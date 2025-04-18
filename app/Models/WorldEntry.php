<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorldEntry extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 代入可能な属性
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'world_id',
        'entry_time',
        'exit_time',
        'coins_earned',
        'day_of_week',
    ];

    /**
     * 日付として扱う属性
     *
     * @var array<string>
     */
    protected $dates = [
        'entry_time',
        'exit_time',
        'created_at',
        'updated_at',
    ];

    /**
     * このエントリーのユーザー
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * このエントリーのワールド
     */
    public function world()
    {
        return $this->belongsTo(World::class);
    }

    /**
     * 今日のエントリーを取得
     */
    public static function getTodayEntry($userId)
    {
        return static::where('user_id', $userId)
            ->whereDate('entry_time', today())
            ->whereNull('exit_time')
            ->first();
    }

    /**
     * ユーザーの今日のエントリーが存在するか確認
     */
    public static function hasTodayEntry($userId)
    {
        return static::where('user_id', $userId)
            ->whereDate('entry_time', today())
            ->whereNull('exit_time')
            ->exists();
    }
}
