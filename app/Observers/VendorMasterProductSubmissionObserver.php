<?php

namespace App\Observers;

use App\Models\Product\VendorMasterProductSubmission;
use App\Observers\Catalog\CatalogSubmissionNotifier;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class VendorMasterProductSubmissionObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private readonly CatalogSubmissionNotifier $notifier) {}

    public function created(VendorMasterProductSubmission $vendorMasterProductSubmission): void
    {
        if ($vendorMasterProductSubmission->status !== 'pending') {
            return;
        }

        $vendorMasterProductSubmission->loadMissing(['masterProduct', 'vendor']);

        $this->notifier->notifyMasterProductSubmitted(
            $vendorMasterProductSubmission->masterProduct,
            $vendorMasterProductSubmission->vendor
        );
    }
}
