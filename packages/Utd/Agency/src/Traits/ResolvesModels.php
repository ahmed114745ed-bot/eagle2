<?php

namespace Utd\Agency\Traits;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Trait for resolving models from configuration
 * Used in controllers to avoid hard-coded App\Models dependencies
 */
trait ResolvesModels
{
    /**
     * Resolve any model from config
     *
     * @param  string  $modelKey  The config key (e.g., 'user', 'agency')
     * @param  string|null  $default  Default class if not configured
     */
    protected function resolveModel(string $modelKey, ?string $default = null): ?string
    {
        $modelClass = config("agency-dependencies.dependencies.models.{$modelKey}")
                   ?? config("agency-package.models.{$modelKey}")
                   ?? $default;

        if (! $modelClass) {
            Log::warning("Agency Package: Model '{$modelKey}' not configured");

            return null;
        }

        if (! class_exists($modelClass)) {
            Log::warning('Agency Package: Model class does not exist', [
                'key' => $modelKey,
                'class' => $modelClass,
            ]);

            return null;
        }

        return $modelClass;
    }

    /**
     * Get a query builder for a model safely
     *
     * @return \Illuminate\Database\Eloquent\Builder|null
     */
    protected function queryModel(string $modelKey)
    {
        $modelClass = $this->resolveModel($modelKey);

        if (! $modelClass) {
            return null;
        }

        try {
            return $modelClass::query();
        } catch (Exception $e) {
            Log::error("Agency Package: Failed to create query for model '{$modelKey}'", [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get User model class
     */
    protected function getUserModel(): ?string
    {
        return $this->resolveModel('user', \App\Models\User::class);
    }

    /**
     * Get Agency model class
     */
    protected function getAgencyModel(): ?string
    {
        return $this->resolveModel('agency', \Utd\Agency\Entities\Agency::class);
    }

    /**
     * Get AgencyJoinRequest model class
     */
    protected function getAgencyJoinRequestModel(): ?string
    {
        return $this->resolveModel('agency_join_request', \Utd\Agency\Entities\AgencyJoinRequest::class);
    }

    /**
     * Get AgencyUserJob model class
     */
    protected function getAgencyUserJobModel(): ?string
    {
        return $this->resolveModel('agency_user_job', \Utd\Agency\Entities\AgencyUserJob::class);
    }

    /**
     * Get UserSallary model class
     */
    protected function getUserSallaryModel(): ?string
    {
        return $this->resolveModel('user_salary', \App\Models\UserSallary::class);
    }

    /**
     * Get Admin model class
     */
    protected function getAdminModel(): ?string
    {
        return $this->resolveModel('admin', \App\Models\Admin::class);
    }

    /**
     * Get Country model class
     */
    protected function getCountryModel(): ?string
    {
        return $this->resolveModel('country', \App\Models\Country::class);
    }

    /**
     * Get GiftLog model class
     */
    protected function getGiftLogModel(): ?string
    {
        return $this->resolveModel('gift_log', \Utd\Gifts\Entities\GiftLog::class);
    }

    /**
     * Get Config model class
     */
    protected function getConfigModel(): ?string
    {
        return $this->resolveModel('config', \App\Models\Config::class);
    }

    /**
     * Get UsersJoinedAgency model class
     */
    protected function getUsersJoinedAgencyModel(): ?string
    {
        return $this->resolveModel('users_joined_agency', \Utd\Agency\Entities\UsersJoinedAgency::class);
    }

    /**
     * Get Setting model class
     */
    protected function getSettingModel(): ?string
    {
        return $this->resolveModel('setting', \App\Models\Setting::class);
    }

    /**
     * Get CountryRate model class
     */
    protected function getCountryRateModel(): ?string
    {
        return $this->resolveModel('country_rate', \App\Models\CountryRate::class);
    }

    /**
     * Get Gift model class
     */
    protected function getGiftModel(): ?string
    {
        return $this->resolveModel('gift', \Utd\Gifts\Entities\Gift::class);
    }

    /**
     * Get Room model class
     */
    protected function getRoomModel(): ?string
    {
        return $this->resolveModel('room', \Utd\Room\Entities\Room::class);
    }

    /**
     * Get Chat model class
     */
    protected function getChatModel(): ?string
    {
        return $this->resolveModel('chat', \App\Models\Chat::class);
    }

    /**
     * Get Notification model class
     */
    protected function getNotificationModel(): ?string
    {
        return $this->resolveModel('notification', \App\Models\Notification::class);
    }

    /**
     * Get UserGift model class
     */
    protected function getUserGiftModel(): ?string
    {
        return $this->resolveModel('user_gift', \Utd\Gifts\Entities\UserGift::class);
    }

    /**
     * Get Charge model class
     */
    protected function getChargeModel(): ?string
    {
        return $this->resolveModel('charge', \App\Models\Charge::class);
    }

    /**
     * Get CoinLog model class
     */
    protected function getCoinLogModel(): ?string
    {
        return $this->resolveModel('coin_log', \App\Models\CoinLog::class);
    }

    /**
     * Get Bd model class
     */
    protected function getBdModel(): ?string
    {
        return $this->resolveModel('bd', \Utd\Bd\Entities\Bd::class);
    }

    /**
     * Get UserTarget model class
     */
    protected function getUserTargetModel(): ?string
    {
        return $this->resolveModel('user_target', \App\Models\UserTarget::class);
    }

    /**
     * Get PaymentGateway model class
     */
    protected function getPaymentGatewayModel(): ?string
    {
        return $this->resolveModel('payment_gateway', \App\Models\PaymentGateway::class);
    }

    /**
     * Get Language model class
     */
    protected function getLanguageModel(): ?string
    {
        return $this->resolveModel('language', \App\Models\Language::class);
    }

    /**
     * Get Ware model class
     */
    protected function getWareModel(): ?string
    {
        return $this->resolveModel('ware', \App\Models\Ware::class);
    }

    /**
     * Get SalaryTrx model class
     */
    protected function getSalaryTrxModel(): ?string
    {
        return $this->resolveModel('salary_trx', \App\Models\SalaryTrx::class);
    }
}
