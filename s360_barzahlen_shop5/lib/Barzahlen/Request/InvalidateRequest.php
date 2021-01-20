<?php declare(strict_types = 1);

namespace Plugin\s360_barzahlen_shop5\lib\Barzahlen\Request;


class InvalidateRequest extends Request
{
    /**
     * @var string
     */
    protected $path = '/slips/%s/invalidate';

    /**
     * @var string
     */
    protected $method = 'POST';


    /**
     * @param string $slipId
     */
    public function __construct($slipId)
    {
        $this->parameters[] = $slipId;
    }
    
}