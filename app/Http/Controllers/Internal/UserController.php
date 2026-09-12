<?php

namespace App\Http\Controllers\Internal;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('internal.users.index', [
            'users' => User::query()->orderBy('name')->paginate(25),
        ]);
    }

    public function create(): View
    {
        return view('internal.users.create', [
            'user' => new User,
            'roles' => UserRole::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $user = User::create($validated);
        $user->forceFill(['email_verified_at' => now()])->save();

        return redirect()->route('internal.users.edit', $user)
            ->with('status', 'Používateľský účet bol vytvorený.');
    }

    public function edit(User $user): View
    {
        return view('internal.users.edit', [
            'user' => $user,
            'roles' => UserRole::cases(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $this->validated($request, $user);

        if ($request->user()->is($user)) {
            unset($validated['role']);
        }

        if (($validated['password'] ?? '') === '') {
            unset($validated['password']);
        }

        $user->update($validated);

        return back()->with('status', 'Používateľský účet bol uložený.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'role' => ['required', Rule::enum(UserRole::class)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ]);
    }
}
