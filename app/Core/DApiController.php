<?php

/**
 * @author      Obed Ademang <kizit2012@gmail.com>
 * @copyright   Copyright (C), 2015 Obed Ademang
 * @license     MIT LICENSE (https://opensource.org/licenses/MIT)
 *              Refer to the LICENSE file distributed within the package.
 *
 */

namespace app\Core;


class DApiController
{
    public function message($status, $message, $errorCode = null)
    {

        if ($status === false) {
            return $this->json([
                "success" => false,
                "data" => $message,
                "error_code" => (!empty($errorCode)) ? $errorCode : "DERRx000"
            ]);
        }
        else {
            return $this->json([
                "success" => true,
                "data" => $message
            ]);
        }
    }

    public function json($value, ?int $options = null, int $dept = 512): void
    {
        $this->header('Content-Type: application/json; charset=utf-8');
        echo json_encode($value, $options, $dept);
        exit(0);
    }

    /**
     * Add header to response
     * @param string $value
     * @return static
     */
    public function header(string $value): self
    {
        header($value);

        return $this;
    }
}