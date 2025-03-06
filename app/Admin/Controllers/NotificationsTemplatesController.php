<?php

namespace App\Admin\Controllers;

use App\Models\Notification;
use App\Models\NotificationTemplate;
use App\Models\NotificationTranslation;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\helper\HelperType; 
require_once app_path('helper/helperType.php'); 

class NotificationsTemplatesController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'NotificationTemplate';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    
     protected function grid()
     {
         $grid = new Grid(new Notification());
     
         $grid->column('key', __('Key'));
     
         $languages = ['ar' => 'Arabic', 'en' => 'English', 'tr' => 'Turkish', 'hi' => 'Indian'];
     
         foreach ($languages as $code => $lang) {
             $grid->column($code, __($lang))->display(function () use ($code, $lang) {
                 $translation = $this->translations->where('language', $code)->first();
                 $message = $translation ? htmlentities($translation->message) : '-';
                 
                 return "<a href='#' class='view-lang' data-lang='{$lang}' data-value='{$message}'>عرض</a>";
             });
         }
     
         $grid->script = <<<SCRIPT
             document.addEventListener("DOMContentLoaded", function() {
                 document.querySelectorAll('.view-lang').forEach(function(element) {
                     element.addEventListener('click', function(event) {
                         event.preventDefault();
                         var lang = this.getAttribute('data-lang');
                         var message = this.getAttribute('data-value');
                         alert("اللغة: " + lang + "\\n" + "الرسالة: " + message);
                     });
                 });
             });
         SCRIPT;
     
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
    $show = new Show(Notification::with('translations')->findOrFail($id));

    $show->field('key', __('Name'));

    $languages = ['ar' => 'Arabic', 'en' => 'English', 'tr' => 'Turkish', 'hi' => 'Indian'];

    foreach ($languages as $code => $lang) {
        $show->field($code, __($lang))->as(function () use ($code) {
            $translation = $this->translations->where('language', $code)->first();
            return $translation ? $translation->message : '-';
        });
    }

    return $show;
}


    /**
     * Make a form builder.
     *
     * @return Form
     */
    // protected function form()
    // {
    //     $form = new Form(new Notification());
    
    //     $form->text('key', __('Key'))->rules(function ($form) {
    //         return $form->model()->id
    //             ? 'required|max:255|unique:notifications,key,' . $form->model()->id
    //             : 'required|max:255|unique:notifications,key';
    //     });
    
    //     $languages = ['ar' => 'Arabic', 'en' => 'English', 'tr' => 'Turkish', 'hi' => 'Indian'];
    
    //     // foreach ($languages as $code => $lang) {
    //     //     $form->textarea("title_{$code}", __("{$lang} Title"))->rules('nullable|max:255');
    //     //     $form->textarea("message_{$code}", __("{$lang} Message"))->rules('nullable');
    //     // }

    // foreach ($languages as $code => $lang) {
    //     $form->textarea("title_{$code}", __("{$lang} Title"))
    //         ->default(function ($form) use ($code) {
    //             if ($form->model()->id) {
    //                 $translation = $form->model()->translations->where('language', $code)->first();
    //                 return $translation ? $translation->title : null;
    //             }
    //             return null;
    //         })
    //         ->rules('nullable|max:255');

    //     $form->textarea("message_{$code}", __("{$lang} Message"))
    //         ->default(function ($form) use ($code) {
    //             if ($form->model()->id) {
    //                 $translation = $form->model()->translations->where('language', $code)->first();
    //                 return $translation ? $translation->message : null;
    //             }
    //             return null;
    //         })
    //         ->rules('nullable');
    // }

    //     $form->ignore(['title_ar', 'message_ar', 'title_en', 'message_en', 'title_tr', 'message_tr', 'title_hi', 'message_hi']);

    
    //     return $form;
    // }

    // تضمين الملف للوصول إلى المتغيرات

    protected function form()
    {
        $form = new Form(new Notification());
    
        $form->text('key', __('Key'))->rules(function ($form) {
            return $form->model()->id
                ? 'required|max:255|unique:notifications,key,' . $form->model()->id
                : 'required|max:255|unique:notifications,key';
        });
    
        $languages = ['ar' => 'Arabic', 'en' => 'English', 'tr' => 'Turkish', 'hi' => 'Indian'];
    
        $variablesList = implode(', ', VARIABLES);
    
        $form->html(
            "<div style='border: 1px solid var(--primary-hover-alpha) !important; padding: 10px; border-radius: 4px; '>
               <br> 
                " . $variablesList . "
            </div>",
            'Available Variables'
        );
            
        foreach ($languages as $code => $lang) {
            $form->textarea("title_{$code}", __("{$lang} Title"))
                ->default(function ($form) use ($code) {
                    if ($form->model()->id) {
                        $translation = $form->model()->translations->where('language', $code)->first();
                        return $translation ? $translation->title : null;
                    }
                    return null;
                })
                ->rules('nullable|max:255');
    
            $form->textarea("message_{$code}", __("{$lang} Message"))
                ->default(function ($form) use ($code) {
                    if ($form->model()->id) {
                        $translation = $form->model()->translations->where('language', $code)->first();
                        return $translation ? $translation->message : null;
                    }
                    return null;
                })
                ->rules('nullable')
                ->help(__('help_message', ['variables' => $variablesList]))  ;      }
            $form->ignore(['title_ar', 'message_ar', 'title_en', 'message_en', 'title_tr', 'message_tr', 'title_hi', 'message_hi']);

        return $form;
    }
    
    
}    
