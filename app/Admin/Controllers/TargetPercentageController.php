<?php

namespace App\Admin\Controllers;

use Encore\Admin\Layout\Content;
use Illuminate\Support\HtmlString;
use App\Http\Controllers\Controller;
use App\Admin\Controllers\MainController;


class TargetPercentageController extends MainController
{
    public $permission_name = 'target-percentage';
    public function index(Content $content)
    {
        $route = 'admin.target-percentage';

        $hours =  settings()->get('hours');
        $days =  settings()->get('days');
        $moments =  settings()->get('moments');
        $reels = settings()->get('reels');

        $errors = session()->get('errors');
        $errorMessage =  $errors ? $errors->first('msg') :  null;

        $form = '<form method="POST" action="' . route($route) . '"  >';
        $form .= csrf_field();
        $form .= '<h1  class="control-label text-center">Target Percentage</h1>';
        if ($errorMessage) {
            $form .= '<div class="error-message" style="color: red; font-size: 20px; text-align: center;">' . $errorMessage . '</div>';
             }
        $form .= '<label for="hours" class="control-label">Hours:</label>';
        $form .= '<input type="text" id="hours" name="hours" placeholder="hours" value="' . $hours .'"  class="inputs_cus_form">';
        $form .= '<label for="days" class="control-label">Days:</label>';
        $form .= '<input type="text" id="days" name="days" placeholder="days"  value="' . $days .'" class="inputs_cus_form">';
        $form .= '<label for="moments" class="control-label">Moments:</label>';
        $form .= '<input type="text" id="moments" name="moments" placeholder="moments" value="' . $moments .'" class="inputs_cus_form">';
        $form .= '<label for="reels" class="control-label">Reels:</label>';
        $form .= '<input type="text" id="reels" name="reels" placeholder="reels" value="' . $reels .'" class="inputs_cus_form">';

        $form .= '<button type="submit" class="button_form_cus">Submit</button>';

        $form .= '</form>';





        return  parent::index($content
            ->title(trans('Salary Distribution Ratio'))
            ->body(new HtmlString($form)));
    }


}
