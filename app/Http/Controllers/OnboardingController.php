<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function show(): View
    {
        $tags = Tag::all();
        $selectedTagIds = auth()->user()->tags()->pluck('tags.id')->toArray();

        return view('onboarding', [
            'tags' => $tags,
            'selectedTagIds' => $selectedTagIds,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tags' => ['required', 'array', 'min:3'],
            'tags.*' => ['exists:tags,id'],
        ], [
            'tags.required' => 'Pilih minimal 3 topik minat untuk personalisasi feed kamu.',
            'tags.min' => 'Silakan pilih minimal 3 topik agar kami bisa merekomendasikan bacaan terbaik.',
        ]);

        $user = $request->user();
        $user->tags()->sync($validated['tags']);

        if (! $user->onboarded_at) {
            $user->update(['onboarded_at' => now()]);
        }

        return redirect()->route('home')->with('success', 'Selamat datang di Macha! Feed bacaanmu telah disesuaikan.');
    }
}
