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
        ], [
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

        foreach ($fawry_fields as $key => $value) {
            Setting::updateOrCreate([
                'key' => $value['name'],
                'item_id' => $fawry_id->id,
                'type' => 'payment'
            ], [
                'value' => $value['value'],'input_type' => $value['type']
            ]);
        }


        // sky pay

         $pay_sky_id = PaymentCoin::updateOrCreate([
            'title' => 'skyPay',
        ], [
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
            'photo' => 'opay.jpeg',
            'status' => 1,
        ]);

        $opay_fields = [
            'new_1' => [
                "name" => "is_opay_active",
                "type" => "input",
                "value" => true
            ],
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
            'photo' => 'cashfree.jpeg',
            'status' => 1,
        ]);

        $cashfree_fields = [
            'new_1' => [
                "name" => "is_cashfree_active",
                "type" => "input",
                "value" => true
            ],
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
            'photo' => 'applepay.jpeg',
            'status' => 1,
        ]);

        $applepay_fields = [
            'new_1' => [
                "name" => "is_applepay_active",
                "type" => "input",
                "value" => true
            ],
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
        $mada_id = PaymentCoin::updateOrCreate([
            'title' => 'mada',
        ], [
            'photo' => 'mada.jpeg',
            'status' => 1,
        ]);

        $mada_fields = [
            'new_1' => [
                "name" => "is_mada_active",
                "type" => "input",
                "value" => true
            ],
            'new_2' => [
                "name" => "mada_access_token",
                "type" => "input",
                "value" => "apple team"

            ],
            'new_3' => [
                "name" => "mada_public_key",
                "type" => "input",
                "value" => "Sit dignissimos aliq"
            ],
            'new_4' => [
                "name" => "mada_payment_address",
                "type" => "input",
                "value" => 'asdasd'
            ]
        ];


        foreach ($mada_fields as $key => $value) {
            Setting::updateOrCreate([
                'key' => $value['name'],
                'item_id' => $mada_id->id,
                'type' => 'payment'
            ], [
                'value' => $value['value'],'input_type' => $value['type']
            ]);
        }



        //liqpay
        $liqpay_id = PaymentCoin::updateOrCreate([
            'title' => 'liqpay',
        ], [
            'photo' => 'liqpay.jpeg',
            'status' => 1,
        ]);

        $liqpay_fields = [
            'new_1' => [
                "name" => "is_liqpay_active",
                "type" => "input",
                "value" => true
            ],
            'new_2' => [
                "name" => "liqpay_public_key",
                "type" => "input",
                "value" => "apple team"

            ],
            'new_3' => [
                "name" => "liqpay_private_key",
                "type" => "input",
                "value" => "Sit dignissimos aliq"
            ],
            'new_4' => [
                "name" => "liqpay_payment_address",
                "type" => "input",
                "value" => 'asdasd'
            ]
        ];


        foreach ($liqpay_fields as $key => $value) {
            Setting::updateOrCreate([
                'key' => $value['name'],
                'item_id' => $liqpay_id->id,
                'type' => 'payment'
            ], [
                'value' => $value['value'],'input_type' => $value['type']
            ]);
        }



        //paypal
        $paypal_id = PaymentCoin::updateOrCreate([
            'title' => 'paypal',
        ], [
            'photo' => 'paypal.jpeg',
            'status' => 1,
        ]);

        $paypal_fields = [
            'new_1' => [
                "name" => "is_paypal_active",
                "type" => "input",
                "value" => true
            ],
            'new_2' => [
                "name" => "paypal_client_id",
                "type" => "input",
                "value" => "apple team"

            ],
            'new_3' => [
                "name" => "paypal_client_secret",
                "type" => "input",
                "value" => "Sit dignissimos aliq"
            ],
            'new_4' => [
                "name" => "paypal_payment_address",
                "type" => "input",
                "value" => 'asdasd'
            ]
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




        //paytm
        $paytm_id = PaymentCoin::updateOrCreate([
            'title' => 'paytm',
        ], [
            'photo' => 'paytm.jpeg',
            'status' => 1,
        ]);

        $paytm_fields = [
            'new_1' => [
                "name" => "is_paytm_active",
                "type" => "input",
                "value" => true
            ],
            'new_2' => [
                "name" => "paytm_merchant_key",
                "type" => "input",
                "value" => "apple team"

            ],
            'new_3' => [
                "name" => "paytm_merchant_id",
                "type" => "input",
                "value" => "Sit dignissimos aliq"
            ],
            'new_4' => [
                "name" => "paytm_merchant_website_link",
                "type" => "input",
                "value" => 'asdasd'
            ],
            'new_5' => [
                "name" => "paytm_payment_address",
                "type" => "input",
                "value" => 'asdasd'
            ]
        ];


        foreach ($paytm_fields as $key => $value) {
            Setting::updateOrCreate([
                'key' => $value['name'],
                'item_id' => $paytm_id->id,
                'type' => 'payment'
            ], [
                'value' => $value['value'],'input_type' => $value['type']
            ]);
        }



        //paytabs
        $paytabs_id = PaymentCoin::updateOrCreate([
            'title' => 'paytabs',
        ], [
            'photo' => 'paytabs.jpeg',
            'status' => 1,
        ]);

        $paytabs_fields = [
            'new_1' => [
                "name" => "is_paytabs_active",
                "type" => "input",
                "value" => true
            ],
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
            ]
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
        $bkash_id = PaymentCoin::updateOrCreate([
            'title' => 'bkash',
        ], [
            'photo' => 'bkash.jpeg',
            'status' => 1,
        ]);

        $bkash_fields = [
            'new_1' => [
                "name" => "is_bkash_active",
                "type" => "input",
                "value" => true
            ],
            'new_2' => [
                "name" => "bkash_appkey",
                "type" => "input",
                "value" => "apple team"

            ],
            'new_3' => [
                "name" => "bkash_app_secret",
                "type" => "input",
                "value" => "Sit dignissimos aliq"
            ],
            'new_4' => [
                "name" => "bkash_username",
                "type" => "input",
                "value" => 'asdasd'
            ],
            'new_5' => [
                "name" => "bkash_password",
                "type" => "input",
                "value" => 'asdasd'
            ],
            'new_6' => [
                "name" => "bkash_payment_address",
                "type" => "input",
                "value" => 'asdasd'
            ],
        ];


        foreach ($bkash_fields as $key => $value) {
            Setting::updateOrCreate([
                'key' => $value['name'],
                'item_id' => $bkash_id->id,
                'type' => 'payment'
            ], [
                'value' => $value['value'],'input_type' => $value['type']
            ]);
        }


        //razorpay
        $razorpay_id = PaymentCoin::updateOrCreate([
            'title' => 'razorpay',
        ], [
            'photo' => 'razorpay.jpeg',
            'status' => 1,
        ]);

        $razorpay_fields = [
            'new_1' => [
                "name" => "is_razorpay_active",
                "type" => "input",
                "value" => true
            ],
            'new_2' => [
                "name" => "razorpay_api_key",
                "type" => "input",
                "value" => "apple team"

            ],
            'new_3' => [
                "name" => "razorpay_api_secret",
                "type" => "input",
                "value" => "Sit dignissimos aliq"
            ],
            'new_4' => [
                "name" => "razorpay_payment_address",
                "type" => "input",
                "value" => 'asdasd'
            ],
        ];


        foreach ($razorpay_fields as $key => $value) {
            Setting::updateOrCreate([
                'key' => $value['name'],
                'item_id' => $razorpay_id->id,
                'type' => 'payment'
            ], [
                'value' => $value['value'],'input_type' => $value['type']
            ]);
        }



        //senangpay
        $senangpay_id = PaymentCoin::updateOrCreate([
            'title' => 'senangpay',
        ], [
            'photo' => 'senangpay.jpeg',
            'status' => 1,
        ]);

        $senangpay_fields = [
            'new_1' => [
                "name" => "is_senangpay_active",
                "type" => "input",
                "value" => true
            ],
            'new_2' => [
                "name" => "senangpay_callback_url",
                "type" => "input",
                "value" => "apple team"

            ],
            'new_3' => [
                "name" => "senangpay_secret_key",
                "type" => "input",
                "value" => "Sit dignissimos aliq"
            ],
            'new_4' => [
                "name" => "senangpay_merchant_id",
                "type" => "input",
                "value" => 'asdasd'
            ],
            'new_5' => [
                "name" => "senangpay_payment_address",
                "type" => "input",
                "value" => 'asdasd'
            ],
        ];


        foreach ($senangpay_fields as $key => $value) {
            Setting::updateOrCreate([
                'key' => $value['name'],
                'item_id' => $senangpay_id->id,
                'type' => 'payment'
            ], [
                'value' => $value['value'],'input_type' => $value['type']
            ]);
        }




        //paymob_accept
        $paymob_accept_id = PaymentCoin::updateOrCreate([
            'title' => 'paymob_accept',
        ], [
            'photo' => 'paymob_accept.jpeg',
            'status' => 1,
        ]);

        $paymob_accept_fields = [
            'new_1' => [
                "name" => "is_paymob_accept_active",
                "type" => "input",
                "value" => true
            ],
            'new_2' => [
                "name" => "paymob_accept_callback_url",
                "type" => "input",
                "value" => "apple team"

            ],
            'new_3' => [
                "name" => "paymob_accept_api_key",
                "type" => "input",
                "value" => "Sit dignissimos aliq"
            ],
            'new_4' => [
                "name" => "paymob_accept_iframe_id",
                "type" => "input",
                "value" => 'asdasd'
            ],
            'new_5' => [
                "name" => "paymob_accept_integration_id",
                "type" => "input",
                "value" => 'asdasd'
            ],
            'new_6' => [
                "name" => "paymob_accept_hmac",
                "type" => "input",
                "value" => 'asdasd'
            ],
            'new_7' => [
                "name" => "paymob_accept_payment_address",
                "type" => "input",
                "value" => 'asdasd'
            ],
        ];


        foreach ($paymob_accept_fields as $key => $value) {
            Setting::updateOrCreate([
                'key' => $value['name'],
                'item_id' => $paymob_accept_id->id,
                'type' => 'payment'
            ], [
                'value' => $value['value'],'input_type' => $value['type']
            ]);
        }



        //flutterwave
        $flutterwave_id = PaymentCoin::updateOrCreate([
            'title' => 'flutterwave',
        ], [
            'photo' => 'flutterwave.jpeg',
            'status' => 1,
        ]);

        $flutterwave_fields = [
            'new_1' => [
                "name" => "is_flutterwave_active",
                "type" => "input",
                "value" => true
            ],
            'new_2' => [
                "name" => "flutterwave_secret_key",
                "type" => "input",
                "value" => "apple team"

            ],
            'new_3' => [
                "name" => "flutterwave_public_key",
                "type" => "input",
                "value" => "Sit dignissimos aliq"
            ],
            'new_4' => [
                "name" => "flutterwave_hash",
                "type" => "input",
                "value" => 'asdasd'
            ],
            'new_5' => [
                "name" => "flutterwave_payment_address",
                "type" => "input",
                "value" => 'asdasd'
            ],
        ];


        foreach ($flutterwave_fields as $key => $value) {
            Setting::updateOrCreate([
                'key' => $value['name'],
                'item_id' => $flutterwave_id->id,
                'type' => 'payment'
            ], [
                'value' => $value['value'],'input_type' => $value['type']
            ]);
        }




        //paystack
        $paystack_id = PaymentCoin::updateOrCreate([
            'title' => 'paystack',
        ], [
            'photo' => 'paystack.jpeg',
            'status' => 1,
        ]);

        $paystack_fields = [
            'new_1' => [
                "name" => "is_paystack_active",
                "type" => "input",
                "value" => true
            ],
            'new_2' => [
                "name" => "paystack_public_key",
                "type" => "input",
                "value" => "apple team"

            ],
            'new_3' => [
                "name" => "paystack_secret_key",
                "type" => "input",
                "value" => "Sit dignissimos aliq"
            ],
            'new_4' => [
                "name" => "paystack_merchant_email",
                "type" => "input",
                "value" => 'asdasd'
            ],
            'new_5' => [
                "name" => "paystack_return_url",
                "type" => "input",
                "value" => 'asdasd'
            ],
            'new_6' => [
                "name" => "paystack_payment_address",
                "type" => "input",
                "value" => 'asdasd'
            ],
        ];


        foreach ($paystack_fields as $key => $value) {
            Setting::updateOrCreate([
                'key' => $value['name'],
                'item_id' => $paystack_id->id,
                'type' => 'payment'
            ], [
                'value' => $value['value'],'input_type' => $value['type']
            ]);
        }



                //sslcommerz
                $sslcommerz_id = PaymentCoin::updateOrCreate([
                    'title' => 'sslcommerz',
                ], [
                    'photo' => 'sslcommerz.jpeg',
                    'status' => 1,
                ]);

                $sslcommerz_fields = [
                    'new_1' => [
                        "name" => "is_sslcommerz_active",
                        "type" => "input",
                        "value" => true
                    ],
                    'new_2' => [
                        "name" => "sslcommerz_store_id",
                        "type" => "input",
                        "value" => "apple team"

                    ],
                    'new_3' => [
                        "name" => "sslcommerz_store_password",
                        "type" => "input",
                        "value" => "Sit dignissimos aliq"
                    ],
                    'new_4' => [
                        "name" => "sslcommerz_payment_address",
                        "type" => "input",
                        "value" => 'asdasd'
                    ],
                ];


                foreach ($sslcommerz_fields as $key => $value) {
                    Setting::updateOrCreate([
                        'key' => $value['name'],
                        'item_id' => $sslcommerz_id->id,
                        'type' => 'payment'
                    ], [
                        'value' => $value['value'],'input_type' => $value['type']
                    ]);
                }
    }
}
