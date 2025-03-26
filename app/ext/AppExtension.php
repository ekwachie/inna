<?php

/**
 * @author      Payperlez Team <inna@payperlez.org>
 * @copyright   Copyright (C), 2019 Evans Kwachie.
 * @license     MIT LICENSE (https://opensource.org/licenses/MIT)
 *              Refer to the LICENSE file distributed within the package.
 * 
 * AppExtension extends Twig default filters and functions to build custom filters and functions 
 * for seemless data manipultion at the client side
 * 
 * Filters:: How to use: {{ total_amount | price }} 
 * 
 * Functions:: How to use: {{ area(width, length) }}
 *
 */

namespace app\ext;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;


class AppExtension extends AbstractExtension
{
    // Filters goes here.
    public function getFilters()
    {
        return [
            new TwigFilter('price', [$this, 'formatPrice']),
        ];
    }
    // Functions goes here.
    public function getFunctions()
    {
        return [
            new TwigFunction('area', [$this, 'calculateArea']),
        ];
    }

    public function formatPrice($number, $decimals = 2, $decPoint = '.', $thousandsSep = ',')
    {
        $price = number_format($number, $decimals, $decPoint, $thousandsSep);
        $price = 'GHS' . $price;

        return $price;
    }

    public function calculateArea(int $width, int $length)
    {
        return $width * $length;
    }

}