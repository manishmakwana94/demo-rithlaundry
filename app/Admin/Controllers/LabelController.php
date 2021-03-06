<?php

namespace App\Admin\Controllers;

use App\Models\Label;
use App\Models\Status;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class LabelController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Order Labels';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new Label);

        $grid->column('id', __('Id'));
        $grid->column('label_name', __('Label Name'));
        $grid->column('label_name_ar', __('Label name Arabic'));
        $grid->column('label_for_delivery_boy', __('Label for delivery boy'));
        $grid->column('label_for_delivery_boy_ar', __('Label for delivery boy_ar'));

        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableView();
        });
        $grid->filter(function ($filter) {
            
            
            $filter->like('label_name', 'Label Name');
    
        });
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
        $show = new Show(Label::findOrFail($id));

        $show->field('id', __('Id'))    ;
        $show->field('label_name', __('Label name'));
        $show->field('description', __('Description'));
        $show->field('status', __('Status'));
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
        $statuses = Status::pluck('status_name', 'id');

        $form = new Form(new Label);

        $form->text('label_name', __('Label Name'))->rules(function ($form) {
            return 'required|max:100';
        });
        $form->text('label_name_ar', __('Label name Arabic'))->rules(function ($form) {
            return 'required|max:100';
        });
		$form->text('label_for_delivery_boy', __('Label name for delivery boy'))->rules(function ($form) {
            return 'required|max:100';
        });
		$form->text('label_for_delivery_boy_ar', __('Label name for delivery boy Arabic'))->rules(function ($form) {
            return 'required|max:100';
        });
        $form->textarea('description', __('Description'))->rules(function ($form) {
            return 'required';
        });
        $form->textarea('description_ar', __('Description Arabic'))->rules(function ($form) {
            return 'required';
        });
        $form->image('image', __('Image'))->uniqueName()->move('/images');
        
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete(); 
            $tools->disableView();
        });
        $form->footer(function ($footer) {
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });
        return $form;
    }
}
