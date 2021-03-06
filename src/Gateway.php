<?php

namespace Omnipay\Tatrabank;

use Omnipay\Common\AbstractGateway;
use Omnipay\Tatrabank\Message\AbstractRequest;
use Omnipay\Tatrabank\Message\CompletePurchaseRequest;
use Omnipay\Tatrabank\Message\PurchaseRequest;
use Omnipay\Tatrabank\Sign\DataSignator;

/**
 * TatraBank payment gateway
 *
 * @package Omnipay\Tatrabank
 */
class Gateway extends AbstractGateway
{
    /** @var DataSignator */
    private $signator;

    /** @var \DateTimeZone */
    private $timezone;

    /**
     * Get gateway display name
     */
    public function getName()
    {
        return 'TatraBank';
    }

    /**
     * @param DataSignator $signator
     */
    public function setSignator(DataSignator $signator)
    {
        $this->signator = $signator;
    }

    /**
     * @return DataSignator
     */
    public function getSignator()
    {
        return $this->signator;
    }

    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }

    /**
     * @param string $id
     */
    public function setMerchantId($value)
    {
        return $this->setParameter('merchantId', $value);
    }

    /**
     * Get available languages
     */
    public static function getLanguages()
    {
        return Enum\Language::getNames();
    }

    /**
     * Get available currencies
     */
    public static function getCurrencies()
    {
        return Enum\Currency::getNames();
    }

    public static function getEndpoint()
    {
        return 'https://moja.tatrabanka.sk/cgi-bin/e-commerce/start/cardpay';
    }

    protected function createRequest($class, array $parameters)
    {
        if (!($this->signator instanceof DataSignator)) {
            throw new \Exception('Cannot create request, Signator is not set');
        }

        if ($this->timezone === null) {
            $this->timezone = new \DateTimeZone('UTC');
        }

        /** @var AbstractRequest $request */
        $request = parent::createRequest($class, $parameters);
        $request->setSignator($this->signator);
        $request->setTimestamp((new \DateTime(null, $this->timezone))->format('dmYHis'));

        return $request;
    }

    /**
     * @param array $parameters
     * @return PurchaseRequest
     */
    public function purchase(array $parameters = array())
    {
        return $this->createRequest(PurchaseRequest::class, $parameters);
    }

    /**
     * @param array $parameters
     * @return CompletePurchaseRequest
     */
    public function completePurchase(array $parameters = array())
    {
        return $this->createRequest(CompletePurchaseRequest::class, $parameters);
    }

}