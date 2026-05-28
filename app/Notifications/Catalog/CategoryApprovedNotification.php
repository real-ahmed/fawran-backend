<?php

namespace App\Notifications\Catalog;

class CategoryApprovedNotification extends CatalogApprovalNotification
{
    protected function itemType(): string
    {
        return 'Category';
    }
}
