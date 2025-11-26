<?php

namespace Modules\Wallet\Entities;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class WalletFields extends Model
{
    use HasTranslations;

    public $translatable = ['title', 'placeholder'];
    protected $fillable = ['wallet_template_id', 'title', 'type', 'is_required', 'order', 'placeholder'];
}
