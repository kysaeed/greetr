<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorldEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class WorldEntryController extends Controller
{
    /**
     * 今日のワールドエントリーを取得
     */
    public function getTodayEntry()
    {
        $user = Auth::user();
        $today = now()->format('Y-m-d');

        $entry = $user->worldEntries()
            ->whereDate('created_at', $today)
            ->first();

        return $entry;
    }

    /**
     * ワールドエントリーを作成
     */
    public function store(Request $request)
    {
        try {
            // 今日の日付のエントリーが既に存在するかチェック
            $todayEntry = $this->getTodayEntry();
            if ($todayEntry) {
                return response()->json([
                    'success' => false,
                    'message' => '今日はすでにエントリー済みです'
                ]);
            }

            // バリデーション
            $validated = $request->validate([
                'daily_quests' => 'array',
            ]);

            // ユーザーの選択されたワールドを取得
            $selectedWorld = auth()->user()->worlds()
                ->wherePivot('is_selected', true)
                ->first();

            if (!$selectedWorld) {
                return response()->json([
                    'success' => false,
                    'message' => 'ワールドが選択されていません'
                ]);
            }

            // 曜日ごとの報酬を設定
            $dayOfWeek = now()->dayOfWeek; // 0:日曜, 1:月曜, ..., 6:土曜
            $rewards = [100, 200, 300, 400, 500]; // 月曜から金曜までの報酬
            $reward = ($dayOfWeek >= 1 && $dayOfWeek <= 5) ? $rewards[$dayOfWeek - 1] : 0;

            // エントリーを作成
            $entry = auth()->user()->worldEntries()->create([
                'world_id' => $selectedWorld->id,
                'daily_quests' => $validated['daily_quests'] ?? [],
                'coins_earned' => $reward,
                'day_of_week' => $dayOfWeek
            ]);

            // Slackに通知
            $this->postToSlack(auth()->user(), now(), $dayOfWeek, $reward, $validated['daily_quests'] ?? []);

            return response()->json([
                'success' => true,
                'message' => 'エントリーが作成されました',
                'reward' => $reward
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'エントリーの作成に失敗しました: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * ユーザーの全てのワールドエントリーを取得
     */
    public function getUserEntries()
    {
        $user = Auth::user();
        $entries = $user->worldEntries()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($entries);
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

    /**
     * Slackに投稿する
     *
     * @param User $user
     * @param \Carbon\Carbon $date
     * @param int $dayOfWeek
     * @param int $reward
     * @param array $dailyQuests
     * @return void
     */
    private function postToSlack($user, $date, $dayOfWeek, $reward, $dailyQuests)
    {
        $webhookUrl = env('SLACK_WEBHOOK_URL');

        if (!$webhookUrl) {
            return;
        }

        // クエストリストの作成
        $questList = '';
        if (!empty($dailyQuests)) {
            foreach ($dailyQuests as $quest) {
                $questList .= "• {$quest}\n";
            }
        }

        // メッセージの作成
        $message = [
            'text' => "*{$user->name}*\n"
            . "おはよう！\n"
            . $questList
        ];

        // Slackに投稿
        try {
            $client = new Client();
            $client->post($webhookUrl, [
                'json' => $message
            ]);
        } catch (\Exception $e) {
            Log::error('Slack投稿エラー: ' . $e->getMessage());
        }
    }
}