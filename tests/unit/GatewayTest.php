<?php

use Omnipay\Tatrabank\GatewayFactory;
use Omnipay\Tatrabank\Message\CompletePurchaseResponse;
use Omnipay\Tatrabank\Message\PurchaseResponse;

class GatewayTest extends PHPUnit_Framework_TestCase
{
    private $merchantId = '9999';
    private $merchantKey = '31323334353637383930313233343536373839303132333435363738393031323132333435363738393031323334353637383930313233343536373839303132';
    private $merchantKeyExample = '78787878787878787878787878787878787878787878787878787878787878787878787878787878787878787878787878787878787878787878787878787878';

    public function testPurchase()
    {
        $gateway = GatewayFactory::createInstance($this->merchantId, $this->merchantKey);

        $parameters = [
            'customerId'    => '42',
            'transactionId' => '12345',
            'amount'        => 6.0,
            'currency'      => 'EUR',
            'clientIp'      => '1.2.3.4',
            'returnUrl'     => 'http://example.com',
            'language'      => 'CZ',
        ];

        $request = $gateway->purchase($parameters);
        /** @var PurchaseResponse $response */
        $response = $request->send();

        $this->assertInstanceOf(Omnipay\Tatrabank\Message\AbstractRedirectResponse::class, $response);
        $this->assertTrue($response->isRedirect());
        $this->assertEquals('POST', $response->getRedirectMethod());
        $this->assertEquals('https://moja.tatrabanka.sk/cgi-bin/e-commerce/start/cardpay', $response->getRedirectUrl());
        $data = [
            'MID'       => $this->merchantId,
            'AMT'       => '6.00',
            'CURR'      => 978,
            'VS'        => '12345',
            'RURL'      => 'http://example.com',
            'IPC'       => '1.2.3.4',
            'NAME'      => '42',
            'TPAY'      => 'N',
            'AREDIR'    => 1,
            'TIMESTAMP' => str_pad($request->getTimestamp(), 14, "0", STR_PAD_LEFT),
            'LANG'      => 'cz',
        ];
        $data['HMAC'] = $gateway->getSignator()->sign($data, ['MID', 'AMT', 'CURR', 'VS', 'RURL', 'IPC', 'NAME', 'TIMESTAMP']);
        $this->assertEquals(
            $data,
            $response->getRedirectData()
        );
    }


    public function testCompletePurchase()
    {
        $gateway = GatewayFactory::createInstance($this->merchantId, $this->merchantKey);

        $parameters = [
            'data' => [
                'AMT'       => '6.00',
                'CURR'      => 978,
                'VS'        => '12345',
                'RES'       => 'OK',
                'AC'        => '123456',
                'TID'       => '87654321',
                'TIMESTAMP' => '01011970112233',
            ]
        ];

        /** @var CompletePurchaseResponse $response */
        $response = $gateway->completePurchase($parameters)->send();

        $this->assertInstanceOf(Omnipay\Tatrabank\Message\AbstractResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('12345', $response->getTransactionId());
        $this->assertEquals('87654321', $response->getTransactionReference());
        $this->assertEquals('123456', $response->getApprovalCode());
    }

    public function testFromExample()
    {
        $gateway = GatewayFactory::createInstance(9999, $this->merchantKeyExample);

        $parameters = [
            'customerId'    => 'Petr Novak',
            'transactionId' => '78945210',
            'amount'        => 20.78,
            'currency'      => 'EUR',
            'clientIp'      => '188.200.192.180',
            'returnUrl'     => 'https://www.obchodnik.sk/potvrdenie_platby.php',
            'language'      => 'CZ',
        ];

        $request = $gateway->purchase($parameters);
        $response = $request->send();

    }

}
