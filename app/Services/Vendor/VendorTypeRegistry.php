<?php

namespace App\Services\Vendor;

use App\Contracts\VendorTypeHandler;
use App\Enums\VendorType;
use InvalidArgumentException;

class VendorTypeRegistry
{
    /**
     * @var array<string, VendorTypeHandler>
     */
    private array $handlers = [];

    /**
     * Register a handler for a vendor type.
     */
    public function register(VendorType $type, VendorTypeHandler $handler): void
    {
        $this->handlers[$type->value] = $handler;
    }

    /**
     * Get the handler for a vendor type.
     *
     * @throws InvalidArgumentException
     */
    public function handler(VendorType $type): VendorTypeHandler
    {
        if (! isset($this->handlers[$type->value])) {
            throw new InvalidArgumentException("No handler registered for vendor type: {$type->value}");
        }

        return $this->handlers[$type->value];
    }

    /**
     * Check if a handler exists for a vendor type.
     */
    public function hasHandler(VendorType $type): bool
    {
        return isset($this->handlers[$type->value]);
    }
}
