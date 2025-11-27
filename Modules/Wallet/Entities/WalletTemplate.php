<?php

namespace Modules\Wallet\Entities;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class WalletTemplate extends Model
{
    use HasTranslations;

    public $translatable = ['title'];
    protected $fillable = ['title', 'type', 'minimum', 'transfer_fee'];
}
