<?php

namespace App\DTOs\Vendor\Order;

use App\Enums\SubOrderStatus;

class UpdateSubOrderStatusDTO
{
    public function __construct(
        public readonly int $subOrderId,
        public readonly int $vendorId,
        public readonly SubOrderStatus $status
    ) {}

    /**
     * @param  array{status: string}  $data
     */
    public static function fromValidated(array $data, int $subOrderId, int $vendorId): self
    {
        return new self(
            subOrderId: $subOrderId,
            vendorId: $vendorId,
            status: SubOrderStatus::from($data['status'])
        );
    }
}
