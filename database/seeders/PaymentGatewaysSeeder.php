<?php

namespace Database\Seeders;

use App\Models\PaymentCoin;
use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Storage;

class PaymentGatewaysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $images = [
            'fawry.jpeg',
            'paysky.png',
            'stripe.png',
            'opay.png',
            'cashfree.jpg',
            'applepay.png',
//            'mada.png',
//            'liqpay.png',
            'paypal.png',
//            'paytm.png',
            'paytabs.webp',
//            'bkash.png',
//            'razorpay.webp',
//            'senangpay.png',
//            'paymob.png',
//            'flutterwave.jpg',
//            'paystack.png',
//            'sslcommerz.png',
            'googlepay.png',
//            'huaweipay.png',
//            'zinipay.jpg',
        ];

        foreach ($images as $img) {
            $localPath = public_path('images/' . $img);
            $gcsPath = 'images/' . $img;

            if (file_exists($localPath)) {
                Storage::disk('gcs')->put($gcsPath, file_get_contents($localPath), 'public');
            }
        }

        $duplicates = Setting::select('key')//, 'item_id', 'type')
            ->groupBy('key')//, 'item_id', 'type')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        info($duplicates);
        foreach ($duplicates as $key) {
            $settings = Setting::where('key', $key['key'])
//                ->where('item_id', $dup->item_id)
//                ->where('type', $dup->type)
                ->orderBy('id')
                ->get();

            $settings->each(function ($setting) {
                $setting->delete();
            });
        }

        $fawry_id = PaymentCoin::updateOrCreate([
            'title' => 'fawry',
        ], [
            'photo' => 'images/fawry.jpeg',
            'status' => 1,
            'type' => 'fawry',
        ]);
        $fawry_fields = [
            'new_1' => [
                "name" => "fawry_secret",
                "type" => "input",
                "value" => "3a96b82d742c4531a5822ec6eb8c87a4"

            ],
            'new_2' => [
                "name" => "fawry_merchant_code",
                "type" => "input",
                "value" => "400000019844"

            ],
            'new_5' => [
                "name" => "fawry_url",
                "type" => "input",
                "value" => 'https://atfawry.com/fawrypay-api/api/payments/init'
            ],
            'new_6' => [
                "name" => "fawry_return_url",
                "type" => "input",
                "value" => 'https://eagle.utdsoftware.com/api/utd-fawry-callback'
            ],
            'new_7' => [
                "name" => "fawry_webhook_url",
                "type" => "input",
                "value" => url('/api/fawry-callback')
            ],
        ];

        foreach ($fawry_fields as $key => $value) {
            Setting::updateOrCreate([
                'key' => $value['name'],
                'item_id' => $fawry_id->id,
                'type' => 'payment'
            ], [
                'value' => $value['value'],'input_type' => $value['type']
            ]);
        }

        //utd fawry
        $utd_fawry_id = PaymentCoin::updateOrCreate([
            'title' => 'utdFawry',
        ], [
            'photo' => 'images/fawry.jpeg',
            'status' => 1,
            'type' => 'utd_fawry',
        ]);
        $utd_fawry_fields = [
            'new_1' => [
                "name" => "utd_fawry_secret",
                "type" => "input",
                "value" => "3a96b82d742c4531a5822ec6eb8c87a4"

            ],
            'new_2' => [
                "name" => "utd_fawry_merchant_code",
                "type" => "input",
                "value" => "400000019844"

            ],
            'new_3' => [
                "name" => "utd_url",
                "type" => "input",
                "value" => "https://utd-test.utdsoftware.com/api/fawry-initial"
            ],
            'new_5' => [
                "name" => "utd_fawry_url",
                "type" => "input",
                "value" => 'https://atfawry.com/fawrypay-api/api/payments/init'
            ],
            'new_6' => [
                "name" => "utd_fawry_return_url",
                "type" => "input",
                "value" => 'https://eagle.utdsoftware.com/api/utd-fawry-callback'
            ],
        ];

        foreach ($utd_fawry_fields as $key => $value) {
            Setting::updateOrCreate([
                'key' => $value['name'],
                'item_id' => $utd_fawry_id->id,
                'type' => 'payment'
            ], [
                'value' => $value['value'],'input_type' => $value['type']
            ]);
        }

        // sky pay
         $pay_sky_id = PaymentCoin::updateOrCreate([
            'title' => 'skyPay',
        ], [
            'photo' => 'images/paysky.png',
            'status' => 1,
             'type' => 'sky_pay',
         ]);

        $pay_sky_fields = [
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
            ],
            'new_6' => [
                "name" => "paysky_webhook_url",
                "type" => "input",
                "value" => 'https://www.google.com'
            ]
        ];

        foreach ($pay_sky_fields as $key => $value) {
            Setting::updateOrCreate([
                'key' => $value['name'],
                'item_id' => $pay_sky_id->id,
                'type' => 'payment'
            ], [
                'value' => $value['value'],'input_type' => $value['type']
            ]);
        }


        //stripe
        $strip_id = PaymentCoin::updateOrCreate([
            'title' => 'strip',
        ], [
            'photo' => 'images/stripe.png',
            'status' => 1,
            'type' => 'strip',
        ]);

        $strip_fields = [
            'new_2' => [
                "name" => "stripe_test_secret_key",
                "type" => "input",
                "value" => "sk_test_51RaYt2CoBjkPaaGmlmEc6BHnWEZuaX6V6QBh6r0thGsIJYO9bpdNwO1hVGhXXVIljELmMawLKdV1VqGwU5yNbGsP00rAwqVduC"

            ],
            'new_3' => [
                "name" => "stripe_success_url",
                "type" => "input",
                "value" => "pk_test_51RaYt2CoBjkPaaGmno2Ug09jtT4dM6enLsQiIBm4fmt6ZnvZbD2JJN0ZGdWHYia00Cnil07Rn3vrcphwj8Cw5f0A00VNIgJtOA"
            ],
            'new_4' => [
                "name" => "stripe_cancel_url",
                "type" => "https://eagle.utdsoftware.com/api/stripe-callback",
                "value" => true
            ],
            'new_5' => [
                "name" => "stripe_currency",
                "type" => "input",
                "value" => 'usd'
            ],
            'new_6' => [
                "name" => "stripe_webhook_secret",
                "type" => "input",
                "value" => 'whsec_qLRlb2dGJLGqeJstj8lO5CNfYiKCzAaE'
            ],
            'new_7' => [
                "name" => "stripe_webhook_url",
                "type" => "input",
                "value" => url('/api/stripe-callback')
            ],
        ];


        foreach ($strip_fields as $key => $value) {
            Setting::updateOrCreate([
                'key' => $value['name'],
                'item_id' => $strip_id->id,
                'type' => 'payment'
            ], [
                'value' => $value['value'],'input_type' => $value['type']
            ]);
        }

        //opay
        $opay_id = PaymentCoin::updateOrCreate([
            'title' => 'opay',
        ], [
            'photo' => 'images/opay.png',
            'status' => 1,
            'type' => 'opay',
        ]);

        $opay_fields = [
            'new_2' => [
                "name" => "opay_currency",
                "type" => "input",
                "value" => "usd"

            ],
            'new_3' => [
                "name" => "opay_secret_key",
                "type" => "input",
                "value" => "Sit dignissimos aliq"
            ],
            'new_4' => [
                "name" => "opay_public_key",
                "type" => "input",
                "value" => true
            ],
            'new_5' => [
                "name" => "opay_merchant_id",
                "type" => "input",
                "value" => 'usd'
            ],
            'new_6' => [
                "name" => "opay_country_code",
                "type" => "input",
                "value" => 'webhook'
            ],
            'new_7' => [
                "name" => "opay_base_url",
                "type" => "input",
                "value" => 'webhook'
            ],
            'new_8' => [
                "name" => "opay_webhook_url",
                "type" => "input",
                "value" => 'webhook'
            ],
        ];


        foreach ($opay_fields as $key => $value) {
            Setting::updateOrCreate([
                'key' => $value['name'],
                'item_id' => $opay_id->id,
                'type' => 'payment'
            ], [
                'value' => $value['value'],'input_type' => $value['type']
            ]);
        }

        //cashfree
        $cashfree_id = PaymentCoin::updateOrCreate([
            'title' => 'cashfree',
        ], [
            'photo' => 'images/cashfree.jpg',
            'status' => 1,
            'type' => 'cash_free',
        ]);

        $cashfree_fields = [
            'new_2' => [
                "name" => "cashfree_currency",
                "type" => "input",
                "value" => "usd"

            ],
            'new_3' => [
                "name" => "cashfree_app_id",
                "type" => "input",
                "value" => "Sit dignissimos aliq"
            ],
            'new_4' => [
                "name" => "cashfree_secret_key",
                "type" => "input",
                "value" => 'asdasd'
            ],
            'new_5' => [
                "name" => "cashfree_base_url",
                "type" => "input",
                "value" => 'asdasdad'
            ],
            'new_6' => [
                "name" => "cashfree_webhook_url",
                "type" => "input",
                "value" => 'asdasdad'
            ],
        ];


        foreach ($cashfree_fields as $key => $value) {
            Setting::updateOrCreate([
                'key' => $value['name'],
                'item_id' => $cashfree_id->id,
                'type' => 'payment'
            ], [
                'value' => $value['value'],'input_type' => $value['type']
            ]);
        }

        //applepay
        $applepay_id = PaymentCoin::updateOrCreate([
            'title' => 'applepay',
        ], [
            'photo' => 'images/applepay.png',
            'status' => 1,
            'type' => 'apple_pay',
        ]);

        $applepay_fields = [
            'new_2' => [
                "name" => "apple_team_id",
                "type" => "input",
                "value" => "apple team"

            ],
            'new_3' => [
                "name" => "app_id",
                "type" => "input",
                "value" => "Sit dignissimos aliq"
            ],
            'new_4' => [
                "name" => "apple_client_id",
                "type" => "input",
                "value" => 'asdasd'
            ],
            'new_5' => [
                "name" => "apple_redirect_uri",
                "type" => "input",
                "value" => 'asdasdad'
            ],
            'new_6' => [
                "name" => "apple_service_file",
                "type" => "file",
                "value" => 'asdasdad'
            ],
            'new_7' => [
                "name" => "apple_webhook_url",
                "type" => "file",
                "value" => 'asdasdad'
            ],
        ];


        foreach ($applepay_fields as $key => $value) {
            Setting::updateOrCreate([
                'key' => $value['name'],
                'item_id' => $applepay_id->id,
                'type' => 'payment'
            ], [
                'value' => $value['value'],'input_type' => $value['type']
            ]);
        }



        //mada
