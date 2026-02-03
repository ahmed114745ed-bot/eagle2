<?php

namespace Utd\Agency\Services;

/**
 * Wrapper service for common helper functions
 * This allows the package to work with or without App\Helpers\Common
 */
class AgencyHelperService
{
    protected $commonHelper;
    
    public function __construct()
    {
        if (class_exists(\App\Helpers\Common::class)) {
            $this->commonHelper = \App\Helpers\Common::class;
        }
    }
    
    public function apiResponse($status, $message = '', $data = [], $statusCode = 200, $pagination = '', $dataKey = 'data')
    {
        if ($this->commonHelper) {
            return $this->commonHelper::apiResponse($status, $message, $data, $statusCode, $pagination, $dataKey);
        }
        
        // Fallback implementation
        return response()->json([
            'status' => $status,
            'message' => $message,
            $dataKey => $data,
            'pagination' => $pagination,
        ], $statusCode);
    }
    
    public function getSettingValue($key, $default = null)
    {
        if ($this->commonHelper && method_exists($this->commonHelper, 'getSettingValue')) {
            return $this->commonHelper::getSettingValue($key, $default);
        }
        
        return $default;
    }
    
    public function searchAgency($identifier)
    {
        if ($this->commonHelper && method_exists($this->commonHelper, 'searchAgency')) {
            return $this->commonHelper::searchAgency($identifier);
        }
        
        return null;
    }
    
    public function send_firebase_notification($tokens, $title, $body)
    {
        if ($this->commonHelper && method_exists($this->commonHelper, 'send_firebase_notification')) {
            return $this->commonHelper::send_firebase_notification($tokens, $title, $body);
        }
        
        return null;
    }
    
    public function checkUserAgencyFrozen($user)
    {
        if ($this->commonHelper && method_exists($this->commonHelper, 'checkUserAgencyFrozen')) {
            return $this->commonHelper::checkUserAgencyFrozen($user);
        }
        
        return false;
    }
    
    public function getCoinsValue($key)
    {
        if ($this->commonHelper && method_exists($this->commonHelper, 'getCoinsValue')) {
            return $this->commonHelper::getCoinsValue($key);
        }
        
        return null;
    }
    
    public function getPaginates($data)
    {
        if ($this->commonHelper && method_exists($this->commonHelper, 'getPaginates')) {
            return $this->commonHelper::getPaginates($data);
        }
        
        return '';
    }
    
    public function __call($method, $parameters)
    {
        if ($this->commonHelper && method_exists($this->commonHelper, $method)) {
            return $this->commonHelper::$method(...$parameters);
        }
        
        throw new \BadMethodCallException("Method {$method} does not exist.");
    }
}
