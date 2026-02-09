<?php

namespace App\Livewire\Setup;

use App\Models\Branch;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesIndex extends Component
{
    public int $branch_id = 0;

    public string $name = '';

    /**
     * @var array<int, string>
     */
    public array $selected_permissions = [];

    public int $editing_role_id = 0;

    public bool $show_edit_modal = false;

    public bool $show_delete_modal = false;
    public int $pending_delete_id = 0;
    public string $pending_delete_name = '';

    public int $expanded_role_id = 0;

    public array $expanded_permission_groups = [];

    public $permissions = [];

    public function toggleRolePermissions(int $roleId): void
    {
        if ($this->expanded_role_id === $roleId) {
            $this->expanded_role_id = 0;
        } else {
            $this->expanded_role_id = $roleId;
        }
    }

    public function togglePermissionGroup(string $group): void
    {
        if (in_array($group, $this->expanded_permission_groups)) {
            $this->expanded_permission_groups = array_diff($this->expanded_permission_groups, [$group]);
        } else {
            $this->expanded_permission_groups[] = $group;
        }
    }

    public function getGroupedPermissionsProperty(): array
    {
        $groups = [
            'branches' => ['label' => 'Branches', 'permissions' => []],
            'users' => ['label' => 'Users', 'permissions' => []],
            'rbac' => ['label' => 'RBAC', 'permissions' => []],
            'setup.categories' => ['label' => 'Setup: Categories', 'permissions' => []],
            'setup.bulk' => ['label' => 'Setup: Bulk Units & Types', 'permissions' => []],
            'products' => ['label' => 'Products', 'permissions' => []],
            'stock_in' => ['label' => 'Stock In', 'permissions' => []],
            'sales' => ['label' => 'Sales', 'permissions' => []],
            'expenses' => ['label' => 'Expenses', 'permissions' => []],
            'reports' => ['label' => 'Reports', 'permissions' => []],
            'audit.stock_movements' => ['label' => 'Audit: Stock Movements', 'permissions' => []],
            'audit.activity_logs' => ['label' => 'Audit: Activity Logs', 'permissions' => []],
        ];

        foreach ($this->permissions as $perm) {
            $name = (string) $perm->name;
            $parts = explode('.', $name);

            if (count($parts) >= 2) {
                $module = $parts[0];
                $subModule = $parts[1] ?? '';
                $groupKey = $module . ($subModule ? '.' . $subModule : '');

                if (! isset($groups[$groupKey])) {
                    $groups[$groupKey] = ['label' => ucwords(str_replace('.', ' ', $groupKey)), 'permissions' => []];
                }

                $groups[$groupKey]['permissions'][] = $perm;
            }
        }

        return $groups;
    }

    public function mount(): void
    {
        $this->branch_id = (int) (Branch::query()->where('is_active', true)->orderBy('name')->value('id') ?? 0);
        $this->resetForm();
    }

    protected function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'min:1', Rule::exists('branches', 'id')],
            'name' => ['required', 'string', 'max:100'],
            'selected_permissions' => ['array'],
        ];
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->selected_permissions = [];
        $this->editing_role_id = 0;
        $this->show_edit_modal = false;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can('rbac.manage'), 403);

        $data = $this->validate();

        $branchId = (int) $data['branch_id'];
        setPermissionsTeamId($branchId);

        $role = null;
        if ($this->editing_role_id > 0) {
            $role = Role::query()->whereKey($this->editing_role_id)->where('branch_id', $branchId)->firstOrFail();
            $role->name = $data['name'];
            $role->save();
        } else {
            $role = Role::query()->create([
                'name' => $data['name'],
                'guard_name' => 'web',
                'branch_id' => $branchId,
            ]);
        }

        $perms = collect($this->selected_permissions)
            ->map(fn ($v) => trim((string) $v))
            ->filter(fn ($v) => $v !== '')
            ->unique()
            ->values()
            ->all();

        $role->syncPermissions($perms);

        setPermissionsTeamId(null);

        session()->flash('status', 'Role saved successfully.');
        $this->resetForm();
    }

    public function openEditModal(int $id): void
    {
        abort_unless(auth()->user()?->can('rbac.manage'), 403);

        $branchId = (int) $this->branch_id;
        setPermissionsTeamId($branchId);

        $role = Role::query()->whereKey($id)->where('branch_id', $branchId)->firstOrFail();

        $this->editing_role_id = (int) $role->id;
        $this->name = (string) $role->name;
        $this->selected_permissions = $role->permissions()->pluck('name')->values()->all();
        $this->show_edit_modal = true;

        setPermissionsTeamId(null);
    }

    public function closeEditModal(): void
    {
        $this->resetForm();
    }

    public function openDeleteModal(int $id): void
    {
        abort_unless(auth()->user()?->can('rbac.manage'), 403);

        $branchId = (int) $this->branch_id;
        setPermissionsTeamId($branchId);

        $role = Role::query()->whereKey($id)->where('branch_id', $branchId)->firstOrFail();

        $this->pending_delete_id = (int) $role->id;
        $this->pending_delete_name = (string) $role->name;
        $this->show_delete_modal = true;

        setPermissionsTeamId(null);
    }

    public function closeDeleteModal(): void
    {
        $this->show_delete_modal = false;
        $this->pending_delete_id = 0;
        $this->pending_delete_name = '';
    }

    public function confirmDelete(): void
    {
        abort_unless(auth()->user()?->can('rbac.manage'), 403);

        $id = (int) $this->pending_delete_id;
        $this->closeDeleteModal();

        if ($id <= 0) {
            return;
        }

        $branchId = (int) $this->branch_id;
        setPermissionsTeamId($branchId);

        $role = Role::query()->whereKey($id)->where('branch_id', $branchId)->first();
        if ($role) {
            $role->delete();
        }

        setPermissionsTeamId(null);

        session()->flash('status', 'Role deleted successfully.');
        $this->resetForm();
    }

    public function render()
    {
        abort_unless(auth()->user()?->can('rbac.manage'), 403);

        $branches = Branch::query()->where('is_active', true)->orderBy('name')->get();

        $this->permissions = Permission::query()->orderBy('name')->get();

        $branchId = (int) $this->branch_id;
        setPermissionsTeamId($branchId);

        $roles = Role::query()
            ->where('branch_id', $branchId)
            ->orderBy('name')
            ->get();

        setPermissionsTeamId(null);

        return view('livewire.setup.roles-index', [
            'branches' => $branches,
            'roles' => $roles,
            'permissions' => $this->permissions,
        ]);
    }
}
