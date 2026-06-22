<?php

namespace App\Http\Controllers\Concerns;

/**
 * Trait AdminScoped
 *
 * Provides a helper to resolve the "root" admin ID for any authenticated user.
 *
 * Role hierarchy:
 *   master  → top-level (no admin_id scope needed)
 *   admin   → root admin (admin_id = auth()->id())
 *   finance/noc/teknisi → sub-user (admin_id = parent_admin_id)
 */
trait AdminScoped
{
    /**
     * Return the admin_id that should be used to scope queries.
     * Returns null when the logged-in user is 'master' (sees all).
     */
    protected function resolveAdminId(): ?int
    {
        $user = auth()->user();

        if ($user->role === 'master') {
            return null; // master sees everything
        }

        if ($user->role === 'admin') {
            return $user->id;
        }

        // finance / noc / teknisi  → use their parent admin
        return $user->parent_admin_id;
    }

    /**
     * Scope an Eloquent query to the current admin's data.
     * If adminId is null (master), no scope is applied.
     */
    protected function scopeToAdmin($query, ?int $adminId = -1)
    {
        if ($adminId === -1) {
            $adminId = $this->resolveAdminId();
        }

        if ($adminId !== null) {
            $query->where('admin_id', $adminId);
        }

        return $query;
    }
}
