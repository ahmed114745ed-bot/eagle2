<?php

namespace App\Admin\Forms;

use Encore\Admin\Form;
use Encore\Admin\Form\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TabsFrom extends Form
{

    public function update($id, $data = null)
    {
        $data = ($data) ?: request()->all();

        $isEditable = $this->isEditable($data);

        if (($data = $this->handleColumnUpdates($id, $data)) instanceof Response) {
            return $data;
        }

        /* @var Model $this ->model */
        $builder = $this->model();

        if ($this->isSoftDeletes) {
            $builder = $builder->withTrashed();
        }

        $this->model = $builder->with($this->getRelations())->findOrFail($id);

        $this->setFieldOriginalValue();

        // Handle validation errors.
        if ($validationMessages = $this->validationMessages($data)) {
            if (!$isEditable) {
                return back()->withInput()->withErrors($validationMessages);
            }

            return response()->json(['errors' => Arr::dot($validationMessages->getMessages())], 422);
        }

        if (($response = $this->prepare($data)) instanceof Response) {
            return $response;
        }

        DB::transaction(function () {
            $updates = $this->prepareUpdate($this->updates);

            foreach ($updates as $column => $value) {
                /* @var Model $this ->model */
                $this->model->setAttribute($column, $value);
            }

            $this->model->save();

            $this->updateRelation($this->relations);
        });

        if (($result = $this->callSaved()) instanceof Response) {
            return $result;
        }

        if ($response = $this->ajaxResponse(trans('admin.update_succeeded'))) {
            return $response;
        }

        return $this->redirectAfterUpdate($id);
    }


    protected function redirectAfterUpdate($key)
    {
        $resourcesPath = $this->resource(-1);

        return $this->redirectAfterSaving($resourcesPath, $key);
    }



    protected function redirectAfterSaving($resourcesPath, $key)
    {
        \Log::info('🔀 [TabsFrom] redirectAfterSaving', [
            'resourcesPath' => $resourcesPath,
            'key' => $key,
            'request_uri' => \request()->getUri(),
            'previous_field' => request(Builder::PREVIOUS_URL_KEY),
            'session_return_url' => session('return_url'),
            'after_save' => request('after-save'),
        ]);

        $redirectUrl = session('return_url');
        if ($redirectUrl){
            session()->forget('return_url');
            $url =  $redirectUrl;
        }else if (request('after-save') == 1) {
            // continue editing
            $url = rtrim($resourcesPath, '/')."/{$key}/edit";
        } elseif (request('after-save') == 2) {
            // continue creating
            $url = rtrim($resourcesPath, '/').'/create';
        } elseif (request('after-save') == 3) {
            // view resource
            $url = rtrim($resourcesPath, '/')."/{$key}";
        } else {
            $url = request(Builder::PREVIOUS_URL_KEY) ?: $resourcesPath;
        }

        admin_toastr(trans('admin.save_succeeded'));

        return redirect($url);
    }


}
