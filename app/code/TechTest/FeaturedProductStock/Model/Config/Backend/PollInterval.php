<?php

declare(strict_types=1);

namespace TechTest\FeaturedProductStock\Model\Config\Backend;

use TechTest\FeaturedProductStock\Model\Config;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;

/**
 * Validates the stock refresh interval before saving system config.
 */
class PollInterval extends Value
{
    /**
     * Ensures that the interval is an integer greater than or equal to the minimum.
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave(): self
    {
        $value = trim((string) $this->getValue());

        if ($value === '' || !ctype_digit($value)) {
            throw new LocalizedException(
                __('Please enter the stock refresh interval as an integer number of milliseconds.')
            );
        }

        $interval = (int) $value;

        if ($interval < Config::MIN_POLL_INTERVAL) {
            throw new LocalizedException(
                __('The stock refresh interval must be at least %1 milliseconds.', Config::MIN_POLL_INTERVAL)
            );
        }

        $this->setValue((string) $interval);

        return parent::beforeSave();
    }
}
