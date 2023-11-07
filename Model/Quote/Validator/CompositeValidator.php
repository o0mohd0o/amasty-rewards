<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Quote\Validator;

use Magento\Quote\Model\Quote;

class CompositeValidator implements ValidatorInterface
{
    /**
     * @var ValidatorInterface[]
     */
    private $validatorPool;

    public function __construct(
        array $validatorPool = []
    ) {
        $this->validatorPool = $validatorPool;
    }

    /**
     * @param Quote $quote
     * @param float|int|string $usedPoints
     * @param array &$pointsData
     */
    public function validate(Quote $quote, $usedPoints, array &$pointsData): void
    {
        foreach ($this->validatorPool as $validator) {
            if ($validator instanceof ValidatorInterface) {
                $validator->validate($quote, $usedPoints, $pointsData);
            } else {
                throw new \InvalidArgumentException(
                    'Type "' . get_class($validator) . '" is not instance on ' . ValidatorInterface::class
                );
            }
        }
    }
}
