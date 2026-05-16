<?php

namespace Modules\StratosLogbook\Http\Controllers\Api;

use App\Contracts\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogbookController extends Controller
{
    public function pireps(Request $request): JsonResponse
    {
        return response()->json(['items' => [], 'total' => 0, 'limit' => 25, 'offset' => 0]);
    }

    public function pirep(string $id): JsonResponse
    {
        return response()->json(['message' => 'Not found'], 404);
    }

    public function stats(): JsonResponse
    {
        return response()->json([]);
    }
}
