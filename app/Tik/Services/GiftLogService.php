<?php

namespace App\Tik\Services;

/**
 * 
 */
class GiftLogService
{
    private $packageService;

    public function __construct()
    {
        if (class_exists(\Utd\Gifts\Services\GiftLogService::class)) {
            try {
                $this->packageService = app(\Utd\Gifts\Services\GiftLogService::class);
            } catch (\Exception $e) {
                \Log::error('GiftLogService: Package service not available', ['error' => $e->getMessage()]);
            }
        }
        
        if (!$this->packageService) {
            \Log::warning('GiftLogService: Running without Gifts package. Some features may not work.');
        }
    }

    public function __call($method, $arguments)
    {
        if ($this->packageService) {
            return $this->packageService->$method(...$arguments);
        }
        
        throw new \Exception("GiftLogService: Method '{$method}' requires Gifts package to be installed.");
    }

    /**
     * Proxy methods for IDE support
     */
    public function sendGift($request, $updateUserWhenSendGift)
    {
        return $this->__call('sendGift', func_get_args());
    }

    public function sendTestGift($request, $updateUserWhenSendGift)
    {
        return $this->__call('sendTestGift', func_get_args());
    }

    public function userGiftIfo($id, $type, $startDate, $endDate, $perPage, $page)
    {
        return $this->__call('userGiftIfo', func_get_args());
    }
}
