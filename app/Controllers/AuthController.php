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
use app\Core\Request;
use app\Core\Controller;
use app\models\User;
use app\Core\Utils\DUtil;
use app\Core\Response;
use app\Core\Application;
use app\Core\Utils\Session;
use app\Core\Middlewares\AdminMiddleware;

class AuthController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware(new AdminMiddleware());
    // }
    public function login(Request $request)
    {
        $user = new User;
        if ($request->isPost()) {
            #implementin CSRF TOKEN
            $csrf_token = $_POST['csrf_token'] ?? '';

            if (!DUtil::csrf_verifier($csrf_token)) {
                DUtil::logCsrfFailure();
                DUtil::trackFailedCsrfAttempts();

                $flash = $this->setFlash('error', 'CSRF token validation failed. Too many attempts may result in blocking.');
            } else {

                Session::unsert('csrf_token');
                Session::unsert('csrf_token_expiry');
                Session::unsert('csrf_failures');

                $body = $request->getBody();
                $user->name('username')->value($_POST['username'])->required();
                $user->name('password')->value($_POST['pass'])->required();
                if ($user->isSuccess()) {
                    $user->authenticate($body['username'], $body['pass']);

                    if (!empty($user->error)) {
                        $flash = $this->setFlash($user->error[0], $user->error[1]);
                    }

                }
            }
        }
        // DUtil::debug($user->errors);
        return $this->render('login', [
            'static' => STATIC_URL,
            'flash' => !empty($flash) ? $flash : '',
            'errors' => !empty($user->errors) ? $this->setFlash('danger', $user->errors[0]) : '',
            'model' => $request->getBody(),
        ]);
    }

    public function register(Request $request)
    {
        $user = new User;
        if ($request->isPost()) {
            #implementin CSRF TOKEN
            $csrf_token = $_POST['csrf_token'] ?? '';

            if (!DUtil::csrf_verifier($csrf_token)) {
                DUtil::logCsrfFailure();
                DUtil::trackFailedCsrfAttempts();

                $flash = $this->setFlash('error', 'CSRF token validation failed. Too many attempts may result in blocking.');
            } else {
                $user->name('firstname')->value($_POST['fname'])->pattern('words')->required();
                $user->name('lastname')->value($_POST['lname'])->pattern('words')->required();
                $user->name('e-mail')->value($_POST['email'])->required()->is_email($_POST['email']);
                $user->name('password')->value($_POST['pass'])->pattern('alphanum')->required();
                $user->name('confirm password')->value($_POST['passc'])->pattern('alphanum')->required();
                $user->name('password')->value($_POST['pass'])->equal($_POST['passc']);

                if ($this->user->isSuccess()) {
                    $body = $request->getBody();
                    $data = [
                        'fname' => $body['fname'],
                        'lname' => $body['lname'],
                        'email' => $body['email'],
                        'password' => DUtil::passHash($body['pass'])
                    ];

                    $stmt = $user->is_exist($data);

                    if ($stmt) {
                        $flash = $this->setFlash(
                            'danger',
                            'Sorry, you already have an acoount with us. Please login using your credentials'
                        );

                    } else {
                        $stmt = $user->createUser($data);
                        if ($stmt) {
                            $flash = $this->setFlash(
                                'success',
                                'Account registered successfully'
                            );

                        }
                    }

                }
            }

        }
        return $this->render('register', [
            'static' => STATIC_URL,
            'flash' => ($flash = isset($flash) ? $flash : ''),
            'errors' => ($errors = isset($user->errors) ? $user->errors : ''),
            'model' => $request->getBody(),
        ]);
    }

    public function logout(Request $request, Response $response)
    {
        if (Session::issert('user')) {
            Session::destroy();
        }
        Application::$app->response->redirect('/');
    }
}
