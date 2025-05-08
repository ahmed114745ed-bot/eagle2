<?php

namespace Database\Seeders;

use App\Models\PaymentCoin;
use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentGatewaysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

         $fawry_id = PaymentCoin::updateOrCreate([
            'title' => 'fawry',
        ],[
            'photo' => 'fawry.jpeg',
            'status' => 1,
        ]);

        $fawry_fields = [
            'new_1' => [
                "name" => "fawry_secret",
                "type" => "input",
                "value" => "fiest1"

            ],
            'new_2' => [
                "name" => "fawry_merchant_code",
                "type" => "input",
                "value" => "Impedit laborum bla"

            ],
            'new_3' => [
                "name" => "fawry_utd_url",
                "type" => "input",
                "value" => "Sit dignissimos aliq"
            ],
            'new_4' => [
                "name" => "is_fawry_active",
                "type" => "input",
                "value" => true
            ],
            'new_5' => [
                "name" => "fawry_url",
                "type" => "input",
                "value" => 'https://www.google.com'
            ],
            'new_6' => [
                "name" => "fawry_return_url",
                "type" => "input",
                "value" => 'https://www.google.com'
            ],
        ];


        foreach($fawry_fields as $key => $value){
            Setting::updateOrCreate(['key' => $value['name'],
            'payment_id' => $fawry_id,
             'type' => 'payment'],[
                'value' => $value['value']
            ]);
        }


        // sky pay

        $pay_sky_id = PaymentCoin::updateOrCreate([
            'title' => 'skyPay',
        ],[
            'photo' => 'skyPay.jpeg',
            'status' => 1,
        ]);

        $pay_sky_fields = [
            'new_1' => [
                "name" => "is_skyPay_active",
                "type" => "input",
                "value" => true
            ],
            'new_2' => [
                "name" => "paysky_base_url",
                "type" => "input",
                "value" => "Impedit laborum bla"

            ],
            'new_3' => [
                "name" => "paysky_merchant_id",
                "type" => "input",
                "value" => "Sit dignissimos aliq"
            ],
            'new_4' => [
                "name" => "paysky_terminal_id",
                "type" => "input",
                "value" => true
            ],
            'new_5' => [
                "name" => "paysky_api_key",
                "type" => "input",
                "value" => 'https://www.google.com'
            ]
        ];


        foreach($pay_sky_fields as $key => $value){
            Setting::updateOrCreate(['key' => $value['name'],
            'payment_id' => $pay_sky_id,
             'type' => 'payment'],[
                'value' => $value['value']
            ]);
        }


        //stripe
        $strip_id = PaymentCoin::updateOrCreate([
            'title' => 'strip',
        ],[
            'photo' => 'stripe.jpeg',
            'status' => 1,
        ]);

        $strip_fields = [
            'new_1' => [
                "name" => "is_stripe_active",
                "type" => "input",
                "value" => true
            ],
            'new_2' => [
                "name" => "stripe_test_secret_key",
                "type" => "input",
                "value" => "Impedit laborum bla"

            ],
            'new_3' => [
                "name" => "stripe_success_url",
                "type" => "input",
                "value" => "Sit dignissimos aliq"
            ],
            'new_4' => [
                "name" => "stripe_cancel_url",
                "type" => "input",
                "value" => true
            ],
            'new_5' => [
                "name" => "stripe_currency",
                "type" => "input",
                "value" => 'usd'
            ],
            'new_5' => [
                "name" => "stripe_webhook_secret",
                "type" => "input",
                "value" => 'webhook'
            ],
        ];


        foreach($strip_fields as $key => $value){
            Setting::updateOrCreate(['key' => $value['name'],
            'payment_id' => $strip_id,
             'type' => 'payment'],[
                'value' => $value['value']
            ]);
        }
    }
}
