<?php

namespace App\Admin\Controllers;

use App\Models\CustomerWalletHistory;
use App\Models\Customer;
use App\Models\Status;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CustomerWalletHistoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Customer Wallet Histories';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CustomerWalletHistory);

        $grid->column('id', __('Id'));
        $grid->column('customer_id', __('Customer Name'))->display(function($customer_id){
            return Customer::where('id',$customer_id)->value('customer_name');
        });
        $grid->column('message', __('Message'));
        $grid->column('amount', __('Amount'));
        
        $grid->disableExport();
        //$grid->disableCreation();
        $grid->actions(function ($actions) {
            // $actions->disableView();
            // $actions->disableView();
            // $actions->disableView();
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
        $show = new Show(CustomerWalletHistory::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('Customer_id', __('Customer id'))->as(function($customer_id){
            return Customer::where('id',$customer_id)->value('owner_name');
        });
        $show->field('message', __('Message'));
        $show->field('amount', __('Amount'));
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
        $form = new Form(new CustomerWalletHistory);
        $vendor = Customer::pluck('customer_name', 'id');

        $form->select('customer_id', __('Customer name'))->options($vendor)->rules(function ($form) {
            return 'required';
        });
        $form->text('message', __('Message'))->rules(function ($form) {
            return 'required';
        });
        $form->text('message_ar', __('Message Arabic'))->rules(function ($form) {
            return 'required';
        });
        $form->select('type', __('Type'))->options(['1' => 'Credit', '2'=> 'Debit'])->rules(function ($form) {
            return 'required';
        });
        $form->decimal('amount', __('Amount'))->rules(function ($form) {
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
