<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Api\Data;

interface HighlightInterface
{
    public const VISIBLE = 'visible';
    public const CAPTION_COLOR = 'caption_color';
    public const CAPTION_TEXT = 'caption_text';
    public const REGISTRATION_LINK = 'registration_link';
    public const NEED_TO_CHANGE_MESSAGE = 'need_to_change_message';

    /**
     * @return bool
     */
    public function isVisible();

    /**
     * @param bool $visible
     *
     * @return HighlightInterface
     */
    public function setVisible($visible);

    /**
     * @return string|null
     */
    public function getCaptionColor();

    /**
     * @param string $captionColor
     *
     * @return HighlightInterface
     */
    public function setCaptionColor($captionColor);

    /**
     * @return string|null
     */
    public function getCaptionText();

    /**
     * @param string $captionText
     *
     * @return HighlightInterface
     */
    public function setCaptionText($captionText);

    /**
     * @return string|null
     */
    public function getRegistrationLink(): ?string;

    /**
     * @param $link
     * @return mixed
     */
    public function setRegistrationLink($link);

    /**
     * @return int
     */
    public function getNeedToChangeMessage(): int;

    /**
     * @param int $messageType
     * @return void
     */
    public function setNeedToChangeMessage(int $messageType): void;
}
