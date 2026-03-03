<?php

namespace Utd\Gifts\Http\Controllers\Admin;

use App\Admin\Controllers\MainController;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Utd\Gifts\Entities\GiftLog;
use Illuminate\Http\Request;

/**
 * GiftLogController
 * Controller لإدارة سجلات الهدايا من لوحة التحكم
 */
class GiftLogController extends MainController
{
    use HasResourceActions;

    public $permission_name = 'giftlog';
    protected $title = 'Gift Logs';

    /**
     * Index interface
     */
    public function index(Content $content)
    {
        return parent::index($content
            ->title(__($this->title))
            ->body($this->grid()));
    }

    /**
     * Show interface
     */
    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(__($this->title))
            ->body($this->detail($id)));
    }

    /**
     * Make a grid builder
     */
    protected function grid()
    {
        $grid = new Grid(new GiftLog());

        $grid->model()->orderBy('id', 'desc');

        $grid->id('ID')->sortable();
        $grid->column('giftName', __('Gift Name'));
        $grid->column('giftNum', __('Quantity'));
        $grid->column('giftPrice', __('Price'));

        $grid->column('sender.name', __('Sender'))
            ->display(function ($val) {
                /** @var \Utd\Gifts\Entities\GiftLog $this */
                return $this->sender ? $this->sender->name : '-';
            });

        $grid->column('receiver.name', __('Receiver'))
            ->display(function ($val) {
                /** @var \Utd\Gifts\Entities\GiftLog $this */
                return $this->receiver ? $this->receiver->name : '-';
            });

        $grid->column('roomOwner.name', __('Room Owner'))
            ->display(function ($val) {
                /** @var \Utd\Gifts\Entities\GiftLog $this */
                return $this->roomOwner ? $this->roomOwner->name : '-';
            });

        $grid->column('created_at', __('Sent At'))->sortable();

        $grid->disableCreateButton();
        $grid->disableActions();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('giftName', __('Gift Name'));
            $filter->equal('sender_id', __('Sender ID'));
            $filter->equal('receiver_id', __('Receiver ID'));
            $filter->between('created_at', __('Sent Date'))->datetime();
        });

        $grid->export(function ($export) {
            $export->filename('Gift_Logs_' . date('Y-m-d'));
            $export->column('id', 'ID');
            $export->column('giftName', 'Gift Name');
            $export->column('giftNum', 'Quantity');
            $export->column('giftPrice', 'Price');
            $export->column('sender_id', 'Sender ID');
            $export->column('receiver_id', 'Receiver ID');
            $export->column('created_at', 'Sent At');
        });

        return $grid;
    }

    /**
     * Make a show builder
     */
    protected function detail($id)
    {
        $show = new Show(GiftLog::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('giftName', __('Gift Name'));
        $show->field('giftNum', __('Quantity'));
        $show->field('giftPrice', __('Price'));
        $show->field('sender.name', __('Sender'));
        $show->field('receiver.name', __('Receiver'));
        $show->field('roomOwner.name', __('Room Owner'));
        $show->field('created_at', __('Sent At'));

        return $show;
    }

    /**
     * Statistics page
     */
    public function statistics(Content $content)
    {
        // يمكن إضافة صفحة إحصائيات متقدمة هنا
        return $content
            ->title(__('Gift Statistics'))
            ->description(__('View detailed gift statistics'));
    }

    /**
     * Export gifts logs
     */
    public function export(Request $request)
    {
        // يمكن إضافة تصدير متقدم هنا
        return response()->json([
            'status' => true,
            'message' => __('Export started')
        ]);
    }
}