//        $mada_id = PaymentCoin::updateOrCreate([
//            'title' => 'mada',
//        ], [
//            'photo' => 'images/mada.png',
//            'status' => 1,
//            'type' => 'mada',
//        ]);
//
//        $mada_fields = [
//            'new_2' => [
//                "name" => "mada_access_token",
//                "type" => "input",
//                "value" => "apple team"
//
//            ],
//            'new_3' => [
//                "name" => "mada_public_key",
//                "type" => "input",
//                "value" => "Sit dignissimos aliq"
//            ],
//            'new_4' => [
//                "name" => "mada_payment_address",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//            'new_5' => [
//                "name" => "mada_webhook_url",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//        ];


//        foreach ($mada_fields as $key => $value) {
//            Setting::updateOrCreate([
//                'key' => $value['name'],
//                'item_id' => $mada_id->id,
//                'type' => 'payment'
//            ], [
//                'value' => $value['value'],'input_type' => $value['type']
//            ]);
//        }



//        //liqpay
//        $liqpay_id = PaymentCoin::updateOrCreate([
//            'title' => 'liqpay',
//        ], [
//            'photo' => 'images/liqpay.png',
//            'status' => 1,
//            'type' => 'liq_pay',
//        ]);
//
//        $liqpay_fields = [
//            'new_2' => [
//                "name" => "liqpay_public_key",
//                "type" => "input",
//                "value" => "apple team"
//
//            ],
//            'new_3' => [
//                "name" => "liqpay_private_key",
//                "type" => "input",
//                "value" => "Sit dignissimos aliq"
//            ],
//            'new_4' => [
//                "name" => "liqpay_payment_address",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//            'new_5' => [
//                "name" => "liqpay_webhook_url",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//        ];
//
//
//        foreach ($liqpay_fields as $key => $value) {
//            Setting::updateOrCreate([
//                'key' => $value['name'],
//                'item_id' => $liqpay_id->id,
//                'type' => 'payment'
//            ], [
//                'value' => $value['value'],'input_type' => $value['type']
//            ]);
//        }

        //paypal
        $paypal_id = PaymentCoin::updateOrCreate([
            'title' => 'paypal',
        ], [
            'photo' => 'images/paypal.png',
            'status' => 1,
            'type' => 'paypal',
        ]);

        $paypal_fields = [
            'new_1' => [
                "name" => "paypal_base_url",
                "type" => "input",
                "value" => "https://api-m.sandbox.paypal.com"

            ],
            'new_2' => [
                "name" => "paypal_client_id",
                "type" => "input",
                "value" => "AbMnfYHyLXWcRTct1RGWW5tPFnd6SryR0ALvRMgSG4PQW5oV8uti7fYOTmQvXLPzGVJSOgZTrmJ_NpKR"

            ],
            'new_3' => [
                "name" => "paypal_client_secret",
                "type" => "input",
                "value" => "EHWD7-rBHXm74Dv8e9_-EMYRWMt1LAt7qzmW-YW3pOBWdLPCVs-D5hoAwfqYyS7cNW5A7Uv3IyqUgqyA"
            ],
//            'new_4' => [
//                "name" => "paypal_payment_address",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
            'new_4' => [
                "name" => "paypal_webhook_url",
                "type" => "input",
                "value" => 'https://eagle.utdsoftware.com/api/paypal-callback'
            ],
            'new_5' => [
                "name" => "paypal_webhook_id",
                "type" => "input",
                "value" => '4Y826428L32304057'
            ],
        ];


        foreach ($paypal_fields as $key => $value) {
            Setting::updateOrCreate([
                'key' => $value['name'],
                'item_id' => $paypal_id->id,
                'type' => 'payment'
            ], [
                'value' => $value['value'],'input_type' => $value['type']
            ]);
        }
