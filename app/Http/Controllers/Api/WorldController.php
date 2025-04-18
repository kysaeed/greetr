<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorldEntry;
use App\Models\DailyQuest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;

class WorldController extends Controller
{
    /**
     * 今週のログインボーナス一覧を取得
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWeeklyBonus()
    {
        // 認証済みユーザーを取得
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => '認証が必要です'
            ], 401);
        }

        // 今週の月曜日の日付を計算
        $today = now();
        $dayOfWeek = $today->dayOfWeek; // 0:日曜, 1:月曜, ..., 6:土曜
        $mondayOffset = $dayOfWeek === 0 ? -6 : 1 - $dayOfWeek; // 日曜なら前の月曜に、それ以外は今週の月曜に

        $monday = $today->copy()->addDays($mondayOffset);

        // ログインボーナスの報酬テーブル (曜日別)
        $rewards = [
            1 => 100, // 月曜日
            2 => 200, // 火曜日
            3 => 300, // 水曜日
            4 => 400, // 木曜日
            5 => 500, // 金曜日
        ];

        $dayNames = ['日', '月', '火', '水', '木', '金', '土'];
        $weeklyBonus = [];

        // 月〜金のそれぞれの日付とログイン状態を設定
        for ($i = 0; $i < 5; $i++) {
            $date = $monday->copy()->addDays($i);
            $dateStr = $date->format('Y-m-d');
            $weekday = $i + 1; // 1:月曜, 2:火曜, ..., 5:金曜

            // 日付の範囲を指定してクエリを実行
            $startOfDay = $date->copy()->startOfDay();
            $endOfDay = $date->copy()->endOfDay();

            // エントリー状態をチェック
            $isLoggedIn = $user->worldEntries()
                ->whereBetween('entry_time', [$startOfDay, $endOfDay])
                ->exists();

            $weeklyBonus[] = [
                'date' => $dateStr,
                'weekday' => $weekday,
                'dayName' => $dayNames[$weekday],
                'reward' => $rewards[$weekday] ?? 0,
                'isLoggedIn' => $isLoggedIn
            ];
        }

        return response()->json([
            'success' => true,
            'weeklyBonus' => $weeklyBonus
        ]);
    }

    /**
     * 週間ボーナス情報を取得
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function weeklyBonus()
    {
        $user = Auth::user();
        $today = now();

        // 今週の月曜日の日付を計算
        $dayOfWeek = $today->dayOfWeek; // 0:日曜, 1:月曜, ..., 6:土曜
        $mondayOffset = $dayOfWeek === 0 ? -6 : 1 - $dayOfWeek; // 日曜なら前の月曜に、それ以外は今週の月曜に
        $monday = $today->copy()->addDays($mondayOffset);

        // 月〜金のそれぞれの日付とログイン状態を設定
        $weeklyBonus = [];
        $rewards = [
            1 => 100, // 月曜日
            2 => 200, // 火曜日
            3 => 300, // 水曜日
            4 => 400, // 木曜日
            5 => 500, // 金曜日
        ];

        for ($i = 0; $i < 5; $i++) {
            $date = $monday->copy()->addDays($i);
            $dateStr = $date->format('Y-m-d');

            // その日のエントリーが存在するか確認
            $isLoggedIn = $user->worldEntries()
                ->whereDate('created_at', $dateStr)
                ->exists();

            $weeklyBonus[] = [
                'date' => $dateStr,
                'reward' => $rewards[$i + 1],
                'isLoggedIn' => $isLoggedIn
            ];
        }

        return response()->json([
            'success' => true,
            'weeklyBonus' => $weeklyBonus
        ]);
    }
}
