<?php

namespace Modules\AgencyApp\Entities;

use App\Models\User;
use App\Models\Agency;
use App\Models\Country;
use Encore\Admin\Form\Field\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class AdditionalInfo extends Model
{
    protected $guarded = ['id'];

    public function agency()
    {
        return $this->belongsTo(Agency::class,); 
    }

    public function country()
    {
        return $this->belongsTo(Country::class,); 
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
