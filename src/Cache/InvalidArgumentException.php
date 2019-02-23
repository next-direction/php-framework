<?php

namespace NextDirection\Framework\Cache;

use Psr\SimpleCache\InvalidArgumentException as PsrInvalidArgumentException;

class InvalidArgumentException extends \Exception implements PsrInvalidArgumentException {

}