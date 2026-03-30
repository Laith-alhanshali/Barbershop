<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $authUser): bool
    {
        // يفتح صفحة المستخدمين إذا معه ViewAny أو View
        return $authUser->can('ViewAny:User')
            || $authUser->can('View:User')
            || $authUser->hasAnyRole(['admin', 'super_admin']);
    }


    public function view(User $authUser, User $model): bool
    {
        // إذا معه ViewAny يقدر يشوف أي مستخدم
        if ($authUser->can('ViewAny:User') || $authUser->hasAnyRole(['admin', 'super_admin'])) {
            return true;
        }

        // إذا معه View فقط يشوف نفسه
        if ($authUser->can('View:User')) {
            return $authUser->id === $model->id;
        }

        return false;
    }

    public function update(User $authUser, User $model): bool
    {
        // إذا معه ViewAny (أو أدمن) يقدر يعدل أي مستخدم
        if ($authUser->can('ViewAny:User') || $authUser->hasAnyRole(['admin', 'super_admin'])) {
            return $authUser->can('Update:User');
        }

        // إذا معه Update فقط، يعدل نفسه فقط
        if ($authUser->can('Update:User')) {
            return $authUser->id === $model->id;
        }

        return false;
    }

    public function create(User $authUser): bool
    {
        return $authUser->can('Create:User');
    }

    public function delete(User $authUser, ?User $model = null): bool
    {
        return $authUser->can('Delete:User');
    }

    public function restore(User $authUser, ?User $model = null): bool
    {
        return $authUser->can('Restore:User');
    }

    public function forceDelete(User $authUser, ?User $model = null): bool
    {
        return $authUser->can('ForceDelete:User');
    }

    public function forceDeleteAny(User $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:User');
    }

    public function restoreAny(User $authUser): bool
    {
        return $authUser->can('RestoreAny:User');
    }

    public function replicate(User $authUser, ?User $model = null): bool
    {
        return $authUser->can('Replicate:User');
    }

    public function reorder(User $authUser): bool
    {
        return $authUser->can('Reorder:User');
    }
}