//
//
//
//
//        //paytm
//        $paytm_id = PaymentCoin::updateOrCreate([
//            'title' => 'paytm',
//        ], [
//            'photo' => 'images/paytm.png',
//            'status' => 1,
//            'type' => 'paytm',
//        ]);
//
//        $paytm_fields = [
//            'new_2' => [
//                "name" => "paytm_merchant_key",
//                "type" => "input",
//                "value" => "apple team"
//
//            ],
//            'new_3' => [
//                "name" => "paytm_merchant_id",
//                "type" => "input",
//                "value" => "Sit dignissimos aliq"
//            ],
//            'new_4' => [
//                "name" => "paytm_merchant_website_link",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//            'new_5' => [
//                "name" => "paytm_payment_address",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//            'new_6' => [
//                "name" => "paytm_webhook_url",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//        ];
//
//
//        foreach ($paytm_fields as $key => $value) {
//            Setting::updateOrCreate([
//                'key' => $value['name'],
//                'item_id' => $paytm_id->id,
//                'type' => 'payment'
//            ], [
//                'value' => $value['value'],'input_type' => $value['type']
//            ]);
//        }



        //paytabs
        $paytabs_id = PaymentCoin::updateOrCreate([
            'title' => 'paytabs',
        ], [
            'photo' => 'images/paytabs.webp',
            'status' => 1,
            'type' => 'paytabs',
        ]);

        $paytabs_fields = [
            'new_2' => [
                "name" => "paytabs_profile_id",
                "type" => "input",
                "value" => "apple team"

            ],
            'new_3' => [
                "name" => "paytabs_server_key",
                "type" => "input",
                "value" => "Sit dignissimos aliq"
            ],
            'new_4' => [
                "name" => "paytabs_base_url",
                "type" => "input",
                "value" => 'asdasd'
            ],
            'new_5' => [
                "name" => "paytabs_payment_address",
                "type" => "input",
                "value" => 'asdasd'
            ],
            'new_6' => [
                "name" => "paytabs_webhook_url",
                "type" => "input",
                "value" => 'asdasd'
            ],
        ];


        foreach ($paytabs_fields as $key => $value) {
            Setting::updateOrCreate([
                'key' => $value['name'],
                'item_id' => $paytabs_id->id,
                'type' => 'payment'
            ], [
                'value' => $value['value'],'input_type' => $value['type']
            ]);
        }




        //BKash
