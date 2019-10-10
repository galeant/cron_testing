<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

use App\Order;
use App\OrderProduct;
use App\CronLog;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class TestController extends Controller
{
    public function cron(){
        $data = Order::with('product')->where([
                    'subscribe' => 1,
                    'order_status_id' => 5
                ])
                ->where('cc_token','!=',null)
                ->where('card_cvn','!=',null)
                ->get();
        
        if($data->count() != 0){
            foreach($data as $dt){

                $new_data_order = $dt->toArray();
                $new_data_order['date_added'] = Carbon::now()->format('Y-m-d');
                $new_data_order['date_modified'] = Carbon::now()->format('Y-m-d');
                $new_data_order['order_status_id'] = 1;
                $new_data_order['xendit_id'] = null;
                $new_data_order['xendit_external_id'] = sha1('kontur'.time());

                switch($dt->delivery_interval){
                    case 'week':
                        if(Carbon::parse($dt->delivery_date)->addDays(4)->format('Y-m-d') == Carbon::now()->format('Y-m-d')){
                            $new_data_order['delivery_date'] = Carbon::parse($dt->delivery_date)->addWeeks(1)->format('Y-m-d');
                        }
                    break;

                    case 'bi_week':
                        if(Carbon::parse($dt->delivery_date)->addDays(11)->format('Y-m-d') == Carbon::now()->format('Y-m-d')){
                            $new_data_order['delivery_date'] = Carbon::parse($dt->delivery_date)->addWeeks(2)->format('Y-m-d');
                        }
                    break;

                    case 'month':
                        if(Carbon::parse($dt->delivery_date)->addMonths(1)->addDays(-3)->format('Y-m-d') == Carbon::now()->format('Y-m-d')){
                            $new_data_order['delivery_date'] = Carbon::parse($dt->delivery_date)->addMonths(1)->format('Y-m-d');
                        }   
                    break;
                }
                $new_order = Order::create($new_data_order);
                foreach($dt->product as $pr){
                    $new_product_data = $pr->toArray();
                    $new_product_data['order_id'] = $new_order->id;
                    OrderProduct::create($new_product_data);
                }
                $client = new Client(['base_uri' => 'https://api.xendit.co/']);
                try {
                    $result = $client->post('credit_card_charges', [
                        'auth' => [
                            ENV('XENDIT_KEY'), 
                            null
                        ],
                        'json' => [
                            'external_id' => $new_order->xendit_external_id,
                            'token_id' => $new_order->cc_token,
                            'amount' => (int)$new_order->total,
                            'card_cvn' => $new_order->card_cvn
                        ]
                    ]);
                }catch (GuzzleException $e) {
                    $response = $e->getResponse();
                    $responseBodyAsString = $response->getBody()->getContents();
                    CronLog::create([
                        'order_id' => $new_order->id,
                        'xendit_external_id' => $new_order->xendit_external_id,
                        'log' => $responseBodyAsString
                    ]);
                }
            }
        }else{
            return 'nothing to update';
        }
    }
}

