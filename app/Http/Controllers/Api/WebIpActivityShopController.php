<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebIpActivityShop;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class WebIpActivityShopController extends Controller
{
    /**
     * 獲取所有 IP 商店列表
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $shops = WebIpActivityShop::orderBy('votes', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $shops,
                'message' => '獲取 IP 商店列表成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '獲取 IP 商店列表失敗：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 投票功能
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function vote(Request $request, int $id): JsonResponse
    {
        try {
            $shop = WebIpActivityShop::findOrFail($id);

            // 每次投票增加 1 票
            $shop->addVotes(1);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $shop->id,
                    'title' => $shop->title,
                    'votes' => $shop->votes
                ],
                'message' => '投票成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'IP 商店不存在或投票失敗'
            ], 404);
        }
    }

    /**
     * 獲取投票排行榜
     *
     * @return JsonResponse
     */
    public function leaderboard(): JsonResponse
    {
        try {
            $shops = WebIpActivityShop::orderBy('votes', 'desc')
                                     ->get(['id', 'title', 'votes', 'image_url']);

            return response()->json([
                'success' => true,
                'data' => $shops,
                'message' => '獲取投票排行榜成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '獲取投票排行榜失敗：' . $e->getMessage()
            ], 500);
        }
    }
}
