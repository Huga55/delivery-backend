<?php

namespace App\Http\Controllers\query;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class QueryController extends Controller
{
    private $tokenDostavista = '1E3028806EAF16291E412C776E842F1E325D045A';
    private $baseUrlDostavista = 'https://robotapitest.dostavista.ru/api/business/1.1/';

    private $tokenDadataPublic = "b15e4101f6f962c56516decebfa99648041e0602";
    private $tokenDadataPrivate = "a6e85d3f5c15a80899abef9944681e88ea984ba8";
    //private $baseUrlDadata = "https://cleaner.dadata.ru/api/";
    private $baseUrlDadata = "https://suggestions.dadata.ru/suggestions/api/";

    private function queryGet($endPoint, $data, $service)
    {
        $data_query = $this->getQueryData($service);

        return json_decode(Http::withHeaders($data_query['header'])->get($data_query['baseUrl'] . $endPoint, $data));
    }

    private function queryPost($endPoint, $data, $service)
    {
        $data_query = $this->getQueryData($service);

        return json_decode(Http::withHeaders($data_query['header'])->post($data_query['baseUrl'] . $endPoint, $data));
    }

    private function getQueryData($service)
    {
        if($service === "dostavista") {
            return [
                'header' => [
                    'X-DV-Auth-Token' => $this->tokenDostavista,
                    'Content-Type' => 'application/json',
                ],
                'baseUrl' => $this->baseUrlDostavista,
            ];
        }

        if($service === "dadata") {
            return [
                'header' => [
                    'Authorization' => 'Token ' . $this->tokenDadataPublic,
                    //'X-Secret' => $this->tokenDadataPrivate,
                    'Content-Type' => 'application/json',
                ],
                'baseUrl' => $this->baseUrlDadata,
            ];
        }

        return ['header' => [''], 'baseUrl' => $service];
    }

    public function getCaptchaKey()
    {
        return $this->queryGet("", "", "http://express/captcha/api/default");
    }


    public function getPrice($data)
    {
        /*
         * @params
         * !@matter - string
         * @total_weight_kg - integer (0)
         * @insurance_amount - money (0.00)
         * @payment_method - cash/non_cash/bank_card (null)
         * @bank_card_id - if(payment_method === bank_card) integer
         * !@points -  ([]) {
         *      !@address - string, full address: city, street, house...
         *      !@contact_person {
         *          !@phone
         *          @name - string/null
         *      }
         *      @client_order_id - id order from bd client - string/null
         *      @latitude - coordinate / null
         *      @longitude - coordinate / null
         *      @note - additional info for courier - string / null
         *      @entrance_number - string / null
         *      @floor_number - string / null
         *      @apartment_number string / null
         * }
         * */
        return $this->queryPost('calculate-order', $data, "dostavista");
    }

    public function getOrders($data)
    {
        /*
         * @params
         * @order_id - integer / list
         * @status - string
         * @offset - pagination (0)
         * @count - integer - max 50 (10)
         * */
        return $this->queryGet('orders', $data, "dostavista");
    }

    public function setOrder($data)
    {
        /*
         * @params
         * !@matter - string
         * @total_weight_kg - integer (0)
         * @insurance_amount - money (0.00)
         * @payment_method - cash/non_cash/bank_card (null)
         * @bank_card_id - if(payment_method === bank_card) integer
         * !@points -  ([]) {
         *      !@address - string, full address: city, street, house...
         *      !@contact_person {
         *          !@phone
         *          @name - string/null
         *      }
         *      @client_order_id - id order from bd client - string/null
         *      @latitude - coordinate / null
         *      @longitude - coordinate / null
         *      @note - additional info for courier - string / null
         *      @entrance_number - string / null
         *      @floor_number - string / null
         *      @apartment_number string / null
         * }
         * */
        return $this->queryPost('create-order', $data, "dostavista");
    }

    public function getCorrectAddress($data)
    {
        return $this->queryPost('4_1/rs/suggest/address', ['query' => $data], 'dadata');
    }

    public function getCorrectAddressFromCoordinates($data)
    {
        return $this->queryGet('4_1/rs/geolocate/address', $data, 'dadata');
    }

    public function getNameOrganization($data)
    {
        return $this->queryPost('4_1/rs/findById/party', ['query' => $data], 'dadata');
    }

    public function cancelOrder($data)
    {
        return $this->queryPost('cancel-order', ['order_id' => $data], 'dostavista');
    }

    public function test()
    {
        $response = Http::withHeaders([
            'X-DV-Auth-Token' => '1E3028806EAF16291E412C776E842F1E325D045A123',
        ])->post('https://robotapitest.dostavista.ru/api/business/1.1/calculate-order', [

        ]);

        return $response;
    }


}
