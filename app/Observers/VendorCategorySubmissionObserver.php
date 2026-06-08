<?php

namespace App\Observers;

use App\Models\Catalog\VendorCategorySubmission;
use App\Observers\Catalog\CatalogSubmissionNotifier;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class VendorCategorySubmissionObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private readonly CatalogSubmissionNotifier $notifier) {}

    public function created(VendorCategorySubmission $vendorCategorySubmission): void
    {
        if ($vendorCategorySubmission->status !== 'pending') {
            return;
        }

        $vendorCategorySubmission->loadMissing(['category', 'vendor']);

        $this->notifier->notifyCategorySubmitted($vendorCategorySubmission->category, $vendorCategorySubmission->vendor);
    }
}
