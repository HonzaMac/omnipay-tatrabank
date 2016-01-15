<?php

namespace Omnipay\Tatrabank\Sign;

class DataSignator
{
    /** @var Preparer */
    private $preparer;

    /** @var Signator */
    private $signator;

    function __construct(Preparer $preparer, Signator $signator)
    {
        $this->preparer = $preparer;
        $this->signator = $signator;
    }

    /**
     * @param array $data
     * @param array $arrayKeys
     * @return string
     */
    public function sign(array $data, array $arrayKeys)
    {
        $strToSign = $this->preparer->getStringToSign($data, $arrayKeys);
        return $this->signator->sign($strToSign);
    }
}