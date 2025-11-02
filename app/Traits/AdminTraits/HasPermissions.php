<?php

namespace App\Traits\AdminTraits;

use Illuminate\Support\Collection;

trait HasPermissions
{
    /**
     * Get all permissions of user.
     *
     * @return mixed
     */
    public function allPermissions(): Collection
    {
        // return \Cache::rememberForever("user_permissions_{$this->id}", function () {
        return $this->roles()
            ->with('permissions:id,slug') // load only needed fields
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->merge($this->permissions) // direct permissions
            ->unique('id')
            ->values();
        // });    
    }

    public function cachedPermissions()
    {
        // return cache()->remember("admin_user_permissions_{$this->id}", 600, function () {
        return $this->roles()->with('permissions')->get()
            ->pluck('permissions')->flatten()->pluck('slug')->unique();
        // });
    }

    /**
     * Check if user has permission.
     *
     * @param $ability
     * @param array $arguments
     *
     * @return bool
     */
    public function can($ability, $arguments = []): bool
    {

        // Super admin check
        if ($this->isAdministrator()) {
            return true;
        }

        $permissions = $this->cachedPermissions();

        // Allow everything if wildcard
        if ($permissions->contains('*') || empty($ability)) {
            return true;
        }

        return $permissions->contains($ability);
    }

    /**
     * Check if user has no permission.
     *
     * @param $permission
     *
     * @return bool
     */
    public function cannot(string $permission): bool
    {
        return !$this->can($permission);
    }

    /**
     * Check if user is administrator.
     *
     * @return mixed
     */
    public function isAdministrator(): bool
    {
        return $this->isRole('administrator') || $this->isRole('developer');
    }

    /**
     * Check if user is $role.
     *
     * @param string $role
     *
     * @return mixed
     */
    public function isRole(string $role): bool
    {
        return $this->roles->pluck('slug')->contains($role);
    }

    /**
     * Check if user in $roles.
     *
     * @param array $roles
     *
     * @return mixed
     */
    public function inRoles(array $roles = []): bool
    {
        return $this->roles->pluck('slug')->intersect($roles)->isNotEmpty();
    }

    /**
     * If visible for roles.
     *
     * @param $roles
     *
     * @return bool
     */
    public function visible(array $roles = []): bool
    {
        if (empty($roles)) {
            return true;
        }

        $roles = array_column($roles, 'slug');

        return $this->inRoles($roles) || $this->isAdministrator();
    }

    /**
     * Clear cached permissions (after role/permission update).
     */
    public function forgetCachedPermissions(): void
    {
        \Cache::forget("admin_user_permissions_{$this->id}");
        \Cache::forget("user_permissions_{$this->id}");
    }

    /**
     * Detach models from the relationship.
     *
     * @return void
     */
    protected static function bootHasPermissions()
    {
        static::saved(function ($model) {
            $model->forgetCachedPermissions();
        });

        static::deleting(function ($model) {
            $model->roles()->detach();
            $model->permissions()->detach();
            $model->forgetCachedPermissions();
        });
    }
}
