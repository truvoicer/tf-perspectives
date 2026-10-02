<?php

namespace Truvoicer\TfPerspectives\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Truvoicer\TfPerspectives\Models\Permission;

trait PermissionTrait
{
    public function buildPermissionIds(array $permissionData): array
    {
        if (
            count(array_filter($permissionData, fn ($permissionId) => is_numeric($permissionId))) === count($permissionData)
        ) {
            return $permissionData;
        }

        if (
            count(array_filter($permissionData, fn ($permissionId) => is_string($permissionId))) === count($permissionData)
        ) {
            return array_map(
                fn ($permission) => Permission::where('name', $permission)->first()?->id,
                $permissionData
            );
        }

        throw new \Exception('Error building permission ids');
    }

    public function syncPermissions(BelongsToMany $permissions, array $permissionData): array
    {
        if (empty($permissionData)) {
            $permissions->detach();

            return [];
        }

        return $permissions->sync(
            $this->buildPermissionIds($permissionData)
        );
    }

    public function assignPermissions(BelongsToMany $permissions, array $permissionData): void
    {
        $permissions->attach(
            $this->buildPermissionIds($permissionData)
        );
    }
}
