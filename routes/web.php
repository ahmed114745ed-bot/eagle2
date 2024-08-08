<?php
use Pusher\Pusher;
use App\Models\Room;
use App\Models\User;
use App\Models\Agency;
use App\Helpers\Common;
use GuzzleHttp\Psr7\Request;
use App\Models\MultiLanguage;
use Encore\Admin\Facades\Admin;


use App\Models\AgencyJoinRequest;
use Illuminate\Support\Facades\DB;
use App\Notifications\AcceptAgency;
use App\Services\RoomLevelServices;
use Illuminate\Support\Facades\Route;
use App\Http\Services\RoomGameServices;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\PersonalAccessToken;
use Modules\CP\Http\Services\CpServices;
use App\Admin\Controllers\CoinController;
use App\Admin\Controllers\UserController;
use Illuminate\Http\Request as HttpRequest;
use App\Http\Controllers\addTOjesonController;
use App\Http\Controllers\AddTargetToJsonController;
use Modules\Reals\Http\Controllers\RealsController;
use App\Admin\Controllers\AppSitiingCOnfigController;
use App\Admin\Controllers\TargetPercentageController;
use App\Jobs\ExportGiftLogsJob;
use Modules\Public\Http\Services\UserCounterServices;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Modules\Public\Http\Controllers\web\UpgradeLevelController;
use KevinSoft\MultiLanguage\Http\Controllers\MultiLanguageController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/t1', function () {
//    $i = 0;
//    while ($i < 100000000){
//        $i++;
//        echo $i;
//        echo '<br>';
//    }
});

Route::get('/deleted/users', function (){
    $users = User::withTrashed()->where('deleted_at', '!=', null)->select('id','name')->get();
    return response()->json([
        'data'=> $users
    ]);
});

Route::get('/t2', function () {
    return gethostname();
});
Route::get('/fetch_data/{id}', [CoinController::class, 'fetchData']);
Route::get('/create_coin', [CoinController::class, 'createCoin']);
Route::get('/create_coin/{id}', [CoinController::class, 'setPrice']);
Route::get('/git_image', [\App\Http\Controllers\Api\V1\GiftController::class, 'gitImage']);
Route::get('/custom-page', [AppSitiingCOnfigController::class, 'index'])->name('admin.AppSitiingCOnfigController');
Route::get('/', function (HttpRequest $request) {
    // return view ('welcome');
//     $user=$request->user_id;
//     $month=intval($request->month);
//     $year=intval($request->year);

//  $all=   Common::CurantUsdHistoryOwner($user ,$month,$year);


//     return $all;

// $user  = App\Models\User::query()->find(1900);
// $token = $user->createToken('api_token')->plainTextToken;
// echo $token;

});
//Route::post('postAddSitin', [addTOjesonController::class,'postAddSitin'])->name('postAddSitin');


Route::prefix ('payment')->group (function (){
    Route::get ('payment-success',[\App\Http\Controllers\Web\PaymentController::class,'success']);
    Route::get ('payment-fail',[\App\Http\Controllers\Web\PaymentController::class,'fail']);
});

Route::get('/page/{name}', function ($name) {
    $page =  \App\Models\Page::query ()->where ('name',$name)->firstOrFail ();
    return (app()->getLocale() == 'ar'? $page->content : ($page->content_en ?? $page->content));
})->middleware('localization');

Route::get('/clear', function() {

    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    Artisan::call('view:cache');

    return "Cleared!";

 });

Route::get('for-test', [\App\Http\Controllers\TestController::class, 'index']);
Route::get('/payment-callback/{payment?}',[\App\Http\Controllers\TestController::class,'payment_verify'])->name('payment-verify');




