<?php

namespace Utd\Form\Entities;

use Utd\Bd\Entities\Bd;
use App\Models\User;
use App\Support\PackageHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormRequest extends Model
{
    use HasFactory;

    protected $table = 'form_requests';

    protected $fillable = [
        'form_template_id',
        'submitted_by',
        'bd_id',
        'name',
        'whatsapp_number',
        'form_template_type',
        'data',
        'country',
        'status',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function template()
    {
        return $this->belongsTo(FormTemplate::class, 'form_template_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function bd()
    {
        return PackageHelper::checkRelation($this, 'bd', 'belongsTo')
            ?? $this->belongsTo(Bd::class, 'bd_id');
    }
}
