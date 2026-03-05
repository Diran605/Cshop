<?php

namespace App\Livewire\Setup;

use App\Models\Branch;
use App\Models\User;
use App\Support\ActivityLogger;
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

    public bool $show_view_modal = false;
    public bool $show_edit_modal = false;
    public bool $show_delete_modal = false;

    public int $view_user_id = 0;
    public int $edit_user_id = 0;
    public int $delete_user_id = 0;
    public string $delete_user_name = '';

    public array $edit_selected_roles = [];

    public int $filter_branch_id = 0;
    public int $filter_user_id = 0;
    public string $filter_role = '';

    public $viewUser = null;
    public $editUser = null;
    public $editRoles = null;
    public $deleteUser = null;

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

        ActivityLogger::log(
            'user_roles_assigned',
            $user,
            "Assigned roles to {$user->name}: " . implode(', ', $validRoles),
            ['roles' => $validRoles],
            $branchId
        );

        setPermissionsTeamId(null);

        session()->flash('status', 'User roles updated successfully.');
    }

    public function openViewModal(int $userId): void
    {
        $this->view_user_id = $userId;
        $user = User::query()->find($userId);
        if ($user) {
            $branchId = (int) ($user->branch_id ?? 0);
            setPermissionsTeamId($branchId);
            $this->viewUser = User::with(['branch', 'roles'])->find($userId);
            setPermissionsTeamId(null);
        }
        $this->show_view_modal = true;
    }

    public function closeViewModal(): void
    {
        $this->show_view_modal = false;
        $this->view_user_id = 0;
        $this->viewUser = null;
    }

    public function openEditModal(int $userId): void
    {
        $this->edit_user_id = $userId;
        $this->edit_selected_roles = [];

        $user = User::query()->findOrFail($userId);
        $branchId = (int) ($user->branch_id ?? 0);
        setPermissionsTeamId($branchId);
        $this->editUser = $user;
        $this->editRoles = Role::query()->where('branch_id', $branchId)->orderBy('name')->get();
        $this->edit_selected_roles = $user->roles()->pluck('name')->values()->all();
        setPermissionsTeamId(null);

        $this->show_edit_modal = true;
    }

    public function closeEditModal(): void
    {
        $this->show_edit_modal = false;
        $this->edit_user_id = 0;
        $this->edit_selected_roles = [];
        $this->editUser = null;
        $this->editRoles = null;
    }

    public function saveEdit(): void
    {
        if ($this->edit_user_id <= 0) {
            return;
        }

        $user = User::query()->findOrFail($this->edit_user_id);
        $branchId = (int) ($user->branch_id ?? 0);
        setPermissionsTeamId($branchId);

        $validRoles = Role::query()
            ->where('branch_id', $branchId)
            ->whereIn('name', $this->edit_selected_roles)
            ->pluck('name')
            ->values()
            ->all();

        $user->syncRoles($validRoles);

        ActivityLogger::log(
            'user_roles_updated',
            $user,
            "Updated roles for {$user->name}: " . implode(', ', $validRoles),
            ['roles' => $validRoles],
            $branchId
        );

        setPermissionsTeamId(null);

        session()->flash('status', 'Roles updated successfully.');
        $this->closeEditModal();
    }

    public function openDeleteModal(int $userId): void
    {
        $user = User::query()->findOrFail($userId);
        $this->delete_user_id = $userId;
        $this->delete_user_name = $user->name;
        $this->deleteUser = $user;
        $this->show_delete_modal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->show_delete_modal = false;
        $this->delete_user_id = 0;
        $this->delete_user_name = '';
    }

    public function confirmDelete(): void
    {
        if ($this->delete_user_id <= 0) {
            return;
        }

        $user = User::query()->findOrFail($this->delete_user_id);
        $branchId = (int) ($user->branch_id ?? 0);
        setPermissionsTeamId($branchId);

        $oldRoles = $user->roles()->pluck('name')->toArray();
        $user->syncRoles([]);

        ActivityLogger::log(
            'user_roles_removed',
            $user,
            "Removed all roles from {$user->name}",
            ['removed_roles' => $oldRoles],
            $branchId
        );

        setPermissionsTeamId(null);

        session()->flash('status', 'Roles removed successfully.');
        $this->closeDeleteModal();
    }

    public function getViewUserProperty()
    {
        if ($this->view_user_id > 0) {
            return User::with(['branch', 'roles'])->find($this->view_user_id);
        }
        return null;
    }

    public function getEditUserProperty()
    {
        if ($this->edit_user_id > 0) {
            return User::with(['branch'])->find($this->edit_user_id);
        }
        return null;
    }

    public function getEditRolesProperty()
    {
        if ($this->edit_user_id > 0) {
            $user = User::query()->find($this->edit_user_id);
            if ($user) {
                $branchId = (int) ($user->branch_id ?? 0);
                setPermissionsTeamId($branchId);
                $roles = Role::query()->where('branch_id', $branchId)->orderBy('name')->get();
                setPermissionsTeamId(null);
                return $roles;
            }
        }
        return collect();
    }

    public function getDeleteUserProperty()
    {
        if ($this->delete_user_id > 0) {
            return User::query()->find($this->delete_user_id);
        }
        return null;
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

        $filtered_users = User::query()
            ->when($this->filter_branch_id > 0, fn ($q) => $q->where('branch_id', (int) $this->filter_branch_id))
            ->orderBy('name')
            ->get();

        $all_roles = [];
        foreach ($branches as $branch) {
            setPermissionsTeamId((int) $branch->id);
            $branchRoles = Role::query()->where('branch_id', (int) $branch->id)->pluck('name')->toArray();
            $all_roles = array_merge($all_roles, $branchRoles);
            setPermissionsTeamId(null);
        }
        $all_roles = array_unique($all_roles);
        sort($all_roles);

        $user_role_assignments = [];
        foreach ($branches as $branch) {
            setPermissionsTeamId((int) $branch->id);
            $branchUsers = User::query()
                ->where('branch_id', (int) $branch->id)
                ->when($this->filter_branch_id > 0, fn ($q) => $q->where('branch_id', (int) $this->filter_branch_id))
                ->when($this->filter_user_id > 0, fn ($q) => $q->where('id', (int) $this->filter_user_id))
                ->with('roles')
                ->get();

            foreach ($branchUsers as $user) {
                $userRoles = $user->roles->pluck('name')->toArray();

                // Skip users with no roles
                if (empty($userRoles)) {
                    continue;
                }

                if ($this->filter_role !== '' && !in_array($this->filter_role, $userRoles)) {
                    continue;
                }

                $user_role_assignments[] = [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'branch_name' => $branch->name,
                    'roles' => $userRoles,
                ];
            }
            setPermissionsTeamId(null);
        }

        return view('livewire.setup.user-roles-index', [
            'branches' => $branches,
            'users' => $users,
            'roles' => $roles,
            'user_role_assignments' => $user_role_assignments,
            'filtered_users' => $filtered_users,
            'all_roles' => $all_roles,
        ]);
    }
}