//        $bkash_id = PaymentCoin::updateOrCreate([
//            'title' => 'bkash',
//        ], [
//            'photo' => 'images/bkash.png',
//            'status' => 1,
//            'type' => 'bkash',
//        ]);
//
//        $bkash_fields = [
//            'new_2' => [
//                "name" => "bkash_appkey",
//                "type" => "input",
//                "value" => "apple team"
//
//            ],
//            'new_3' => [
//                "name" => "bkash_app_secret",
//                "type" => "input",
//                "value" => "Sit dignissimos aliq"
//            ],
//            'new_4' => [
//                "name" => "bkash_username",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//            'new_5' => [
//                "name" => "bkash_password",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//            'new_6' => [
//                "name" => "bkash_payment_address",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//            'new_7' => [
//                "name" => "bkash_webhook_url",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//        ];
//
//
//        foreach ($bkash_fields as $key => $value) {
//            Setting::updateOrCreate([
//                'key' => $value['name'],
//                'item_id' => $bkash_id->id,
//                'type' => 'payment'
//            ], [
//                'value' => $value['value'],'input_type' => $value['type']
//            ]);
//        }


        //razorpay
//        $razorpay_id = PaymentCoin::updateOrCreate([
//            'title' => 'razorpay',
//        ], [
//            'photo' => 'images/razorpay.webp',
//            'status' => 1,
//            'type' => 'razor_pay',
//        ]);
//
//        $razorpay_fields = [
//            'new_2' => [
//                "name" => "razorpay_api_key",
//                "type" => "input",
//                "value" => "apple team"
//
//            ],
//            'new_3' => [
//                "name" => "razorpay_api_secret",
//                "type" => "input",
//                "value" => "Sit dignissimos aliq"
//            ],
//            'new_4' => [
//                "name" => "razorpay_payment_address",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//            'new_5' => [
//                "name" => "razorpay_webhook_url",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//        ];
//
//
//        foreach ($razorpay_fields as $key => $value) {
//            Setting::updateOrCreate([
//                'key' => $value['name'],
//                'item_id' => $razorpay_id->id,
//                'type' => 'payment'
//            ], [
//                'value' => $value['value'],'input_type' => $value['type']
//            ]);
//        }



        //senangpay
