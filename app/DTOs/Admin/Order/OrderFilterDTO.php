<?php

namespace App\DTOs\Admin\Order;

use Illuminate\Http\Request;

readonly class OrderFilterDTO
{
    public function __construct(
        public ?string $search = null,
        public ?string $status = null,
        public ?string $order_type = null,
        public ?string $date_from = null,
        public ?string $date_to = null,
        public ?int $customer_id = null,
        public ?int $vendor_id = null,
        public ?int $courier_id = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->query('search') ?? null,
            status: $request->query('status') ?? null,
            order_type: $request->query('order_type') ?? null,
            date_from: $request->query('date_from') ?? null,
            date_to: $request->query('date_to') ?? null,
            customer_id: $request->query('customer_id') !== null ? (int) $request->query('customer_id') : null,
            vendor_id: $request->query('vendor_id') !== null ? (int) $request->query('vendor_id') : null,
            courier_id: $request->query('courier_id') !== null ? (int) $request->query('courier_id') : null,
        );
    }
}
