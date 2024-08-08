<?php

namespace Modules\SpecialId\Entities;

use App\Models\User;
use App\Models\Ware;
use Encore\Admin\Form\Field\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class SpecialHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];
    protected $table   = 'special_id_histories';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function ware()
    {
        return $this->belongsTo(Ware::class);
    }
  

}
