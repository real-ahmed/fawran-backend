<?php

namespace App\Notifications\Catalog;

class MasterProductApprovedNotification extends CatalogApprovalNotification
{
    protected function itemType(): string
    {
        return 'Master Product';
    }
}
