<?php

namespace Modules\DynamicTheme\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScreenAllowedWidget extends Model
{
    use HasFactory;

    protected $fillable = [
        'screen_id',
        'widget_id',
    ];

    public function screen()
    {
        return $this->belongsTo(Screen::class);
    }

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }
}
