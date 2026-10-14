<?php

const SITE_CURRENCY = 'Rs';


const GOOGLE_MAPS_API_KEY = '';

function currency_symbol($currency = null)
{
    if (is_string($currency) && trim($currency) !== '' && trim($currency) !== '$') {
        return htmlspecialchars($currency);
    }
    return SITE_CURRENCY;
}

function format_price($price, $currency = null, $decimals = 2)
{
    return currency_symbol($currency) . ' ' . number_format((float)$price, $decimals);
}