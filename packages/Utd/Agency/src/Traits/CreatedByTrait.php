<?php

namespace Utd\Agency\Traits;

use Illuminate\Support\Facades\Auth;

trait CreatedByTrait
{
    protected static function bootCreatedByTrait()
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
            } elseif (class_exists('Admin') && \Admin::user()) { // Check for Admin facade/class
                $model->created_by = \Admin::user()->id;
            }
        });
    }
}
