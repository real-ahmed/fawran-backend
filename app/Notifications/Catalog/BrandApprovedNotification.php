<?php

namespace App\Notifications\Catalog;

class BrandApprovedNotification extends CatalogApprovalNotification
{
    protected function itemType(): string
    {
        return 'Brand';
    }
}
