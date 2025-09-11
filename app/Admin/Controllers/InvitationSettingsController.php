<?php

namespace App\Admin\Controllers;

use App\Models\Config;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Layout\Row;

class InvitationSettingsController extends AdminController
{
    protected $title = '';

    public function index(Content $content)
    {
        $content = $content->title(__($this->title));

            $content = $content->row(function (Row $row) {
                $row->column(12, $this->grid());
                $row->column(12, $this->form());
            });

            return $content;
    }

    protected function grid()
    {
        $stop_invite_code = settings()->get('stop_invite_code');

        return (new Box(
            title: __('admin.Actions'),
            content: view('admin.grid.users.invitationCodeStop', compact(['stop_invite_code'])),
        ));
    }

    protected function form()
    {
        $form = new Form(new Config());
    
        $form->setAction(admin_url('invitation-code/settings')); 
    
        $form->decimal('value', __('invitation.earn_percentage'))
            ->default($this->getValue('earn_from_invitation'))
            ->rules('required|numeric|min:0|max:100')
            ->help(__('invitation.earn_percentage_help'));
    
        $form->hidden('name')->default('earn_from_invitation');
    
        $form->decimal('host_reward', __('invitation.host_reward'))
            ->default($this->getValue('invitation_host_reward'))
            ->rules('required|numeric|min:0')
            ->help(__('invitation.host_reward_help'));
    
        $form->decimal('invitee_reward', __('invitation.invitee_reward'))
            ->default($this->getValue('invitation_invitee_reward'))
            ->rules('required|numeric|min:0')
            ->help(__('invitation.invitee_reward_help'));
    
        $form->saving(function (Form $form) {
            $earn = Config::updateOrCreate(['name' => 'earn_from_invitation'], ['value' => $form->value]);
            $host = Config::updateOrCreate(['name' => 'invitation_host_reward'], ['value' => $form->host_reward]);
            $invitee = Config::updateOrCreate(['name' => 'invitation_invitee_reward'], ['value' => $form->invitee_reward]);
        
         
        
            admin_toastr(__('invitation.saved_successfully'), 'success');
            return back();
        });
    
        return $form;
    }
    

    private function getValue(string $key, $default = 0)
    {
        return Config::where('name', $key)->value('value') ?? $default;
    }

}
