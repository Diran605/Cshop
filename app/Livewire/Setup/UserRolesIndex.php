<?php

namespace App\Livewire\Setup;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class UserRolesIndex extends Component
{
    public int $branch_id = 0;
    public int $user_id = 0;

    /**
     * @var array<int, string>
     */
    public array $selected_roles = [];

    public function mount(): void
    {
        $this->branch_id = (int) (Branch::query()->where('is_active', true)->orderBy('name')->value('id') ?? 0);
        $this->user_id = 0;
        $this->selected_roles = [];
    }

    public function updatedUserId(): void
    {
        $this->selected_roles = [];

        if ($this->user_id <= 0 || $this->branch_id <= 0) {
            return;
        }

        setPermissionsTeamId((int) $this->branch_id);

        $user = User::query()->whereKey((int) $this->user_id)->where('branch_id', (int) $this->branch_id)->first();
        if ($user) {
            $this->selected_roles = $user->roles()->pluck('name')->values()->all();
        }

        setPermissionsTeamId(null);
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can('users.manage'), 403);

        $this->validate([
            'branch_id' => ['required', 'integer', 'min:1', Rule::exists('branches', 'id')],
            'user_id' => ['required', 'integer', 'min:1', Rule::exists('users', 'id')],
            'selected_roles' => ['array'],
        ]);

        $branchId = (int) $this->branch_id;
        setPermissionsTeamId($branchId);

        $user = User::query()->whereKey((int) $this->user_id)->where('branch_id', $branchId)->firstOrFail();

        $roleNames = collect($this->selected_roles)
            ->map(fn ($v) => trim((string) $v))
            ->filter(fn ($v) => $v !== '')
            ->unique()
            ->values()
            ->all();

        $validRoles = Role::query()
            ->where('branch_id', $branchId)
            ->whereIn('name', $roleNames)
            ->pluck('name')
            ->values()
            ->all();

        $user->syncRoles($validRoles);

        setPermissionsTeamId(null);

        session()->flash('status', 'User roles updated successfully.');
    }

    public function render()
    {
        abort_unless(auth()->user()?->can('users.manage'), 403);

        $branches = Branch::query()->where('is_active', true)->orderBy('name')->get();

        $users = User::query()
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', (int) $this->branch_id))
            ->orderBy('name')
            ->get();

        $roles = collect();
        if ($this->branch_id > 0) {
            setPermissionsTeamId((int) $this->branch_id);
            $roles = Role::query()->where('branch_id', (int) $this->branch_id)->orderBy('name')->get();
            setPermissionsTeamId(null);
        }

        return view('livewire.setup.user-roles-index', [
            'branches' => $branches,
            'users' => $users,
            'roles' => $roles,
        ]);
    }
}