//        $senangpay_id = PaymentCoin::updateOrCreate([
//            'title' => 'senangpay',
//        ], [
//            'photo' => 'images/senangpay.png',
//            'status' => 1,
//            'type' => 'senang_pay',
//        ]);
//
//        $senangpay_fields = [
//            'new_2' => [
//                "name" => "senangpay_callback_url",
//                "type" => "input",
//                "value" => "apple team"
//
//            ],
//            'new_3' => [
//                "name" => "senangpay_secret_key",
//                "type" => "input",
//                "value" => "Sit dignissimos aliq"
//            ],
//            'new_4' => [
//                "name" => "senangpay_merchant_id",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//            'new_5' => [
//                "name" => "senangpay_payment_address",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//            'new_6' => [
//                "name" => "senangpay_webhook_url",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//        ];
//
//
//        foreach ($senangpay_fields as $key => $value) {
//            Setting::updateOrCreate([
//                'key' => $value['name'],
//                'item_id' => $senangpay_id->id,
//                'type' => 'payment'
//            ], [
//                'value' => $value['value'],'input_type' => $value['type']
//            ]);
//        }




        //paymob_accept
//        $paymob_accept_id = PaymentCoin::updateOrCreate([
//            'title' => 'paymob_accept',
//        ], [
//            'photo' => 'images/paymob.png',
//            'status' => 1,
//            'type' => 'paymob_accept',
//        ]);
//
//        $paymob_accept_fields = [
//            'new_2' => [
//                "name" => "paymob_accept_callback_url",
//                "type" => "input",
//                "value" => "apple team"
//
//            ],
//            'new_3' => [
//                "name" => "paymob_accept_api_key",
//                "type" => "input",
//                "value" => "Sit dignissimos aliq"
//            ],
//            'new_4' => [
//                "name" => "paymob_accept_iframe_id",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//            'new_5' => [
//                "name" => "paymob_accept_integration_id",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//            'new_6' => [
//                "name" => "paymob_accept_hmac",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//            'new_7' => [
//                "name" => "paymob_accept_payment_address",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//            'new_8' => [
//                "name" => "paymob_accept_webhook_url",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//        ];
//
//
//        foreach ($paymob_accept_fields as $key => $value) {
//            Setting::updateOrCreate([
//                'key' => $value['name'],
//                'item_id' => $paymob_accept_id->id,
//                'type' => 'payment'
//            ], [
//                'value' => $value['value'],'input_type' => $value['type']
//            ]);
//        }



        //flutterwave
