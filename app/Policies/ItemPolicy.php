<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Item;
use App\Services\OutletContextService;

class ItemPolicy
{
    /**
     * Determine whether the admin can view any items.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->can('Manage Barang') || $admin->can('View Barang') || $admin->isKasirOutlet();
    }

    /**
     * Determine whether the admin can create items.
     */
    public function create(Admin $admin): bool
    {
        return $admin->can('Create Barang');
    }

    /**
     * Determine whether the admin can update the item.
     */
    public function update(Admin $admin, Item $item): bool
    {
        if (!$admin->can('Edit Barang')) {
            return false;
        }

        return $this->canAccessItemOutlet($admin, $item);
    }

    /**
     * Determine whether the admin can delete the item.
     */
    public function delete(Admin $admin, Item $item): bool
    {
        if (!$admin->can('Delete Barang')) {
            return false;
        }

        return $this->canAccessItemOutlet($admin, $item);
    }

    /**
     * Check if admin has access to the item's outlet (multi-tenant guard).
     */
    protected function canAccessItemOutlet(Admin $admin, Item $item): bool
    {
        if ($admin->isSuperAdmin()) {
            return true;
        }

        $koperasiId = OutletContextService::getKoperasiOutletId();

        if ($admin->isKasirKoperasi() || $admin->isKoordinatorCahayaMart()) {
            return $item->outlet_id === $koperasiId || is_null($item->outlet_id);
        }

        $authOutletIds = $admin->getOutletIds();
        if (!empty($authOutletIds)) {
            return in_array($item->outlet_id, $authOutletIds, true);
        }

        if ($admin->outlet_id) {
            return $item->outlet_id === $admin->outlet_id;
        }

        return true;
    }
}
