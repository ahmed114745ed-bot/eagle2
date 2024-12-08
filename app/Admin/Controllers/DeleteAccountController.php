<?php

namespace App\Admin\Controllers;

use App\Models\DeleteAccount;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;

class DeleteAccountController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
   

    public function index(Content $content)
    {
        return $content
            ->title(trans('delete-accounts'))
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->title(trans('delete-accounts'))
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->title(trans('delete-accounts'))
            ->body($this->form()->edit($id));
    }

    public function create(Content $content)
    {
        return $content
            ->title(trans('delete-accounts'))
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DeleteAccount());

        $grid->column('id', __('Id'));
        $grid->column('title', __('Title'));
        $grid->column('image', __('Image'))->image('', 50);
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
        $show = new Show(DeleteAccount::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
        $show->field('image', __('Image'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new DeleteAccount());

        $form->table('entries', __('data'), function ($table) {
            $table->textarea('title', __('Title'))->required();
            $table->image('image', __('Image'))->required();
        });

        $form->saving(function (Form $form) {
            $entries = $form->entries; // This is already an array
    
            if (is_array($entries)) {
                foreach ($entries as $entry) {
                    DeleteAccount::create([
                        'title' => $entry['title'],
                        'image' => $entry['image'],
                    ]);
                }
            }
    
            // Prevent saving the entries as a single field in the database
            unset($entries);
        });
    
        return $form;
    }
}
