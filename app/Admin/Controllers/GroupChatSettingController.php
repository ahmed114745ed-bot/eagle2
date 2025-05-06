<?php

namespace App\Admin\Controllers;

use App\Models\Config;
use Illuminate\Support\HtmlString;

use Encore\Admin\Layout\Content;

class GroupChatSettingController extends MainController
{
    public $permission_name = 'updates_group_chat';



    public function index(Content $content)
    {
        $config = Config::where('name', 'group_chat')->first();

        $form = $this->buildConfigForm(
            route: 'admin.update-config-group-chat',
            config: $config,
            label: __('admin.price'),
            inputType: 'number',
            inputAttributes: [
                'min' => '1',
                'placeholder' => 'android_min_version',
                'class' => 'form-input'
            ]
        );

        return parent::index(
            $content->title(trans('Settings'))
                    ->body(new HtmlString($form))
        );
    }

    protected function buildConfigForm(string $route, ?Config $config, string $label, string $inputType, array $inputAttributes = []): string
    {
        return <<<HTML
        <div class="form-wrapper">
            <form method="POST" action="{$this->escapeHtml(route($route))}" class="config-form">
                {$this->csrfField()}

                {$this->getHiddenIdField($config)}

                <div class="form-group">
                    <label class="form-label">{$this->escapeHtml($label)}</label>
                    {$this->buildFormInput($inputType, $config->value ?? '', $inputAttributes)}
                </div>

                <div class="form-footer">
                    <button type="submit" class="submit-btn">{$this->escapeHtml(__('admin.submit'))}</button>
                </div>
            </form>
        </div>

        {$this->getFormStyles()}
        HTML;
    }

    protected function csrfField(): string
    {
        return csrf_field();
    }

    protected function getHiddenIdField(?Config $config): string
    {
        return $config ? '<input type="hidden" name="id" value="'.$this->escapeHtml($config->id).'">' : '';
    }

    protected function buildFormInput(string $type, $value, array $attributes = []): string
    {
        $attrs = '';
        foreach ($attributes as $name => $val) {
            $attrs .= ' '.$name.'="'.$this->escapeHtml($val).'"';
        }

        return '<input type="'.$this->escapeHtml($type).'" name="value" value="'.$this->escapeHtml($value).'"'.$attrs.'>';
    }

    protected function escapeHtml($value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8', true);
    }

    protected function getFormStyles(): string
    {
        return <<<'CSS'
        <style>
        .form-wrapper {
            max-width: 500px;
            margin: 20px auto 0;
            padding: 0 15px;
        }

        .config-form {
            padding: 25px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: 1px solid #eaeaea;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-input {
            width: 100%;
            padding: 10px 12px;
            font-size: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            transition: border 0.2s ease;
        }

        .form-input:focus {
            border-color: #3490dc;
            outline: none;
            box-shadow: 0 0 0 2px rgba(52,144,220,0.1);
        }

        .form-footer {
            display: flex;
            justify-content: flex-end;
            margin-top: 25px;
        }

        .submit-btn {
            padding: 10px 20px;
            background-color: #3490dc;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 15px;
            transition: background 0.2s ease;
        }

        .submit-btn:hover {
            background-color: #2779bd;
        }
        </style>
        CSS;
    }
}