//        $flutterwave_id = PaymentCoin::updateOrCreate([
//            'title' => 'flutterwave',
//        ], [
//            'photo' => 'images/flutterwave.jpg',
//            'status' => 1,
//            'type' => 'flutter_wave',
//        ]);
//
//        $flutterwave_fields = [
//            'new_2' => [
//                "name" => "flutterwave_secret_key",
//                "type" => "input",
//                "value" => "apple team"
//
//            ],
//            'new_3' => [
//                "name" => "flutterwave_public_key",
//                "type" => "input",
//                "value" => "Sit dignissimos aliq"
//            ],
//            'new_4' => [
//                "name" => "flutterwave_hash",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//            'new_5' => [
//                "name" => "flutterwave_payment_address",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//            'new_6' => [
//                "name" => "flutterwave_webhook_url",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//        ];
//
//
//        foreach ($flutterwave_fields as $key => $value) {
//            Setting::updateOrCreate([
//                'key' => $value['name'],
//                'item_id' => $flutterwave_id->id,
//                'type' => 'payment'
//            ], [
//                'value' => $value['value'],'input_type' => $value['type']
//            ]);
//        }




        //paystack
//        $paystack_id = PaymentCoin::updateOrCreate([
//            'title' => 'paystack',
//        ], [
//            'photo' => 'images/paystack.png',
//            'status' => 1,
//            'type' => 'pay_stack',
//        ]);
//
//        $paystack_fields = [
//            'new_2' => [
//                "name" => "paystack_public_key",
//                "type" => "input",
//                "value" => "apple team"
//
//            ],
//            'new_3' => [
//                "name" => "paystack_secret_key",
//                "type" => "input",
//                "value" => "Sit dignissimos aliq"
//            ],
//            'new_4' => [
//                "name" => "paystack_merchant_email",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//            'new_5' => [
//                "name" => "paystack_return_url",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//            'new_6' => [
//                "name" => "paystack_payment_address",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//            'new_7' => [
//                "name" => "paystack_webhook_url",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//        ];
//
//
//        foreach ($paystack_fields as $key => $value) {
//            Setting::updateOrCreate([
//                'key' => $value['name'],
//                'item_id' => $paystack_id->id,
//                'type' => 'payment'
//            ], [
//                'value' => $value['value'],'input_type' => $value['type']
//            ]);
//        }



        //sslcommerz
