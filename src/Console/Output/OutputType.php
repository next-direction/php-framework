<?php

namespace NextDirection\Framework\Console\Output;

use NextDirection\Framework\Common\EnumBase;

/**
 * Class OutputType
 *
 * Defines possible output messages to console
 *
 * @package NextDirection\Framework\Console
 */
abstract class OutputType extends EnumBase {
    public const NORMAL = 0;
    public const INFO = 1;
    public const SUCCESS = 2;
    public const WARNING = 3;
    public const ERROR = 4;
}