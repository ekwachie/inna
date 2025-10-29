<?php

/**
 * @author      Payperlez Team <inna@payperlez.org>
 * @copyright   Copyright (C), 2019. Payperlez Inc.
 * @license     MIT LICENSE (https://opensource.org/licenses/MIT)
 *              Refer to the LICENSE file distributed within the package.
 *
 * @todo PDO exception and error handling
 * @category    Database
 * @example
 * $this->query('INSERT INTO tb (col1, col2, col3) VALUES(?,?,?)', $var1, $var2, $var3);
 *
 *
 */

namespace app\Controllers;

use app\Core\Controller;
use app\Core\Request;
use app\Core\Middlewares\AuthMiddleware;

class HomeController extends Controller
{
    public function home(Request $request)
    {
        $data = [
            'static' => STATIC_URL,
            'date' => date('Y-m-d'),
        ];

        return $this->render('home', $data);
    }

    public function about()
    {

        return $this->render('about');
    }

    public function documentation()
    {
        $data = [
            'static' => STATIC_URL,
            'date' => date('Y-m-d'),
        ];
        return $this->render('docs', $data);
    }

    public function contact()
    {

        return $this->render('contact');
    }
}