//        $sslcommerz_id = PaymentCoin::updateOrCreate([
//            'title' => 'sslcommerz',
//        ], [
//            'photo' => 'images/sslcommerz.png',
//            'status' => 1,
//            'type' => 'ssl_commerz',
//        ]);
//
//        $sslcommerz_fields = [
//            'new_2' => [
//                "name" => "sslcommerz_store_id",
//                "type" => "input",
//                "value" => "apple team"
//
//            ],
//            'new_3' => [
//                "name" => "sslcommerz_store_password",
//                "type" => "input",
//                "value" => "Sit dignissimos aliq"
//            ],
//            'new_4' => [
//                "name" => "sslcommerz_payment_address",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//            'new_5' => [
//                "name" => "sslcommerz_webhook_url",
//                "type" => "input",
//                "value" => 'asdasd'
//            ],
//        ];
//
//
//        foreach ($sslcommerz_fields as $key => $value) {
//            Setting::updateOrCreate([
//                'key' => $value['name'],
//                'item_id' => $sslcommerz_id->id,
//                'type' => 'payment'
//            ], [
//                'value' => $value['value'],'input_type' => $value['type']
//            ]);
//        }

        // Google Pay
        $google_pay_id = PaymentCoin::updateOrCreate([
            'title' => 'google_pay',
        ], [
            'photo' => 'images/googlepay.png',
            'status' => 1,
            'type' => 'google_pay',
        ]);

        $google_pay_fields = [
            'new_1' => [
                "name" => "google_pay_payment_url",
                "type" => "input",
                "value" => "test"
            ],
            'new_2' => [
                "name" => "google_pay_node_server_name",
                "type" => "input",
                "value" => "test"
            ],
            'new_3' => [
                "name" => "google_pay_webhook_url",
                "type" => "input",
                "value" => "test"
            ],
        ];

        foreach ($google_pay_fields as $key => $value) {
            Setting::updateOrCreate([
                'key' => $value['name'],
                'item_id' => $google_pay_id->id,
                'type' => 'payment'
            ], [
                'value' => $value['value'],
                'input_type' => $value['type']
            ]);
        }

        //huawei pay
//        $huawei_pay_id = PaymentCoin::updateOrCreate([
//            'title' => 'huawei_pay',
//        ], [
//            'photo' => 'images/huaweipay.png',
//            'status' => 1,
//            'type' => 'huawei_pay',
//        ]);
//
//        $huawei_pay_fields = [
//            'new_1' => [
//                "name" => "huawei_pay_merchant_id",
//                "type" => "input",
//                "value" => "123"
//            ],
//            'new_2' => [
//                "name" => "huawei_pay_webhook_url",
//                "type" => "input",
//                "value" => "123"
//            ],
//        ];
//
//        foreach ($huawei_pay_fields as $key => $value) {
//            Setting::updateOrCreate([
//                'key' => $value['name'],
//                'item_id' => $huawei_pay_id->id,
//                'type' => 'payment'
//            ], [
//                'value' => $value['value'],
//                'input_type' => $value['type']
//            ]);
//        }

        //zinipay
//        $zinipay_id = PaymentCoin::updateOrCreate([
//            'title' => 'zinipay',
//        ], [
//            'photo' => 'images/zinipay.jpg',
//            'status' => 1,
//            'type' => 'zinipay',
//        ]);
//
//        $zinipay_fields = [
//            'new_1' => [
//                "name" => "zinipay_api_key",
//                "type" => "input",
//                "value" => "123"
//            ],
//            'new_2' => [
//                "name" => "zinipay_url",
//                "type" => "input",
//                "value" => "test"
//            ],
//            'new_3' => [
//                "name" => "zinipay_webhook_url",
//                "type" => "input",
//                "value" => "test"
//            ],
//        ];
//
//        foreach ($zinipay_fields as $key => $value) {
//            Setting::updateOrCreate([
//                'key' => $value['name'],
//                'item_id' => $zinipay_id->id,
//                'type' => 'payment'
//            ], [
//                'value' => $value['value'],
//                'input_type' => $value['type']
//            ]);
//        }

    }
}
