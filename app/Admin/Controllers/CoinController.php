<?php

namespace App\Admin\Controllers;

use App\Models\Coin;
use Firebase\JWT\JWT;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use Database\Seeders\config;
use Illuminate\Http\Request;
use Encore\Admin\Layout\Content;
use Encore\Admin\Auth\Permission;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Encore\Admin\Controllers\HasResourceActions;

class CoinController extends MainController
{
    use HasResourceActions;

    public $permission_name = 'coins';
    public $hiddenColumns = [

    ];

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Coin);

        $grid->id( __ ('ID'));
        $grid->usd( __ ('usd'));
        $grid->coin( __ ('coin'));
//        $grid->first_charge_coin('first_charge_coin');
//        $grid->status('status');
//        $grid->discount_code('discount_code');
//        $grid->discount_code_expire_in('discount_code_expire_in');
//        $grid->extra_value('extra_value');
//        $grid->extra_value_end_in('extra_value_end_in');
//        $grid->created_at(trans('admin.created_at'));
//        $grid->updated_at(trans('admin.updated_at'));
        $this->extendGrid ($grid);
        $grid->disableExport();
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Coin::findOrFail($id));

        $show->id('ID');
        $show->usd('usd');
        $show->coin('coin');
//        $show->first_charge_coin('first_charge_coin');
//        $show->status('status');
//        $show->discount_code('discount_code');
//        $show->discount_code_expire_in('discount_code_expire_in');
//        $show->extra_value('extra_value');
//        $show->extra_value_end_in('extra_value_end_in');
//        $show->created_at(trans('admin.created_at'));
//        $show->updated_at(trans('admin.updated_at'));
        $this->extendShow ($show);
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Coin);

        $form->display( __ ('ID'));
        $form->text('usd', __ ('usd'));
        $form->text('coin', __ ('coin'));
//        $form->text('first_charge_coin', 'first_charge_coin');
//        $form->text('status', 'status');
//        $form->text('discount_code', 'discount_code');
//        $form->text('discount_code_expire_in', 'discount_code_expire_in');
//        $form->text('extra_value', 'extra_value');
//        $form->text('extra_value_end_in', 'extra_value_end_in');
//        $form->display(trans('admin.created_at'));
//        $form->display(trans('admin.updated_at'));

        return $form;
    }

    protected function fetchData()
    {
        return 
        
        $response = Http::get('https://api.appstoreconnect.apple.com/v1/inAppPurchases/{id}', [
            'apiKey' => 'J27XVDV4X5',
        ]);
    }

    protected function createCoin(Request $request)
    {
        //$unique_id = $data['apple_id'];
        $teamId = config('apple.apple_team_id'); // Use the correct environment variable name
        $keyId = "GNDGZ4LFR4"/*config('apple.apple_key_id')*/; // Use the correct environment variable name
        $redirectUri = config('apple.apple_redirect_uri'); // Use the correct environment variable name
        $iat = strtotime('now');
        $exp = strtotime('+60days');
        $keyContent = file_get_contents(config('apple.service_file'));
        $token = JWT::encode([
            'iss' => '732cdce3-47cc-4d3d-ba88-8d2a84701f77',
            'iat' => $iat,
            'exp' => $exp,
            'aud' => 'https://appleid.apple.com',
            
        ], $keyContent, 'ES256', $keyId);

       // $paymentData = $request->input('paymentData');
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Content-Type' => 'application/json',
        ])->post('https://api.appstoreconnect.apple.com/v2/inAppPurchases', [
            'data' => [
                "type"       => "inAppPurchases",
                'attributes' => [
                    "name"                        => "Seattle Neighborhood Coffee Map",
                    "productId"                   => "MAPNEIGHBORHOODS",
                    "inAppPurchaseType"           => "CONSUMABLE",
                    "reviewNote"                  => "This is a neighborhood map for helping to find awesome coffee shops.",
                    "availableInAllTerritories"   => true,
                    
                ],
                'relationships' => [
                    'app'       => [
                        'data' => [
                            "type"  => "apps",
                            'id' => "6446148572",
                        ]
                    ]
                ]
            ]
        ]);
        return Common::apiResponse (true,'',$response,200);
    }

    protected function setPrice(Request $request)
    {
        $keyId = "GNDGZ4LFR4"/*config('apple.apple_key_id')*/; // Use the correct environment variable name
        $iat = strtotime('now');
        $exp = strtotime('+60days');
        $keyContent = file_get_contents(config('apple.service_file'));
        $token = JWT::encode([
            'iss' => '732cdce3-47cc-4d3d-ba88-8d2a84701f77',
            'iat' => $iat,
            'exp' => $exp,
            'aud' => 'https://appleid.apple.com',
            
        ], $keyContent, 'ES256', $keyId);
        $response = Http::withHeaders([
            'Authorization' => $token,
            'Content-Type' => 'application/json',
        ])->post('https://api.appstoreconnect.apple.com/v2/inAppPurchases', [

            "data" => [
                "type"          => "inAppPurchases",
                "id"            => $request['id'],
                "attributes"    => [],
                "relationships" => [
                    "prices"      => [
                        "data"      => [
                            [
                                "type"  => "inAppPurchasePrices",
                                "id"    => "${price1}"
                            ]
                        ]
                    ]
                ]
            ],
            "included"       => [
                [
                    "type"       => "inAppPurchasePrices",
                    "id"         => "${price1}",
                    "attributes" => [
                        "startDate" => null
                    ],
                    "relationships"    => [
                        "inAppPurchaseV2" => [
                            "data"         => [
                                "type"       => "inAppPurchasesV2",
                                "id"         => $request['id']
                            ]
                        ],
                        "inAppPurchasePricePoint"     => [
                            "data"        => [
                                "type"      => "inAppPurchasePricePoints",
                                "id"        => "NjQ0NjQ1MjYxNV91c181"
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }
}
