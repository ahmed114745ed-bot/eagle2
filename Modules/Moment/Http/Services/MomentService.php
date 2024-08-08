<?php

namespace Modules\Moment\Http\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Modules\Moment\Entities\Moment;
use Nwidart\Modules\Facades\Module;

class MomentService extends MomentBaseModelService
{
    public function __construct(Moment $model) { parent::__construct($model); }

    public function show(User $user)
    {
      
    }

    public function create(array $data, int $userId)
    {
    }


    /*
     * $data is = [file, description, categories ids]
     */



    public function delete(int|Module $moment)
    {
        if (gettype($moment) == 'integer') {
            $moment = Moment::query()->find($moment);
        }

        $moment->delete();
    }
}
