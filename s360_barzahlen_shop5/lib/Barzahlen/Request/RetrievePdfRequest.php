<?php declare(strict_types = 1);

namespace Plugin\s360_barzahlen_shop5\lib\Barzahlen\Request;


class RetrievePdfRequest extends Request
{
    /**
     * @var string
     */
    protected $path = '/slips/%s/media/pdf';

    /**
     * @var string
     */
    protected $method = 'GET';


    /**
     * @param string $slipId
     */
    public function __construct($slipId)
    {
        $this->parameters[] = $slipId;
    }
    
}