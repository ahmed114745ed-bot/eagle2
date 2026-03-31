<?php

namespace App\Admin\Controllers;

use App\Models\AdminLoginLog;
use Encore\Admin\Auth\Database\OperationLog;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;
use Illuminate\Support\Arr;

class LogController extends AdminController
{
    /**
     * {@inheritdoc}
     */
    protected function title()
    {
        return trans('admin.operation_log');
    }

    /**
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AdminLoginLog());

        $grid->model()->orderBy('id', 'DESC');

        $grid->column('id', 'ID')->sortable();
        $grid->column('user.name', 'User');
        $grid->column('method')->display(function ($method) {
            $color = Arr::get(OperationLog::$methodColors, $method, 'grey');

            return "<span class=\"badge bg-$color\">$method</span>";
        });
        $grid->column('path')->label('info');
        $grid->column('ip')->label('primary');
        $grid->column('input')->display(function ($input) {
            $input = json_decode($input, true);
            $input = Arr::except($input, ['_pjax', '_token', '_method', '_previous_']);
            if (empty($input)) {
                return '<code>{}</code>';
            }

            return '<pre>' . json_encode($input, JSON_PRETTY_PRINT | JSON_HEX_TAG) . '</pre>';
        });

        // Location columns
        $grid->column('country')->label('warning');
        $grid->column('city')->label('info');
        $grid->column('region');
        $grid->column('latitude')->hide();
        $grid->column('longitude')->hide();

        // Device columns
        $grid->column('device')->display(function ($device) {
            if (!$device) return '';
            $icons = [
                'Desktop' => '🖥️',
                'Mobile'  => '📱',
                'Tablet'  => '📱',
            ];
            $icon = $icons[$device] ?? '❓';
            return "$icon $device";
        });
        $grid->column('platform')->label('success');
        $grid->column('platform_version');
        $grid->column('browser')->label('info');
        $grid->column('browser_version');
        $grid->column('user_agent')->display(function ($value) {
            if (!$value) return '';
            return '<span title="' . e($value) . '">' . \Str::limit($value, 50) . '</span>';
        })->hide();

        // Session columns
        $grid->column('login_at')->display(function ($value) {
            return $value ?: '';
        });
        $grid->column('logout_at')->display(function ($value) {
            return $value ?: '';
        });
        $grid->column('session_duration_minutes', 'Session (min)')->display(function ($value) {
            if (!$value) return '';
            if ($value < 30) {
                $color = 'yellow';
            } elseif ($value < 120) {
                $color = 'green';
            } else {
                $color = 'purple';
            }
            return "<span class=\"badge bg-$color\">{$value} min</span>";
        });

        $grid->column('created_at', trans('admin.created_at'));

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableEdit();
            $actions->disableView();
        });

        $grid->disableCreateButton();

        $grid->filter(function (Grid\Filter $filter) {
            $userModel = config('admin.database.users_model');

            $filter->equal('user_id', 'User')->select($userModel::all()->pluck('name', 'id'));
            $filter->equal('method')->select(array_combine(OperationLog::$methods, OperationLog::$methods));
            $filter->like('path');
            $filter->equal('ip');
            $filter->like('country');
            $filter->like('city');
            $filter->like('device');
            $filter->like('platform');
            $filter->like('browser');
            $filter->between('created_at', 'Created At')->datetime();
        });

        return $grid;
    }

    /**
     * @param mixed $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $ids = explode(',', $id);

        if (AdminLoginLog::destroy(array_filter($ids))) {
            $data = [
                'status'  => true,
                'message' => trans('admin.delete_succeeded'),
            ];
        } else {
            $data = [
                'status'  => false,
                'message' => trans('admin.delete_failed'),
            ];
        }

        return response()->json($data);
    }
}
