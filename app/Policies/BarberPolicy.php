<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Barber;
use Illuminate\Auth\Access\HandlesAuthorization;

class BarberPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $authUser): bool
    {
        // يفتح صفحة الحلاقين إذا معه ViewAny أو View
        return $authUser->can('ViewAny:Barber')
            || $authUser->can('View:Barber')
            || $authUser->hasAnyRole(['admin', 'super_admin']);
    }

    public function view(User $authUser, Barber $barber): bool
    {
        // إذا معه ViewAny أو أدمن => يشوف أي حلاق
        if ($authUser->can('ViewAny:Barber') || $authUser->hasAnyRole(['admin', 'super_admin'])) {
            return true;
        }

        // إذا معه View فقط => يشوف نفسه فقط
        if ($authUser->can('View:Barber')) {
            return $barber->user_id === $authUser->id;
        }

        return false;
    }

    public function create(User $authUser): bool
    {
        return $authUser->can('Create:Barber');
    }

    public function update(User $authUser, Barber $barber): bool
    {
        return $authUser->can('Update:Barber');
    }

    public function delete(User $authUser, Barber $barber): bool
    {
        return $authUser->can('Delete:Barber');
    }

    public function restore(User $authUser, Barber $barber): bool
    {
        return $authUser->can('Restore:Barber');
    }

    public function forceDelete(User $authUser, Barber $barber): bool
    {
        return $authUser->can('ForceDelete:Barber');
    }

    public function forceDeleteAny(User $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Barber');
    }

    public function restoreAny(User $authUser): bool
    {
        return $authUser->can('RestoreAny:Barber');
    }

    public function replicate(User $authUser, Barber $barber): bool
    {
        return $authUser->can('Replicate:Barber');
    }

    public function reorder(User $authUser): bool
    {
        return $authUser->can('Reorder:Barber');
    }
}
