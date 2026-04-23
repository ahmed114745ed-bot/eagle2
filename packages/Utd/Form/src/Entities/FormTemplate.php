<?php

namespace Utd\Form\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class FormTemplate extends Model
{
    use HasFactory, HasTranslations;

    public $translatable = ['title', 'description', 'admin_notice'];

    protected $fillable = [
        'title',
        'description',
        'admin_notice',
        'form_type',
        'is_active',
        'created_by',
        'can_not_delete',
        'note_text'
    ];

    protected $casts = [];

    public function sections()
    {
        return $this->hasMany(FormSection::class);
    }

    public static function fixDoubleEncodedTranslations()
    {
        foreach (self::all() as $template) {
            foreach (['title', 'description'] as $field) {
                $value = $template->getRawOriginal($field);
                if ($value && is_string($value)) {
                    try {
                        $decoded = json_decode($value, true);
                        if (json_last_error() === JSON_ERROR_NONE && isset($decoded['en'])) {
                            $innerDecoded = json_decode($decoded['en'], true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($innerDecoded)) {
                                foreach ($innerDecoded as $locale => $text) {
                                    $template->setTranslation($field, $locale, $text);
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
            $template->save();
        }
    }
}
