<?php
defined('ABSPATH') || exit;

/**
 * Class IMQ_Quote_Items
 * Implements an iterable collection of IMQ_Quote_Item objects.
 */
class IMQ_Quote_Items implements Iterator
{
    private $quote_id;
    private $items = [];
    private $position = 0;

    /**
     * Constructor
     *
     * Populates the collection of IMQ_Quote_Item objects with the items
     * associated with the given quote ID.
     *
     * @param int $quote_id The quote ID to retrieve items for.
     *
     * @since 1.0
     */
    public function __construct($quote_id)
    {
        global $wpdb;
        $this->quote_id = $quote_id;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}quote_product_lookup WHERE quote_id = %d",
                $this->quote_id
            )
        );

        foreach ($results as $result) {
            $this->items[] = new IMQ_Quote_Item($result);
        }
    }


    /**
     * Resets the iterator to the beginning of the collection.
     *
     * @since 1.0
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Returns the current item in the collection.
     *
     * @return IMQ_Quote_Item The current item.
     *
     * @since 1.0
     */
    public function current(): IMQ_Quote_Item
    {
        return $this->items[$this->position];
    }

    /**
     * Returns the current position of the iterator.
     *
     * @return int The current position.
     *
     * @since 1.0
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Advances the iterator to the next item in the collection.
     *
     * @since 1.0
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * Determines if the current item is valid.
     *
     * @return bool True if the current item is valid, false otherwise.
     *
     * @since 1.0
     */
    public function valid(): bool
    {
        return isset($this->items[$this->position]);
    }
}


/**
 * Class IMQ_Quote_Item
 * Represents a single quote item.
 */
class IMQ_Quote_Item
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the ID of the item.
     *
     * @return int
     */
    public function get_item_id(): int
    {
        return $this->data->quote_item_id;
    }

    /**
     * Get the ID of the quote associated with this item.
     *
     * @return int The quote ID.
     */
    public function get_quote_id(): int
    {
        return $this->data->quote_id;
    }

    /**
     * Get the ID of the product associated with this item.
     *
     * @return int The product ID.
     */
    public function get_product_id(): int
    {
        return $this->data->product_id;
    }

    /**
     * Get the ID of the product variation associated with this item. This will
     * be 0 if the item is not a variation.
     *
     * @return int The variation ID.
     */
    public function get_variation_id(): int
    {
        return $this->data->variation_id;
    }


    /**
     * Get the ID of the customer who requested this quote item.
     *
     * @return int The customer ID.
     */
    public function get_customer_id(): int
    {
        return $this->data->customer_id;
    }

    /**
     * Get the date the quote item was created.
     *
     * @return string The date the quote item was created in the format 'Y-m-d H:i:s'.
     */
    public function get_date(): string
    {
        return $this->data->date_created;
    }

    /**
     * Get the quantity of the product associated with this quote item.
     *
     * @return int The product quantity.
     */
    public function get_product_qty(): int
    {
        return $this->data->product_qty;
    }


    /**
     * Get the price of the product associated with this quote item.
     *
     * @return float The price of the product.
     */
    public function get_price(): float
    {
        return $this->data->price;
    }

    /**
     * Get the quote price of the product associated with this quote item.
     *
     * @return float The quote price.
     */
    public function get_quote_price(): float
    {
        return $this->data->quote_price;
    }

    /**
     * Get the total quote price for the product associated with this quote item.
     *
     * @return float The quote price multiplied by the product quantity.
     */
    public function get_quote_price_total(): float
    {
        return $this->data->quote_price * $this->data->product_qty;
    }


    /**
     * Get the SKU of the product associated with this quote item.
     *
     * @return string The product SKU, or an empty string if the product does not exist.
     */
    public function get_line_item(): string
    {
        $product = wc_get_product($this->data->product_id);
        if (!$product) return '';
        return $product->get_sku();
    }

    /**
     * Get the description of the product associated with this quote item.
     *
     * @return string The product description.
     */
    public function get_description(): string
    {
        $product = wc_get_product($this->data->product_id);
        if (!$product) return '';
        return $product->get_description();
    }

    /**
     * Get the price level of the product associated with this quote item.
     *
     * @return string The price level.
     */
    public function get_price_level(): string
    {
        return $this->data->price_level;
    }
}
