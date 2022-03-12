<?php

namespace app\ext;
use app\core\DbModel;


class AppForm extends DbModel
{
    public function getUsers()
    {
        try {
            $stmt = $this->select("SELECT * FROM users");
            return (int) $stmt > 0 ? $stmt : false;
        } catch (\Exception $th) {
            throw new \Exception('APPx001');
        }

    }

}