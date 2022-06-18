<?php

namespace App\Admin\Controllers;

use App\Models\DeliveryBoy;
use App\Models\Status;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;


class DeliveryBoyController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Delivery Boys';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DeliveryBoy);

        $grid->column('id', __('Id'));
        $grid->column('delivery_boy_name', __('Delivery boy name'));
        $grid->column('phone_number', __('Phone number'));
        $grid->column('email', __('Email'));
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
            
            $filter->like('delivery_boy_name', 'Delivery Boy Name');
            $filter->like('phone_number', 'Phone Number');
            $filter->like('email', 'Email');
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
        $show = new Show(DeliveryBoy::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('delivery_boy_name', __('Delivery boy name'));
        $show->field('phone_number', __('Phone number'));
        $show->field('email', __('Email'));
        $show->field('password', __('Password'));
        $show->field('profile_picture', __('Profile picture'));
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
        $form = new Form(new DeliveryBoy);
        $statuses = Status::pluck('status_name', 'id');
        $form->text('delivery_boy_name', __('Delivery boy name'))->rules(function ($form) {
            return 'required|max:100';
        });
        $form->text('phone_number', __('Phone number'))->rules(function ($form) {
                return 'numeric|digits_between:9,20';
        });
        $form->text('phone_with_code', __('Phone With Code'))->rules('required');
        $form->email('email', __('Email'))->rules(function ($form) {
                return 'required|max:100';
        });
        $form->password('password', __('Password'))->rules(function ($form) {
            return 'required';
        });
        $form->image('profile_picture', __('Profile picture'))->uniqueName()->move('delivery_boys')->rules('required');
        $form->select('status', __('Status'))->options($statuses)->default(1)->rules(function ($form) {
            return 'required';
        });
        $form->saving(function ($form) {
            if($form->password && $form->model()->password != $form->password)
            {
                $form->password = $this->getEncryptedPassword($form->password);
            }
        });
        $form->saved(function (Form $form) {
            $this->update_status($form->model()->id,$form->model()->status);
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
    public function getEncryptedPassword($input, $rounds = 12) {
        $salt = "";
        $saltchars = array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9));
        for ($i = 0; $i < 22; $i++) {
            $salt .= $saltchars[array_rand($saltchars)];
        }
        return crypt($input, sprintf('$2y$%2d$', $rounds) . $salt);
    }

    public function update_status($id,$status){
    
        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();
        $newPost = $database
        ->getReference('delivery_partners/'.$id)
        ->update([
            'status' => $status
        ]);
    }

}
