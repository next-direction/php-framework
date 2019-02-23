<?php

namespace NextDirection\Framework\Console\Output;

use NextDirection\Framework\Common\EnumBase;

/**
 * Class OutputStyle
 *
 * Defines possible output styles of messages
 *
 * @package NextDirection\Framework\Console
 */
abstract class OutputStyle extends EnumBase {
    public const BOLD = 0;
    public const DIMMED = 1;
    public const UNDERLINED = 2;
    public const ITALIC = 3;
    public const STRIKE = 4;
}