<?php

namespace App\Http\Controllers;

use App\Models\World;
use Illuminate\Http\Request;

class WorldController extends Controller
{
    public function select(World $world)
    {
        // ユーザーがこのワールドにアクセス権を持っているか確認
        if (!$world->users()->where('user_id', auth()->id())->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'このワールドにアクセスする権限がありません'
            ], 403);
        }

        // 現在選択されているワールドを更新
        auth()->user()->worlds()->updateExistingPivot($world->id, ['is_selected' => true]);

        // 他のワールドの選択状態を解除
        auth()->user()->worlds()
            ->where('worlds.id', '!=', $world->id)
            ->update(['is_selected' => false]);

        return response()->json([
            'success' => true,
            'message' => 'ワールドを選択しました'
        ]);
    }
}