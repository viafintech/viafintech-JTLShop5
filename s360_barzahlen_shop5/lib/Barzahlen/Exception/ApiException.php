<?php

declare(strict_types=1);

namespace Plugin\s360_barzahlen_shop5\lib\Barzahlen\Exception;

class ApiException extends \Exception
{

    /**
     * @var string
     */
    protected $requestId;

    /**
     * @param string $message
     * @param string $requestId
     * @param array $aParams
     * @param bool $bLog
     */
    public function __construct($message, $requestId = 'N/A', $aParams = array(), $bLog = false)
    {
        parent::__construct($message);
        $this->requestId = $requestId;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return __CLASS__ . ": {$this->message} - RequestId: {$this->requestId}";
    }
}
