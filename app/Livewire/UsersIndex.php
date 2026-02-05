<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Livewire\Component;

class UsersIndex extends Component
{
    public int $editingId = 0;

    public string $name = '';
    public string $email = '';
    public int $branch_id = 0;

    public ?string $password = null;
    public ?string $password_confirmation = null;

    public string $search = '';

    public bool $show_delete_modal = false;
    public int $pending_delete_id = 0;
    public string $pending_delete_name = '';

    public bool $show_edit_modal = false;

    protected function rules(): array
    {
        $emailRule = Rule::unique('users', 'email');
        if ($this->editingId > 0) {
            $emailRule = $emailRule->ignore($this->editingId);
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', $emailRule],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ];
    }

    public function resetForm(): void
    {
        $this->editingId = 0;
        $this->name = '';
        $this->email = '';
        $this->branch_id = (int) (Branch::query()->where('is_active', true)->orderBy('name')->value('id') ?? 0);
        $this->password = null;
        $this->password_confirmation = null;
        $this->show_edit_modal = false;
        $this->resetErrorBag();
    }

    public function mount(): void
    {
        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $user = User::query()->where('role', 'branch_admin')->findOrFail($id);

        $this->editingId = (int) $user->id;
        $this->name = (string) $user->name;
        $this->email = (string) $user->email;
        $this->branch_id = (int) ($user->branch_id ?? 0);
        $this->password = null;
        $this->password_confirmation = null;
        $this->resetErrorBag();
    }

    public function openEditModal(int $id): void
    {
        $this->edit($id);
        $this->show_edit_modal = true;
    }

    public function closeEditModal(): void
    {
        $this->show_edit_modal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->editingId > 0) {
            $before = User::query()->find($this->editingId);
            $payload = [
                'name' => $data['name'],
                'email' => $data['email'],
                'branch_id' => (int) $data['branch_id'],
                'role' => 'branch_admin',
            ];

            if ($data['password'] !== null && $data['password'] !== '') {
                $payload['password'] = Hash::make((string) $data['password']);
            }

            User::query()->whereKey($this->editingId)->update($payload);

            $after = User::query()->find($this->editingId);
            ActivityLogger::log(
                'user.updated',
                $after,
                'User updated',
                [
                    'before' => $before ? $before->only(['name', 'email', 'branch_id', 'role']) : null,
                    'after' => $after ? $after->only(['name', 'email', 'branch_id', 'role']) : null,
                ],
                $after?->branch_id ? (int) $after->branch_id : null
            );

            session()->flash('status', 'User updated successfully.');
            $this->resetForm();
            return;
        }

        $tempPassword = Str::random(12);

        $newUser = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'branch_id' => (int) $data['branch_id'],
            'role' => 'branch_admin',
            'password' => Hash::make($tempPassword),
        ]);

        ActivityLogger::log(
            'user.created',
            $newUser,
            'User created',
            [
                'name' => $newUser->name,
                'email' => $newUser->email,
                'branch_id' => $newUser->branch_id,
                'role' => $newUser->role,
            ],
            $newUser->branch_id ? (int) $newUser->branch_id : null
        );

        session()->flash('status', 'User created successfully.');
        session()->flash('temp_password', $tempPassword);
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        if ((int) auth()->id() === (int) $id) {
            session()->flash('status', 'You cannot delete your own account.');
            return;
        }

        $user = User::query()->where('role', 'branch_admin')->findOrFail($id);
        $snapshot = $user->only(['id', 'name', 'email', 'branch_id', 'role']);
        $user->delete();

        ActivityLogger::log(
            'user.deleted',
            ['type' => User::class, 'id' => (int) $id],
            'User deleted',
            ['user' => $snapshot],
            isset($snapshot['branch_id']) ? (int) $snapshot['branch_id'] : null
        );

        if ($this->editingId === (int) $id) {
            $this->resetForm();
        }

        session()->flash('status', 'User deleted successfully.');
    }

    public function openDeleteModal(int $id): void
    {
        if ((int) auth()->id() === (int) $id) {
            session()->flash('status', 'You cannot delete your own account.');
            return;
        }

        $user = User::query()->where('role', 'branch_admin')->findOrFail($id);

        $this->pending_delete_id = (int) $user->id;
        $this->pending_delete_name = (string) $user->name;
        $this->show_delete_modal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->show_delete_modal = false;
        $this->pending_delete_id = 0;
        $this->pending_delete_name = '';
    }

    public function confirmDelete(): void
    {
        $id = (int) $this->pending_delete_id;
        $this->closeDeleteModal();

        if ($id > 0) {
            $this->delete($id);
        }
    }

    public function render()
    {
        $branches = Branch::query()->where('is_active', true)->orderBy('name')->get();

        $users = User::query()
            ->with(['branch'])
            ->where('role', 'branch_admin')
            ->when($this->search !== '', function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->orderBy('name')
            ->get();

        return view('livewire.users-index', [
            'branches' => $branches,
            'users' => $users,
        ]);
    }
}
