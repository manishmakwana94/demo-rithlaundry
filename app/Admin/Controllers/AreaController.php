<?php

namespace App\Admin\Controllers;

use App\Models\Area;
use App\Models\Region;
use App\Models\Status;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AreaController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Area';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Area());

        $grid->column('id', __('Id'));
        $grid->column('region_id', __('Region'))->display(function($regions){
            $regions = Region::where('id',$regions)->value('region');
                return $regions;
        });
        $grid->column('area_name', __('Area Name'));
        $grid->column('delivery_cost', __('Delivery Cost'));
        $grid->column('status', __('Status'))->display(function($status){
            $status_name = Status::where('id',$status)->value('status_name');
            if ($status == 1) {
                return "<span class='label label-success'>$status_name</span>";
            } else {
                return "<span class='label label-danger'>$status_name</span>";
            }
        });
        $grid->disableExport();
        $grid->actions(function ($actions) {
            $actions->disableView();
        });
        $grid->filter(function ($filter) {
            //Get All status
            $statuses = Status::pluck('status_name', 'id');
            
            $filter->like('area_name', 'Area Name');
            $filter->equal('status', 'Status')->select($statuses);
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
        $show = new Show(Area::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('area_name', __('Area name'));
        $show->field('delivery_cost', __('Delivery cost'));
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
        $form = new Form(new Area());
        $statuses = Status::pluck('status_name', 'id');
        $regions = Region::pluck('region', 'id');
        
        $form->select('region_id', __('Region'))->options($regions)->rules(function ($form) {
            return 'required';
        });
        $form->text('area_name', __('Area Name'))->rules(function ($form) {
            return 'required|max:100';
        });
        $form->decimal('delivery_cost', __('Delivery Cost'))->rules(function ($form) {
            return 'required|integer|min:0';
        });
        $form->select('status', __('Status'))->options($statuses)->default(1)->rules(function ($form) {
            return 'required';
        });
        
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
