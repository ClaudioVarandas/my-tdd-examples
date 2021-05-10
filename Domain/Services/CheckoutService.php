<?php declare(strict_types=1);

namespace Domain\Services;

/**
 * Class CheckoutService
 * @author Claudio Varandas <cvarandas@gmail.com>
 * @package Domain\Services
 */
final class CheckoutService
{
    /**
     * @var array
     */
    protected array $items = [];

    /**
     * @var float
     */
    protected float $total = 0.0;

    /**
     * @var array
     */
    protected array $promoRules;

    /**
     * @var array
     */
    protected array $itemQtyOverridesForDiscount = [];

    /**
     * CheckoutService constructor.
     * @param array $promoRules
     */
    public function __construct(array $promoRules)
    {
        $this->promoRules = $promoRules;
    }

    /**
     * @param array $item
     */
    public function scan(array $item)
    {
        array_push($this->items, $item);
        // Apply promo rules (if applicable)
        $promoValue = 0.0;
        if (in_array($item['product_code'], array_keys($this->promoRules))) {
            $promoValue = $this->applyPromoRules($item);
        }
        // Apply product discount (if applicable)
        $qtyApplicableForDiscount = $this->itemQtyOverridesForDiscount[$item['product_code']] ?? $item['qty'];
        $discountValue = $this->calculateDiscount($item) * $qtyApplicableForDiscount;
        // Calculate product final price
        $finalPrice = ($item['price'] * $item['qty']) - $promoValue - $discountValue;
        // Add to checkout total
        $this->total += $this->formatPrice($finalPrice);
    }

    /**
     * @param array $item
     * @param int|null $discount
     * @return float
     */
    public function calculateDiscount(array $item, int $discount = null): float
    {
        $pDiscount = is_null($discount) ? $item['discount'] / 100 : $discount / 100;
        return $item['price'] * $pDiscount;
    }

    /**
     * @param array $item
     * @return float
     */
    protected function applyPromoRules(array $item): float
    {
        $rule = $this->promoRules[$item['product_code']];
        $ruleName = sprintf('calculate%sPromoValue', ucfirst($rule[0]));
        return $this->{$ruleName}($item, $rule);
    }

    /**
     * @param $item
     * @param $rule
     * @return float
     */
    protected function calculateQtyPromoValue($item, $rule): float
    {
        $result = $item['qty'] >= $rule[1] ? $item['price'] * $rule[2] : 0.0;

        $qtyApplicableForDiscount = $item['qty'] - $rule[2];
        $this->itemQtyOverridesForDiscount[$item['product_code']] = $qtyApplicableForDiscount;
        return $result;
    }

    /**
     * @param array $item
     * @param $rule
     * @return float
     */
    public function calculateDirectPromoValue(array $item, $rule): float
    {
        return $this->calculateDiscount($item, $rule[1]);
    }

    /**
     * @param float $price
     * @return float
     */
    public function formatPrice(float $price): float
    {
        return round($price, 2, PHP_ROUND_HALF_DOWN);
    }

    /**
     * @return float
     */
    public function getTotal(): float
    {
        return $this->formatPrice($this->total);
    }

    /**
     * @return float
     */
    public function getRawTotal(): float
    {
        return $this->total;
    }

    /**
     * @return string
     */
    public function getTotalToString(): string
    {
        return number_format($this->total, 2);
    }
}
