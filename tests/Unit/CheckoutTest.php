<?php

namespace Tests\Unit;

use Domain\Services\CheckoutService;
use PHPUnit\Framework\TestCase;

class CheckoutTest extends TestCase
{
    /**
     * Test checkout service
     * 
     * @return void
     */
    public function test_checkout()
    {
        $promoRules = $this->getPromotionRules();
        $checkout = new CheckoutService($promoRules);

        $item = $this->getCartProduct('ISCAIR001N');
        $checkout->scan($item);

        $item = $this->getCartProduct('ISTESI015');
        $checkout->scan($item);

        $item = $this->getCartProduct('ISCFR038');
        $checkout->scan($item);

        $this->assertEquals(271.49, $checkout->getTotal());
    }

    private function getCartProduct(string $productCode): array
    {
        $cart = $this->getCart();
        return $cart[$productCode];
    }

    private function getCart(): array
    {
        return [
            'ISCAIR001N' => [
                'product_code' => 'ISCAIR001N',
                'name' => 'bathroom basin 400mm',
                'qty' => 3,
                'price' => 105,
                'discount' => 20 //105*3=315-105=210-42=168
            ],
            'ISTESI015' => [
                'product_code' => 'ISTESI015',
                'name' => 'cloakroom basin mixer tap',
                'qty' => 1,
                'price' => 125,
                'discount' => 20 //125-37,5=87,5
            ],
            'ISCFR038' => [
                'product_code' => 'ISCFR038',
                'name' => 'bathroom basin 400mm',
                'qty' => 1,
                'price' => 19.99,
                'discount' => 20 //19.99-3,998=15,992
            ]
        ];
    }

    private function getPromotionRules(): array
    {
        return [
            'ISCAIR001N' => ['qty', 3, 1],
            'ISTESI015' => ['direct', 10]
        ];
    }
}
