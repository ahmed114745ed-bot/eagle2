<?php

namespace App\Traits;

use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\MessageBag;

trait PreventDeleteIfCreatedByDeveloper
{
    public static function preventDeleteByDeveloper()
    {
    
        static::deleting(function ($model) {
            if (self::shouldBlock($model)) {
                throw new \Exception(__('delete_not_allowed_div'));

            }
        });

        static::updating(function ($model) {
            if (self::shouldBlock($model)) {
                self::failWithToastr(__('update_not_allowed_div'));
                return false;
            }
        });

    }

    public static function preventCreateByDeveloper()
    {
        static::creating(function ($model) {
            $admin = Admin::user();
            $developerId = env('DEVELOPER_ADMIN_ID', 1);
            if (!$admin || $admin->id != $developerId) {
                self::failWithToastr(__('create_not_allowed_dev'));
                return false;
            }
        });
    }

    protected static function shouldBlock($model)
    {
        $isApi = Request::is('api/*');
        $admin = Admin::user();
        $developerId = env('DEVELOPER_ADMIN_ID', 1); 
        return !$isApi &&
            $admin &&
            ($model->created_by == $developerId || is_null($model->created_by)) &&
           intval( $admin->id) !== intval($developerId);
    }

    protected static function failWithToastr($message)
    {
        $error = new MessageBag([
            'title'   => __('error_title_div'),
            'message' => $message,
        ]);

        session()->flash('error', $error);
        throw new \Exception($message);

    }

        // static::deleting(function ($model) {

        //     $isApi = Request::is('api/*');

        //     if (!$isApi) {
        //         if (
        //             ($model->created_by == 2 || is_null($model->created_by)) &&
        //             Admin::user() &&
        //             Admin::user()->id !== 2
        //         ) {
        //             throw new \Exception('❌ غير مسموح بحذف هذا السجل لأنه أنشئ بواسطة المطور.');
        //         }
        //     }
        // });
}
