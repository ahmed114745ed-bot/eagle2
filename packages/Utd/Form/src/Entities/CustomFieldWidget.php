<?php

namespace Utd\Form\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class CustomFieldWidget extends Model
{
    use HasFactory, HasTranslations;

    public $translatable = ['widget_name', 'description'];

    protected $fillable = [
        'widget_type',
        'widget_name',
        'description',
        'component_path',
        'default_config',
        'allows_multiple',
        'is_active',
    ];

    protected $casts = [
        'default_config' => 'array',
        'allows_multiple' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function fields()
    {
        return $this->hasMany(FormField::class, 'widget_id');
    }

    public function render($field, $value = null, $attributes = [])
    {
        $config = array_merge(
            $this->default_config ?? [],
            $field->widget_config ?? []
        );

        return view($this->component_path, [
            'field' => $field,
            'widget' => $this,
            'value' => $value,
            'config' => $config,
            'attributes' => $attributes,
        ]);
    }

    public function getData($config = [])
    {
        $mergedConfig = array_merge($this->default_config ?? [], $config);

        if (isset($mergedConfig['data_source'])) {
            switch ($mergedConfig['data_source']) {
                case 'users':
                    return $this->getUsersData($mergedConfig);
                case 'api':
                    return $this->getApiData($mergedConfig);
                case 'static':
                    return $mergedConfig['static_data'] ?? [];
                default:
                    return [];
            }
        }

        return [];
    }

    protected function getUsersData($config)
    {
        $query = User::query()->where('is_active', true);

        if (isset($config['role'])) {
            $query->where('role', $config['role']);
        }

        if (isset($config['roles'])) {
            $query->whereIn('role', $config['roles']);
        }

        return $query->select('id', 'name', 'email', 'role')->get();
    }

    protected function getApiData($config)
    {
        return [];
    }
}
