<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

/**
 * Lets an administrator create lecturer/student/admin accounts directly and
 * assign or change their role, bypassing the normal self-registration and
 * lecturer-application flows when an account needs to exist right away.
 *
 * New accounts default to a random, never-shared password and are sent
 * Laravel's standard "reset your password" email to set their own. An admin
 * may instead type a password when creating or editing an account (for users
 * whose email can't receive the link); changing one signs that user out
 * everywhere. Only a super_administrator may assign the
 * super_administrator role, or change the role of an existing one, so a
 * regular administrator can't escalate or demote the platform's top tier.
 */
#[Layout('layouts.app')]
class Users extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    public string $roleFilter = '';

    public string $name = '';

    public string $email = '';

    public string $role = 'student';

    public string $preferredLanguage = 'en';

    public string $newPassword = '';

    public string $newPassword_confirmation = '';

    public ?int $changingRoleId = null;

    public string $newRole = '';

    public ?int $editingId = null;

    public string $editingName = '';

    public string $editingEmail = '';

    public string $editingPreferredLanguage = 'en';

    public string $editingPassword = '';

    public string $editingPassword_confirmation = '';

    public bool $showBulkImport = false;

    public string $bulkImportText = '';

    /** @var array<int, array{line: string, error: string}> */
    public array $bulkImportFailures = [];

    public int $bulkImportCreated = 0;

    protected const BULK_IMPORT_MAX_ROWS = 500;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    protected function assignableRoles(): array
    {
        $roles = Role::orderBy('name')->pluck('name')->all();

        if (! Auth::user()->hasRole('super_administrator')) {
            $roles = array_values(array_diff($roles, ['super_administrator']));
        }

        return $roles;
    }

    /**
     * Shared guard for every action that targets another account: never
     * yourself (no accidental self-demotion/suspension/deletion), and the
     * super_administrator tier is only manageable by its own tier.
     */
    protected function guardTarget(User $user): void
    {
        abort_if($user->id === Auth::id(), 403);
        abort_if($user->hasRole('super_administrator') && ! Auth::user()->hasRole('super_administrator'), 403);
    }

    public function create(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string', Rule::in($this->assignableRoles())],
            'preferredLanguage' => ['required', 'string', 'in:'.implode(',', array_keys(config('platform.locales')))],
            'newPassword' => ['nullable', 'string', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $this->createOneUser(
            $validated['name'],
            $validated['email'],
            $validated['role'],
            $validated['preferredLanguage'],
            $validated['newPassword'] ?: null,
        );

        $this->reset(['name', 'email', 'preferredLanguage', 'newPassword', 'newPassword_confirmation']);
        $this->role = 'student';
        $this->preferredLanguage = 'en';
    }

    /**
     * Shared by both the single-account form and bulk import — every path
     * that creates an account goes through the exact same steps: random
     * unshared password, pre-verified email, profile row, role assignment,
     * audit trail, and the same "set your own password" reset email.
     */
    protected function createOneUser(string $name, string $email, string $role, string $preferredLanguage, ?string $password = null): User
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password ?? Str::password(32)),
        ]);

        // email_verified_at is intentionally outside the model's #[Fillable(...)]
        // list (mass-assignment would let a public form set it), so it has to be
        // set explicitly here for an admin-created account.
        $user->forceFill(['email_verified_at' => now()])->save();

        $user->profile()->create(['preferred_language' => $preferredLanguage]);

        $user->assignRole($role);

        AuditLog::record('user.created', subject: $user, new: ['email' => $user->email, 'role' => $role, 'password_set_by_admin' => $password !== null]);

        // An admin-chosen password replaces the "set your own" email flow.
        if ($password === null) {
            Password::sendResetLink(['email' => $user->email]);
        }

        return $user;
    }

    public function toggleBulkImport(): void
    {
        $this->showBulkImport = ! $this->showBulkImport;
        $this->reset(['bulkImportText', 'bulkImportFailures', 'bulkImportCreated']);
    }

    /**
     * Accepts pasted lines of "name, email, role" (role optional, defaults
     * to student). Each row is validated and created independently — one bad
     * row is reported and skipped rather than aborting the whole batch, so a
     * long paste doesn't fail entirely over a single typo.
     */
    public function runBulkImport(): void
    {
        $this->bulkImportFailures = [];
        $this->bulkImportCreated = 0;

        $lines = array_values(array_filter(array_map('trim', explode("\n", $this->bulkImportText)), fn ($line) => $line !== ''));

        if (empty($lines)) {
            $this->addError('bulkImportText', __('Paste at least one row first.'));

            return;
        }

        if (count($lines) > self::BULK_IMPORT_MAX_ROWS) {
            $this->addError('bulkImportText', __('Import up to :max rows at a time — split larger lists into batches.', ['max' => self::BULK_IMPORT_MAX_ROWS]));

            return;
        }

        $assignableRoles = $this->assignableRoles();
        $seenEmails = [];

        foreach ($lines as $line) {
            $fields = array_map('trim', explode(',', $line));
            $name = $fields[0] ?? '';
            $email = $fields[1] ?? '';
            $role = $fields[2] ?? 'student';

            $emailKey = strtolower($email);

            $rowValidator = Validator::make(
                ['name' => $name, 'email' => $email, 'role' => $role],
                [
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                    'role' => ['required', 'string', Rule::in($assignableRoles)],
                ],
            );

            if ($email !== '' && in_array($emailKey, $seenEmails, true)) {
                $this->bulkImportFailures[] = ['line' => $line, 'error' => __('Duplicate email within this import.')];

                continue;
            }

            if ($rowValidator->fails()) {
                $this->bulkImportFailures[] = ['line' => $line, 'error' => $rowValidator->errors()->first()];

                continue;
            }

            $this->createOneUser($name, $email, $role, 'en');
            $seenEmails[] = $emailKey;
            $this->bulkImportCreated++;
        }

        $this->bulkImportText = '';
    }

    public function startChangeRole(int $id): void
    {
        $user = User::with('roles')->findOrFail($id);

        $this->guardTarget($user);

        $this->reset(['editingId']);
        $this->changingRoleId = $id;
        $this->newRole = $user->roles->first()?->name ?? '';
    }

    public function cancelChangeRole(): void
    {
        $this->reset(['changingRoleId', 'newRole']);
    }

    public function confirmChangeRole(): void
    {
        $user = User::with('roles')->findOrFail($this->changingRoleId);

        $this->guardTarget($user);

        $validated = $this->validate([
            'newRole' => ['required', 'string', Rule::in($this->assignableRoles())],
        ]);

        $old = ['role' => $user->roles->first()?->name];

        $user->syncRoles([$validated['newRole']]);

        AuditLog::record('user.role_changed', subject: $user, old: $old, new: ['role' => $validated['newRole']]);

        $this->cancelChangeRole();
    }

    public function startEdit(int $id): void
    {
        $user = User::with('profile')->findOrFail($id);

        $this->guardTarget($user);

        $this->reset(['changingRoleId', 'newRole', 'editingPassword', 'editingPassword_confirmation']);
        $this->resetErrorBag();
        $this->editingId = $id;
        $this->editingName = $user->name;
        $this->editingEmail = $user->email;
        $this->editingPreferredLanguage = $user->profile?->preferred_language ?? 'en';
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'editingName', 'editingEmail', 'editingPassword', 'editingPassword_confirmation']);
        $this->editingPreferredLanguage = 'en';
        $this->resetErrorBag();
    }

    public function confirmEdit(): void
    {
        $user = User::findOrFail($this->editingId);

        $this->guardTarget($user);

        $validated = $this->validate([
            'editingName' => ['required', 'string', 'max:255'],
            'editingEmail' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'editingPreferredLanguage' => ['required', 'string', 'in:'.implode(',', array_keys(config('platform.locales')))],
            'editingPassword' => ['nullable', 'string', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $old = ['name' => $user->name, 'email' => $user->email];

        $user->update([
            'name' => $validated['editingName'],
            'email' => $validated['editingEmail'],
        ]);

        $user->profile()->updateOrCreate([], ['preferred_language' => $validated['editingPreferredLanguage']]);

        AuditLog::record('user.updated', subject: $user, old: $old, new: ['name' => $user->name, 'email' => $user->email]);

        if (! empty($validated['editingPassword'])) {
            $user->forceFill([
                'password' => Hash::make($validated['editingPassword']),
                'remember_token' => Str::random(60),
            ])->save();

            // Sign the user out everywhere so the old password stops working immediately.
            if (config('session.driver') === 'database') {
                DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
            }

            AuditLog::record('user.password_changed', subject: $user);
        }

        $this->cancelEdit();
    }

    public function suspend(int $id): void
    {
        $user = User::findOrFail($id);

        $this->guardTarget($user);

        $user->forceFill(['suspended_at' => now()])->save();

        AuditLog::record('user.suspended', subject: $user);
    }

    public function unsuspend(int $id): void
    {
        $user = User::findOrFail($id);

        $this->guardTarget($user);

        $user->forceFill(['suspended_at' => null])->save();

        AuditLog::record('user.unsuspended', subject: $user);
    }

    public function delete(int $id): void
    {
        $user = User::findOrFail($id);

        $this->guardTarget($user);

        // Financial, teaching, and learning history must stay reconstructable:
        // a user who ever paid, subscribed, enrolled, taught, or opened a
        // ticket keeps their account (suspend instead of delete). Deletion is
        // for accounts that never really did anything.
        $hasHistory = $user->transactions()->exists()
            || $user->subscriptions()->exists()
            || $user->enrolments()->exists()
            || $user->createdCourses()->exists()
            || $user->coursesTeaching()->exists()
            || $user->supportTickets()->exists();

        if ($hasHistory) {
            $this->addError('delete', __('This user has platform history and cannot be deleted. Suspend the account instead.'));

            return;
        }

        try {
            $user->notifications()->delete();
            $user->profile()->delete();
            $user->lecturerProfile()->delete();
            $user->delete();
        } catch (QueryException) {
            // Some other record still references this user (a relation the
            // checks above don't cover) — fall back to the same guidance.
            $this->addError('delete', __('This user has platform history and cannot be deleted. Suspend the account instead.'));

            return;
        }

        AuditLog::record('user.deleted', old: ['name' => $user->name, 'email' => $user->email]);
    }

    public function render()
    {
        $users = User::query()
            ->with('roles')
            ->when($this->search, fn ($query) => $query->where(
                fn ($q) => $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')
            ))
            ->when($this->roleFilter, fn ($query) => $query->role($this->roleFilter))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.users', [
            'users' => $users,
            'allRoles' => Role::orderBy('name')->pluck('name'),
            'assignableRoles' => $this->assignableRoles(),
        ]);
    }
}
