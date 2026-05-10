<?php

namespace App\Http;

//use App\Http\Middleware\AdminOneMiddleware;
use App\Http\Middleware\IpMiddleware;
use App\Http\Middleware\AuthenticateWeb;
use App\Http\Middleware\CheckLoginAdmin;
use App\Http\Middleware\AgencyMiddleware;
use App\Http\Middleware\AdminIpMiddleware;
use App\Http\Middleware\DisablePjaxForOctane;
use Modules\SuperAdmin\Http\Middleware\PreviewSuperAdmin;
use App\Http\Middleware\UserBanMiddleware;
use App\Http\Middleware\GeneralBanMiddleware;
use App\Http\Middleware\AdminGeneralBanMiddleware;
use App\Http\Middleware\WebAgencyFeatureEnable;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Modules\SalaryTransaction\Http\Middleware\EnsureAgencyFeatureEnabled;
use Modules\ServerControl\Http\Middleware\ConfigMiddleware;
use KevinSoft\MultiLanguage\Middlewares\MultiLanguageMiddleware;


class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Fruitcake\Cors\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\OctaneCacheClearing::class,
        \App\Http\Middleware\ForcePusherRefresh::class,
        \App\Http\Middleware\RefreshThemeConfig::class,
        DisablePjaxForOctane::class,
        \App\Http\Middleware\SecurityHeaders::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\SetCountry::class,
        ],

        'api' => [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\LogApiRequestResponse::class,
            'ip' => IpMiddleware::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array<string, class-string|string>
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'localization' => \App\Http\Middleware\Localization::class,
        'multiLanguage' => MultiLanguageMiddleware::class,
        'enhancedMultiLanguage' => \App\Http\Middleware\EnhancedMultiLanguage::class,
        'agency' => AgencyMiddleware::class,
        'ip' => IpMiddleware::class,
        'adminIp' => AdminIpMiddleware::class,
        'generalBan' => GeneralBanMiddleware::class,
        'adminGeneralBan' => AdminGeneralBanMiddleware::class,
        'checkLatestToken' => \App\Http\Middleware\CheckLatestToken::class,
        'userBan' => UserBanMiddleware::class,
        //        'rate_limiting' => \App\Http\Middleware\RateLimitingMiddleware::class,
        'checkCpu' => \App\Http\Middleware\CheckCpu::class,
        'configM' => ConfigMiddleware::class,
        'appFeatureEnable' => \App\Http\Middleware\AppFeatureEnable::class,
        'verify.signature' => \App\Http\Middleware\VerifyGameSignature::class,
        'verify.gamecoin.signature' => \App\Http\Middleware\VerifyGameCoinSignature::class,
        'verify.game.userInformation.signature' => \App\Http\Middleware\VerifyGameLeaderCCUserInformationMiddleWare::class,
        'octane.pusher.config2' => \App\Http\Middleware\OctanePusherConfigv2::class,
        'verify.game.signature' => \App\Http\Middleware\VerifyLeaderCCMiddleWare::class,

        //        'decrypt.data' => \App\Http\Middleware\DecryptDataMiddleware::class,
        'admin.auth' => AuthenticateWeb::class,
        'prevent-delete' => \App\Http\Middleware\PreventDelete::class,
        'auth.redirect' => CheckLoginAdmin::class,
        'clear.session' => \App\Http\Middleware\ClearSessionAfterRequest::class,
        'verify.fawry.signature' => \App\Http\Middleware\VerifyFawrySignature::class,
        'verify.utdFawry.signature' => \App\Http\Middleware\VerifyUtdFawrySignature::class,
        'verify.payMob.signature' => \App\Http\Middleware\VerifyPayMobSignature::class,
        'verify.paypal.webhook' => \App\Http\Middleware\VerifyPayPalWebhook::class,
        'verify.utdpay.webhook' => \App\Http\Middleware\VerifyUtdPayWebhook::class,
        'verify.codapay.webhook' => \App\Http\Middleware\VerifyCodapayWebhook::class,
        'production.error' => \App\Http\Middleware\StopInProduction::class,
        //        'utd.decreptHeader' => \App\Http\Middleware\UtdDecreptHeader::class,
        'timezone' => \App\Http\Middleware\SetUserTimezone::class,
        'agencyFeature' => EnsureAgencyFeatureEnabled::class,
        'web-agency-feature' => WebAgencyFeatureEnable::class,
        'ban.user.actions' => \App\Http\Middleware\CheckUserBan::class,
        'local' => \App\Http\Middleware\LocalOnly::class,
        'preview.superadmin' => PreviewSuperAdmin::class,
        'update.last.seen' => \App\Http\Middleware\UpdateLastSeen::class,
        'charisma.badge' => \App\Http\Middleware\CharismaBadgeMiddleware::class,
        'room.cup' => \App\Http\Middleware\RoomCupMiddleware::class,
        'room.boom' => \App\Http\Middleware\RoomBoomMiddleware::class,
        'pk.live' => \App\Http\Middleware\PkLiveMiddleware::class,
        'remaining.diamond.action' => \App\Http\Middleware\RemainingDiamondsMiddleware::class,

        'optional.sanctum' => \Modules\Form\Http\Middleware\OptionalSanctum::class,
        'check.allowed.app' => \Modules\RoomCup\Http\Middleware\CheckAllowedApp::class,
        'host.level' => \App\Http\Middleware\HostLevelMiddleware::class,
        'host.level.action' => \App\Http\Middleware\HostLevelActionMiddleWare::class,


        'verify.pusher' => \App\Http\Middleware\VerifyPusherSignature::class,
        'moment.allowed' => \Modules\Moment\Http\Middleware\CheckAllowedMoment::class,
        'verify.utd.signature' => \App\Http\Middleware\VerifyUtdSignature::class,
        'auth.rate.limit' => \App\Http\Middleware\AuthRateLimiter::class,
    ];
}
