<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Block\Email;

class Expiring extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'email/expiring.phtml';

    /**
     * @return string
     */
    public function getExpirationString()
    {
        return $this->getData('expiration_string');
    }

    /**
     * @return array
     */
    public function getExpirationRows()
    {
        return $this->getData('deadlines') ?: [];
    }

    public function getFilledString($params)
    {
        $string = __($this->getExpirationString())->render();

        $string = str_replace('$amount', (float) $params['points'], $string);
        $string = str_replace('$earn_date', $params['earn_date'], $string);
        $string = str_replace('$days_left', $params['days_left'], $string);
        $string = str_replace('$expiration_date', $params['expiration_date'], $string);

        return $string;
    }
}
