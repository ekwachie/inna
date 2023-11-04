<?php
namespace app\Core\Utils;

use app\Core\Application;
use app\Core\DbModel;

/*
 * Kowri payment platform integration
 *
 * @author Desmond Evans - iamdesmondjr@gmail.com http://www.iamdesmondjr.com
 * @version 1.0
 * @date August 14, 2023
 */

class Paystack extends DbModel
{
    private $secret = '';

    public $msg;

    /**
     * pay authorisation fees to paystact account
     */
    public function makePayment($email, $amount)
    {
        try {

            $bearer = 'account';
            $charges = $amount * 0.02;
            $amount = number_format($amount + $charges, 2);
            // echo $amount;
            // die();
            $url = 'https://api.paystack.co/transaction/initialize';
            $fields = [
                'email' => "{$email}",
                'amount' => "{$amount}" * 100,
                'bearer' => "{$bearer}",
            ];

            $fields_string = http_build_query($fields);

            //open connection
            $ch = curl_init();

            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->secret . '',
                'Cache-Control: no-cache',
            ]);

            //So that curl_exec returns the contents of the cURL; rather than echoing it
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            //execute post
            $result = curl_exec($ch);

            $rest = json_decode($result, true);

            Session::set('ref', $rest['data']['reference']);
            // $curl_errno = curl_errno($ch);
            // $curl_error = curl_error($ch);
            if (!empty($rest['data']['authorization_url'])) {
                echo "<script>window.location.replace('" .
                    $rest['data']['authorization_url'] .
                    "')</script>";
            } else {
                return $this->msg = ['error', 'We are experiencing a high number of applications currently. Kindly try again later.'];
            }

        } catch (\Throwable $th) {
            throw new \Exception('PAYx002');
        }
    }

    /**for verifying  payment */
    public function verifyPayment($ref, $id)
    {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://api.paystack.co/transaction/verify/{$ref}",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->secret . '',
                    'Cache-Control: no-cache',
                ],
            ]);

            $response = curl_exec($curl);
            $response = json_decode($response, true);

            if ($response['data']['status'] == 'success') {
                return true;
            } else {
                return false;
            }
        } catch (\Throwable $th) {
            throw new \Exception('PAYx003');
        }
    }

}