<?php

namespace Modules\Chat\Entities;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatLetter extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $table = 'chat_letters';

    protected $guarded = ['id'];
}
