<?php
// helpers.php

/**
 * Format price with currency symbol and decimal places
 * 
 * @param float $price The price to format
 * @param string $currency The currency symbol (default: '$')
 * @param int $decimals Number of decimal places (default: 2)
 * @return string Formatted price string
 */
function formatPrice($price, $currency = '$', $decimals = 2) {
    $price = (float)$price;
    return $currency . number_format($price, $decimals);
}