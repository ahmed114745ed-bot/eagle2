<?php

namespace App\Admin\Controllers;

use App\Models\Admin;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class AdminAuthController extends MainController
{
    public $permission_name = 'admin-profile';

    public function index(Content $content){
        return $content->title(__('user profile'))->body(view('admin_profile'));
    }

}
