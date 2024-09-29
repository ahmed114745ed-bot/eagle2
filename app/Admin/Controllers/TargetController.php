<?php

namespace App\Admin\Controllers;

use App\Models\Target;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Request;

class TargetController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'agencies';
    public $hiddenColumns = [

    ];



    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Target);
        $grid->model()->orderBy('diamonds');

        $grid->id( __ ('ID'));
        $grid->level(__('target no'));

        $grid->diamonds(__('diamonds'))->display(function($column, Grid\Column $value) {
            $value = $value->getOriginal();
            return number_format($value);
        })->editable ();
        $grid->usd(__('usd'))->editable ();;

        $grid->hours(__('hours'))->editable ();
        $grid->days(__('days'))->editable ();
        $grid->column(('reel'),__('real'))->display(function($value) {

            $reel= explode(',', $this->reel);

            $update=  $reel[0] != '' ||  $reel[0] != null ?$reel[0]: 0;
            $like = $reel[1] ?? 0 ;
            $commit = $reel[2]?? 0 ;

            return "<span style=\"color: #000f;\"> ".   __('admin.update')  . "$update</span>
            <br>
             <span style=\"color: #000f ;\">".   __('admin.like')  . "$like</span>
             <br>
             <span style=\"color: #000f ;\">".   __('admin.comment')  . "$commit </span>
             ";
        });
        $grid->column(('moment'),__('Moment'))->display(function($value) {

            $moment= explode(',', $this->moment);

            $update=  $moment[0] != '' ||  $moment[0] != null ?$moment[0]: 0;
            $like = $moment[1] ?? 0 ;
            $commit = $moment[2]?? 0 ;

            return "<span style=\"color: #000f;\"> ".   __('admin.update')  . "$update</span>
            <br>
             <span style=\"color: #000f ;\">".   __('admin.like')  . "$like </span>
             <br>
             <span style=\"color: #000f ;\"> ".   __('admin.comment')  . "$commit </span>
             ";
        });

//        $grid->img('img');
        $grid->agency_share(__('agency share').'(%)')->display(function($column, Grid\Column $value) {
            $value = $value->getOriginal();
            return number_format($value, 2);
        })->editable ();
        $this->extendGrid ($grid);
        $grid->disableExport();
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Target::findOrFail($id));

        $show->id('ID');
        $show->level('target no');
        $show->diamonds('diamonds');
//        $show->minuts('minuts');
        $show->hours('hours');
        $show->days('days');
//        $show->img('img');
//        $show->usd('usd');
//        $show->coin('coin');
//        $show->gold('gold');
//        $show->created_at(trans('admin.created_at'));
//        $show->updated_at(trans('admin.updated_at'));
        $this->extendShow ($show);
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Target);


        $form->display(__ ('ID'));
        $form->number('level', __('target no'));
        $form->number('diamonds', __('diamonds'));
        $form->decimal('usd', __('usd'));
//        $form->text('coin', 'coin');
//        $form->text('gold', 'gold');
//        $form->text('minuts', 'minuts');
        $form->number('hours', __('hours'));
        $form->number('days', __('days'));
//        $form->text('img', 'img');
        $form->decimal('agency_share', __('agency share').'(%)');
        $form->html('',('<h1>Reel</h1>'));
        $form->hidden('reel', 'reel');
        $form->number('reel1', __('uploadReel'))->default(function ($form) {
            $reel = $form->model()->reel;
            $str    = @explode(',', $reel)[0];
            return $str == null || $str == '' ? 0: $str;
        });
        $form->number('reel2', __('LikeReel'))->default(function ($form) {
            $reel = $form->model()->reel;

            return @explode(',', $reel )[1] ?? 0;
        });;
        $form->number('reel3', __('commentReel'))->default(function ($form) {
            $reel = $form->model()->reel;

            return @explode(',', $reel )[2] ?? 0;
        });
        $form->html('',('<h1>Moment</h1>'));
        $form->hidden('moment', 'moment');

        $form->number('moment1', __('uploadMoment'))->default(function ($form) {
            $moment = $form->model()->moment;
            $str    = @explode(',', $moment)[0];
            return $str == null || $str == '' ? 0: $str;
        });
        $form->number('moment2', __('likeMoment'))->default(function ($form) {
            $moment = $form->model()->moment;

            return @explode(',', $moment )[1] ?? 0;
        });
        $form->number('moment3', __('commentMoment'))->default(function ($form) {
            $moment = $form->model()->moment;

            return @explode(',', $moment )[2] ?? 0;
        });


        return $form;

    }

    public function update ( $id )
    {
        $data   = \request()->all();
        if (isset($data['reel1'])) {
            $reel1  = $data['reel1'];
            $reel2  = $data['reel2'];
            $reel3  = $data['reel3'];
            $values = [
                $reel1,
                $reel2,
                $reel3,
            ];

            $data = array_merge( $data, ['reel' => implode(" ,",$values)]);
            unset($data['reel1']);
            unset($data['reel2']);
            unset($data['reel3']);
        }

        if (isset($data['moment1'])) {
            $moment1 = $data['moment1'];
            $moment2 = $data['moment2'];
            $moment3 = $data['moment3'];
            $values2 = [
                $moment1,
                $moment2,
                $moment3,
            ];

            $data = array_merge( $data, [ 'moment' => implode(" ,",$values2)]);
            unset($data['moment1']);
            unset($data['moment2']);
            unset($data['moment3']);
        }



        Request::replace($data);
        return $this->form()->update($id);
    }

    public function store (  )
    {

        $data = \request()->all();
        $values = [
            $data['reel1'],
            $data['reel2'],
            $data['reel3'],
        ];

        $values2 = [
            $data['moment1'],
            $data['moment2'],
            $data['moment3'],
        ];

        $data = array_merge( $data, ['reel' => implode(" ,",$values), 'moment' => implode(" ,",$values2)]);

        unset($data['reel1']);
        unset($data['reel2']);
        unset($data['reel3']);
        unset($data['moment1']);
        unset($data['moment2']);
        unset($data['moment3']);
        Request::replace($data);

//        Target::create($data);
        return $this->form()->store();
    }
}
