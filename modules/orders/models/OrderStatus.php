<?php

declare(strict_types=1);

namespace modules\orders\models;

enum OrderStatus: int
{
    case Pending = 0;
    case InProgress = 1;
    case Completed = 2;
    case Canceled = 3;
    case Error = 4;

    /**
     * @param string $orderStatus
     * @return self
     * @throws \InvalidArgumentException
     */
    public static function fromString(string $orderStatus): self
    {
        return match (strtolower($orderStatus)) {
            strtolower(self::Pending->name) => self::Pending,
            strtolower(self::InProgress->name) => self::InProgress,
            strtolower(self::Completed->name) => self::Completed,
            strtolower(self::Canceled->name) => self::Canceled,
            strtolower(self::Error->name) => self::Error,
            default => throw new \InvalidArgumentException("Unknown order status '$orderStatus'"),
        };
    }
}
