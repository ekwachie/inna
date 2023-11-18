<?php
/**
 * @author      Evans Kwachie <evans.kwachie@ucc.edu.gh>
 * @copyright   Copyright (C), 2019 Evans Kwachie.
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
use app\ext\AppForm;
use app\Core\Utils\Session;

class HomeController extends Controller
{
    public function home()
    {
        $user = new AppForm();
        $data = [
            'static' => STATIC_URL,
            // 'users' => $users,
            'date' => date('Y-m-d'),
        ];

        return $this->render('home', $data);
    }

    public function contact()
    {

        return $this->render('contact');
    }
}
