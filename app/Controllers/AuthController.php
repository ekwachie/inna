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
            $csrf_result = DUtil::csrf_verifier($csrf_token);

            if (!$csrf_result['valid']) {
                DUtil::logCsrfFailure();
                DUtil::trackFailedCsrfAttempts();
                
                // Provide specific error message based on failure reason
                $errorMsg = 'Security token validation failed. ';
                if ($csrf_result['reason'] === 'expired') {
                    $errorMsg .= 'Your session has expired. Please refresh the page and try again.';
                } elseif ($csrf_result['reason'] === 'missing') {
                    $errorMsg .= 'Security token is missing. Please refresh the page and try again.';
                } else {
                    $errorMsg .= 'Invalid security token. Please refresh the page and try again.';
                }
                
                $flash = $this->setFlash('error', $errorMsg);
            } else {
                // Reset CSRF failure tracking on successful validation
                DUtil::resetCsrfFailures();
                Session::unsert('csrf_token');
                Session::unsert('csrf_token_expiry');

                $body = $request->getBody();
                $user->name('username')->value($_POST['username'])->required();
                $user->name('password')->value($_POST['pass'])->required();
                if ($user->isSuccess()) {
                    $user->authenticate($_POST['username'], $_POST['pass']);

                    if (!empty($user->error)) {
                        $flash = $this->setFlash($user->error[0], $user->error[1]);
                    }

                } else {
                    $flash = $this->setFlash('danger', $user->errors[0]);
                    $errors = $user->errors;
                }
            }
        }
        // DUtil::debug($user->errors);
        return $this->render('login', [
            'static' => STATIC_URL,
            'csrf_token' => DUtil::csrf_token(),
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
            $csrf_result = DUtil::csrf_verifier($csrf_token);

            if (!$csrf_result['valid']) {
                DUtil::logCsrfFailure();
                DUtil::trackFailedCsrfAttempts();
                
                // Provide specific error message based on failure reason
                $errorMsg = 'Security token validation failed. ';
                if ($csrf_result['reason'] === 'expired') {
                    $errorMsg .= 'Your session has expired. Please refresh the page and try again.';
                } elseif ($csrf_result['reason'] === 'missing') {
                    $errorMsg .= 'Security token is missing. Please refresh the page and try again.';
                } else {
                    $errorMsg .= 'Invalid security token. Please refresh the page and try again.';
                }
                
                $flash = $this->setFlash('error', $errorMsg);
            } else {
                // Reset CSRF failure tracking on successful validation
                DUtil::resetCsrfFailures();
                $user->name('firstname')->value($_POST['fname'])->pattern('words')->required();
                $user->name('lastname')->value($_POST['lname'])->pattern('words')->required();
                $user->name('e-mail')->value($_POST['email'])->required();
                if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) $user->errors[] = 'Invalid email address.';
                $user->name('password')->value($_POST['pass'])->required()->min(8);
                $user->name('confirm password')->value($_POST['passc'])->required();
                $user->name('password')->value($_POST['pass'])->equal($_POST['passc']);

                if ($user->isSuccess()) {
                    $body = $request->getBody();
                    $data = [
                        'firstname' => $_POST['fname'],
                        'lastname' => $_POST['lname'],
                        'username' => $_POST['email'],
                        'email' => $body['email'],
                        'password' => DUtil::passHash($_POST['pass'])
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

                } else {
                    $flash = $this->setFlash('danger', $user->errors[0]);
                }
            }

        }
        return $this->render('register', [
            'static' => STATIC_URL,
            'csrf_token' => DUtil::csrf_token(),
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
