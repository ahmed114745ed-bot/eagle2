<?php

namespace Modules\SuperAdmin\Http\Controllers\SuperAdmin;

use Encore\Admin\Controllers\HandleController as BaseHandleController;
use Illuminate\Http\Request;

class HandleController extends BaseHandleController
{
    public function handleAction(Request $request)
    {
        \Log::info('SuperAdmin HandleController handleAction called', [
            'request_url' => $request->url(),
            'request_method' => $request->method(),
            'request_path' => $request->path(),
            'request_data' => $request->all(),
            'headers' => $request->headers->all()
        ]);
        
        return parent::handleAction($request);
    }
}