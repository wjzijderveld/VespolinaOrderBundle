<?php

namespace Vespolina\OrderBundle\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Vespolina\OrderBundle\Model\SalesOrderManager;
use Vespolina\OrderBundle\Document\PaymentAgreement;


class OrderDocumentCreateTest extends WebTestCase
{
    protected $client;

    public function setUp()
    {
        $this->client = $this->createClient();
    }

    public function getKernel(array $options = array())
    {
        if (!self::$kernel) {
            self::$kernel = $this->createKernel($options);
            self::$kernel->boot();
        }

        return self::$kernel;
    }

    /**
     * @covers Vespolina\OrderBundle\Service\OrderService::create
     */
    public function testSalesOrderCreate()
    {

        $salesOrderManager = $this->getKernel()->getContainer()->get('vespolina_sales_order.sales_order_manager');


        $salesOrder = $salesOrderManager->createSalesOrder();

        $salesOrder->setOrderDate(new \DateTime());
        $salesOrder->setOrderState('awaiting_payment');

        $salesOrderItem1 = $salesOrderManager->createItem($salesOrder);

        $productA = $this->getMockForAbstractClass('Vespolina\ProductBundle\Model\Product');
        $productB = $this->getMockForAbstractClass('Vespolina\ProductBundle\Model\Product');

        $salesOrderItem1->setProduct($productA);
        $salesOrderItem1->setOrderedQuantity(10);
        $salesOrderItem1->setCustomerComment('please deliver one with a green print');
        $salesOrderItem1->setCustomerProductReference('PROMO_2012_T_SHIRT_CUSTOM_COLOR');
        $salesOrderItem1->setItemState('shipped');

        $this->assertEquals(count($salesOrder->getItems()), 1);
        $this->assertEquals(($salesOrderItem1->getOrderedQuantity()), 10);

        $salesOrderItem2 = $salesOrderManager->createItem($salesOrder);
        $salesOrderItem2->setProduct($productB);
        $salesOrderItem2->setOrderedQuantity(5);
        $salesOrderItem2->setItemState('shipped');

        $this->assertEquals(count($salesOrder->getItems()), 2);
        $this->assertEquals(($salesOrderItem2->getOrderedQuantity()), 5);

        //Payment
        $paymentAgreement = new PaymentAgreement();
        $paymentAgreement->setPaymentType('COD');
        $paymentAgreement->setPaymentState('unpaid');
        
        $salesOrder->setPaymentAgreement($paymentAgreement);
        $salesOrderManager->updateSalesOrder($salesOrder);
    }
}