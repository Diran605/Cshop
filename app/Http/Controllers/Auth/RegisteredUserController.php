<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $user = auth()->user();
        if ($user && (string) $user->role === 'super_admin') {
            return view('users.create', [
                'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            ]);
        }

        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $currentUser = $request->user();
        if ($currentUser && (string) $currentUser->role === 'super_admin') {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'branch_id' => ['required', 'integer', 'exists:branches,id'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'branch_admin',
                'branch_id' => (int) $request->branch_id,
            ]);

            ActivityLogger::log(
                'user.created',
                $user,
                'User created (registration)',
                [
                    'name' => $user->name,
                    'email' => $user->email,
                    'branch_id' => $user->branch_id,
                    'role' => $user->role,
                ],
                $user->branch_id ? (int) $user->branch_id : null,
                $currentUser ? (int) $currentUser->id : null
            );

            event(new Registered($user));

            return redirect()->route('users.index')->with('status', 'User created successfully.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        Branch::query()->firstOrCreate(
            ['name' => 'Main Branch'],
            ['code' => 'MAIN', 'is_active' => true]
        );

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'super_admin',
            'branch_id' => null,
        ]);

        ActivityLogger::log(
            'user.created',
            $user,
            'Initial super admin created (registration)',
            [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            null,
            (int) $user->id
        );

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
