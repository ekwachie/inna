<?php

/**
 * @author      Evans Kwachie <evans.kwachie@ucc.edu.gh>
 * @copyright   Copyright (C), 2019 Evans Kwachie.
 * @license     MIT LICENSE (https://opensource.org/licenses/MIT)
 *              Refer to the LICENSE file distributed within the package.
 * 
 * AppExtension extends Twig default filters and functions to build custom filters and functions 
 * for seemless data manipultion at the client side
 *
 */

namespace app\ext;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;


class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return array(
            new TwigFilter('price', [$this, 'formatPrice']),
        );
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('area', [$this, 'calculateArea']),
        );
    }

    public function formatPrice($number, $decimals = 0, $decPoint = '.', $thousandsSep = ',')
    {
        $price = number_format($number, $decimals, $decPoint, $thousandsSep);
        $price = '$'.$price;

        return $price;
    }

    public function calculateArea(int $width, int $length)
    {
        return $width * $length;
    }

}