<?php

namespace app\Models;

use app\Core\DbModel;
use app\Core\Utils\Session;
use app\Core\Application;
use app\Core\Utils\DUtil;

class User extends DbModel
{
    public function createUser($data)
    {
        try {
            $stmt = $this->insert('users', $data);
            return (int) $stmt > 0 ? true : false;
        } catch (\Exception $th) {
            throw new \Exception('USRx001');
        }

    }

    public function authenticate($username, $password)
    {

        try {
            $find = $this->findOne($username);
            if ($find !== false) {
                $stmt = $this->select("SELECT concat(fname, ' ', lname) AS name, email, username, auth, role, password, county_set, country_id FROM users WHERE username = :username", array('username' => $username));
                if (DUtil::passVerify($password, $stmt[0]['password'])) {
                    Session::set('user', $stmt);
                    if (!empty(Session::get('user'))) {
                        // DUtil::debug($stmt);
                        Application::$app->response->redirect('/');
                    }
                } else {
                    return $this->error = array('danger', 'Username / password invalid');
                }

            } else {
                return $this->error = array('danger', 'Invalid username or password');
            }

        } catch (\Throwable $th) {
            throw new \Exception('USRx002');
        }

    }

    public function is_exist($data)
    {
        try {
            $exist = $this->select("SELECT email FROM users WHERE email = :email", array('email' => $data['email']));
            return $exist = (count($exist) > 0) ? true : false;
        } catch (\Exception $th) {
            throw new \Exception('USRx003');
        }
    }

    public function findOne($data)
    {
        try {
            $find = $this->select("SELECT username FROM users WHERE username = :username", array('username' => $data));
            return $find = (count($find) > 0) ? true : false;
        } catch (\Throwable $th) {
            throw new \Exception('USRx004');
        }
    }
}
