<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Badge;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the logged in user's profile.
     */
    public function show(Request $request): View
    {
        $user = $request->user();

        return $this->renderProfileView($user, true);
    }

    /**
     * Display a public user profile by username.
     */
    public function showUser(string $username): View
    {
        $user = User::where('username', $username)->firstOrFail();
        $isOwner = auth()->check() && auth()->id() === $user->id;

        return $this->renderProfileView($user, $isOwner);
    }

    /**
     * Display the user's profile edit form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $tags = Tag::all();
        $userTagIds = $user->tags()->pluck('tags.id')->toArray();

        return view('profile.edit', [
            'user' => $user,
            'tags' => $tags,
            'userTagIds' => $userTagIds,
        ]);
    }

    /**
     * Update the user's profile information, avatar, and tags.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->name = $validated['name'];
        $user->username = strtolower($validated['username']);
        $user->email = $validated['email'];

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $user->avatar_path = $request->file('avatar')->store('avatars', 'public');
        }

        $user->save();

        if (isset($validated['tags'])) {
            $user->tags()->sync($validated['tags']);
        }

        return Redirect::route('profile.show')->with('success', 'Profil dan minat membaca berhasil diperbarui!');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/')->with('success', 'Akun berhasil dihapus.');
    }

    private function renderProfileView(User $user, bool $isOwner): View
    {
        $allBadges = Badge::all();
        $userBadgeMap = $user->badges()->get()->keyBy('id');

        $badgesWithStatus = $allBadges->map(function (Badge $b) use ($userBadgeMap) {
            $earned = isset($userBadgeMap[$b->id]);
            $b->is_earned = $earned;
            $b->earned_at = $earned ? $userBadgeMap[$b->id]->pivot->earned_at : null;

            return $b;
        });

        $currentWeekKey = Carbon::now('Asia/Jakarta')->isoFormat('GGGG-[W]WW');
        $weeklyExp = $user->getWeeklyExp($currentWeekKey);

        $readings = $user->readings()
            ->published()
            ->with(['tags', 'slides'])
            ->latest('published_at')
            ->get();

        return view('profile.show', [
            'user' => $user,
            'isOwner' => $isOwner,
            'weeklyExp' => $weeklyExp,
            'badges' => $badgesWithStatus,
            'readings' => $readings,
            'tags' => $user->tags,
        ]);
    }
}
