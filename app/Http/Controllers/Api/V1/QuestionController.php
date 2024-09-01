<?php

namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Services\AllGameService;
use App\Helpers\Common;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function questions()
    {
        $questions = Question::where("status",1)->get();
        return Common::apiResponse(1, '', $questions);
    }

    public function send_mail_to_customer_service()
    {
        
    }
}
