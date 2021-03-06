<?php
/**
 * This file is part of the O2System Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author         Steeve Andrian Salim
 * @copyright      Copyright (c) Steeve Andrian Salim
 */

// ------------------------------------------------------------------------

namespace O2System\Framework\Libraries\Ui\Components;

// ------------------------------------------------------------------------

use O2System\Framework\Libraries\Ui\Element;

/**
 * Class Embed
 *
 * @package O2System\Framework\Libraries\Ui\Components
 */
class Embed extends Element
{
    /**
     * Embed::RATIO_21BY9
     *
     * @var string
     */
    const RATIO_21BY9 = '21by9';

    /**
     * Embed::RATIO_16BY9
     *
     * @var string
     */
    const RATIO_16BY9 = '16by9';

    /**
     * Embed::RATIO_4BY3
     *
     * @var string
     */
    const RATIO_4BY3 = '4by3';

    /**
     * Embed::RATIO_1BY1
     *
     * @var string
     */
    const RATIO_1BY1 = '1by1';

    /**
     * Embed::$media
     *
     * @var \O2System\Framework\Libraries\Ui\Element
     */
    public $media;

    // ------------------------------------------------------------------------

    /**
     * Embed::__construct
     *
     * @param string $aspectRatio
     */
    public function __construct($aspectRatio = self::RATIO_16BY9)
    {
        parent::__construct('div');
        $this->attributes->addAttributeClass(['embed-responsive', 'embed-responsive-' . $aspectRatio]);

        $this->media = new Element('iframe');
        $this->media->attributes->addAttributeClass('embed-responsive-item');
        $this->media->attributes->addAttribute('allowfullscreen', 'allowfullscreen');
    }

    // ------------------------------------------------------------------------

    /**
     * Embed::setMedia
     *
     * @param string $src
     *
     * @return static
     */
    public function setMedia($src)
    {
        $src = str_replace(
            [
                'youtube.com/',
                'youtu.be',
                '?rel=0',
            ],
            [
                'youtube.com/embed/',
                'youtube.com/embed/',
                '',
            ],
            $src
        );

        if (strpos($src, 'youtube') !== false) {
            $src .= '?rel=0';
        }

        $this->media->attributes->addAttribute('src', $src);

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Embed::render
     *
     * @return string
     */
    public function render()
    {
        $output[] = $this->open();
        $output[] = $this->media;
        $output[] = $this->close();

        return implode(PHP_EOL, $output);
    }
}