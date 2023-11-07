<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model;

use Amasty\Rewards\Api\Data\HighlightInterface;

class Highlight extends \Magento\Framework\Model\AbstractModel implements HighlightInterface
{
    /**
     * {@inheritdoc}
     */
    public function isVisible()
    {
        return $this->_getData(self::VISIBLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setVisible($isVisible)
    {
        $this->setData(self::VISIBLE, $isVisible);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCaptionColor()
    {
        return $this->_getData(self::CAPTION_COLOR);
    }

    /**
     * {@inheritdoc}
     */
    public function setCaptionColor($captionColor)
    {
        $this->setData(self::CAPTION_COLOR, $captionColor);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCaptionText()
    {
        return $this->_getData(self::CAPTION_TEXT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCaptionText($captionText)
    {
        $this->setData(self::CAPTION_TEXT, $captionText);

        return $this;
    }

    public function getRegistrationLink(): ?string
    {
        return $this->_getData(self::REGISTRATION_LINK);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegistrationLink($link)
    {
        $this->setData(self::REGISTRATION_LINK, $link);

        return $this;
    }

    public function getNeedToChangeMessage(): int
    {
        return (int)$this->_getData(self::NEED_TO_CHANGE_MESSAGE);
    }

    public function setNeedToChangeMessage(int $messageType): void
    {
        $this->setData(self::NEED_TO_CHANGE_MESSAGE, $messageType);
    }
}
