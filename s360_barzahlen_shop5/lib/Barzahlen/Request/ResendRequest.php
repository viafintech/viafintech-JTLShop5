<?php

declare(strict_types=1);

namespace Plugin\s360_barzahlen_shop5\lib\Barzahlen\Request;

class ResendRequest extends Request
{

    /**
     * @var string
     */
    protected $path = '/slips/%s/resend/%s';

    /**
     * @var string
     */
    protected $method = 'POST';

    /**
     * @param string $slipId
     * @param string $type
     */
    public function __construct($slipId, $type)
    {
        $this->parameters[] = $slipId;
        $this->parameters[] = $type;
    }
}
