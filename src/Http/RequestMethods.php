<?php

namespace NextDirection\Framework\Http;

use NextDirection\Framework\Common\EnumBase;

class RequestMethods extends EnumBase {
    public const GET = 'GET';
    public const HEAD = 'HEAD';
    public const POST = 'POST';
    public const PUT = 'PUT';
    public const DELETE = 'DELETE';
    public const OPTIONS = 'OPTIONS';
    public const PATCH = 'PATCH';
}