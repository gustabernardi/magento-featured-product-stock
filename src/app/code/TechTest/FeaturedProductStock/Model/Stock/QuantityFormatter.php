<?php

declare(strict_types=1);

namespace TechTest\FeaturedProductStock\Model\Stock;

/**
 * Formats salable quantities for compact storefront display.
 */
class QuantityFormatter
{
    /**
     * Formats integer and decimal quantities without unnecessary trailing zeros.
     *
     * @param float $qty
     * @return string
     */
    public function format(float $qty): string
    {
        if (floor($qty) === $qty) {
            return number_format($qty, 0, '.', '');
        }

        return rtrim(rtrim(number_format($qty, 4, '.', ''), '0'), '.');
    }
}
