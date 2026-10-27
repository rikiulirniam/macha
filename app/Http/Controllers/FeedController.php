<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedController extends Controller
{
    public function __construct(
        protected RecommendationService $recommendationService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $tab = $request->query('tab', 'for-you');
        $tagId = $request->filled('tag') ? (int) $request->query('tag') : null;
        $offset = (int) $request->query('offset', 0);
        $limit = 10;

        $user = $request->user();

        if ($tab === 'latest') {
            $readings = $this->recommendationService->getLatestReadings($limit, $offset, $tagId);
        } else {
            $readings = $this->recommendationService->getRecommendedReadings($user, $limit, $offset, $tagId);
        }

        $hasMore = $readings->count() >= $limit;

        if ($request->ajax() || $request->wantsJson() || $request->query('ajax') === '1') {
            $html = '';
            foreach ($readings as $index => $reading) {
                $html .= view('components.reading-card', [
                    'reading' => $reading,
                    'isFirst' => false,
                    'isFeed' => true,
                ])->render();
            }

            return response()->json([
                'html' => $html,
                'has_more' => $hasMore,
                'next_offset' => $offset + $readings->count(),
            ]);
        }

        $tags = Tag::all();

        return view('feed', [
            'readings' => $readings,
            'tags' => $tags,
            'activeTab' => $tab,
            'activeTagId' => $tagId,
            'hasMore' => $hasMore,
        ]);
    }
}
