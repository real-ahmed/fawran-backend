<?php

namespace App\Enums;

class OrderStatusTransition
{
    /**
     * Define the valid status transitions.
     * key: current status, value: array of allowed next statuses
     */
    public static function allowedTransitions(): array
    {
        return [
            OrderStatus::Pending->value => [
                OrderStatus::Processing->value,
                OrderStatus::Cancelled->value,
            ],
            OrderStatus::Processing->value => [
                OrderStatus::OutForDelivery->value,
                OrderStatus::Cancelled->value,
            ],
            OrderStatus::OutForDelivery->value => [
                OrderStatus::Delivered->value,
                OrderStatus::Cancelled->value,
            ],
            OrderStatus::Delivered->value => [],
            OrderStatus::Cancelled->value => [],
        ];
    }

    public static function canTransition(string $from, string $to): bool
    {
        $transitions = self::allowedTransitions();

        return isset($transitions[$from]) && in_array($to, $transitions[$from]);
    }
}
