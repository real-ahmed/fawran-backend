<?php

namespace App\Observers;

use App\Models\Catalog\VendorBrandSubmission;
use App\Observers\Catalog\CatalogSubmissionNotifier;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class VendorBrandSubmissionObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private readonly CatalogSubmissionNotifier $notifier) {}

    public function created(VendorBrandSubmission $vendorBrandSubmission): void
    {
        if ($vendorBrandSubmission->status !== 'pending') {
            return;
        }

        $vendorBrandSubmission->loadMissing(['brand', 'vendor']);

        $this->notifier->notifyBrandSubmitted($vendorBrandSubmission->brand, $vendorBrandSubmission->vendor);
    }
}