Route::get('test-table', [\App\Http\Controllers\TestController::class,'index']);


 Route::get('/admin/custom-export-users', [\App\Admin\Controllers\ExportController::class, 'usersSallaryTargets'
 ])->name('custom-export-users');

 Route::get('/admin/agency-export-report', [\App\Admin\Controllers\ExportController::class, 'usersAgencyTargets'
 ])->name('agency-export-report');



 Route::get('/privacy-policy', function () {
    $page = \App\Models\Page::where("name", "privacy-policy")->first();
    return view('privacy.privacy', ['page' => $page]);
});
Route::get('/generate', function () {

    $user  = App\Models\User::query()->find(1770);
    $token = $user->createToken('api_token')->plainTextToken;
    echo $token;

});


Route::post('postAddSitin', [addTOjesonController::class,'postAddSitin'])->name('postAddSitin');
//Route::get('/custom-page', [AppSitiingCOnfigController::class, 'index'])->name('admin.AppSitiingCOnfigController');


Route::post('targe-percentage', [AddTargetToJsonController::class,'targetPercentage'])->name('target-percentage');
Route::get('test-google-pay', [\App\Http\Controllers\InAppPurchase::class, 'verifyGooglePay']);
Route::get('old_real', [RealsController::class, 'oldReal']);
//Route::get('/test-websocket', [WebsocketController::class, 'index']);
Route::post("send-request-make-rooms-top",[UserController::class,"make_rooms_top"]);
Route::post("send-request-transfer-salary",[UserController::class,"transferSalary"]);
$router->post('ovip-config', [UpgradeLevelController::class,'ovipConfig'])->name('ovip-config');
/*$router->post('group-chat-config', [UpgradeLevelController::class,'group_chat_config'])->name('group-chat-config');

$router->post('reel-config', [UpgradeLevelController::class,'reelConfig'])->name('reel-config');
$router->post('moment-config', [UpgradeLevelController::class,'momentConfig'])->name('moment-config');*/

Route::post("send-request-stop-charge",[UserController::class,"stop_charge"]);

// use App\Classes\UserHandling;
// use App\Models\Agency;

// Route::get('/test-kick-user', function () {
//     // يمكنك استدعاء الكلاس وإنشاء كائن منه
//     $userHandling = new UserHandling();

//     // قم بالحصول على مستخدم معين لاختبار الدالة
//     $user = Agency::find(13);

//     // استدعاء الدالة المطلوبة
//     $userHandling->kickOfAllUsersFromAgency($user);

//     // يمكنك إضافة المزيد من التحكم أو عرض النتائج كما تشاء
//     dd("goood");
// });
//Route::post('update-room-count', [\App\Http\Controllers\Api\V1\Room\EnteranceController::class, 'updateRoomCount']);

Route::get("update-room-socket",function(){

    Room::where('count_room_socket',"!=",0)->whereDoesntHave("roomVisitors")->update(['count_room_socket'=>0,"room_visitor"=>""]);

    return "تم التعديل بنجاح";
});
Route::get("update-join-date",function(){
    $results = AgencyJoinRequest::with("user")->get();
    foreach($results as $result){
        $result->user->join_agency_date = $result->updated_at;
        $result->user->save();
    }
    return "تم التعديل بنجاح";
});

Route::get("test-room-level",function(){
    $room = Room::find(8);
    $totalPrice = 80;
    (new RoomLevelServices)->update_coins_and_level($room, $totalPrice);
    return "تم التعديل بنجاح";
});

Route::get("test-user-salary",function(){
    $user = User::find(419);

    return $user->salary;
});




Route::get("ufu",function(){
    $value = Config::get('broadcasting.connections.pusher.app_id');

    dd($value);
});

Route::get("download-users",function(){
    return Excel::download(new \App\Exports\UsersExport(), 'users.xlsx');
});

Route::get("download-rooms",function(){
    return Excel::download(new \App\Exports\RoomsExport(), 'rooms.xlsx');
});

Route::get("download-agency",function(){
    return Excel::download(new \App\Exports\AgenciesExport(), 'agencies.xlsx');
});

Route::get("download-gift-log",function(){
    $filename = 'gift_logs_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
    $filePath = 'exports/' . $filename;

    dispatch(new ExportGiftLogsJob($filePath));
    // return Excel::download(new \App\Exports\GiftLogExport(), 'gift_logs.xlsx');
});

