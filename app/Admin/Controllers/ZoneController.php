<?php

namespace App\Admin\Controllers;

use App\Models\Zone;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class ZoneController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Zones';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Zone());

        $grid->column('id', __('Id'));
        $grid->column('fast_delivery_charge', __('Fast Delivery Charge'));
        $grid->column('min_checkout_amount', __('Min Checkout Amount'));
        $grid->column('name', __('Name'));
        $grid->column('Polygon')->display(function () {;
            return "<a href='".URL::to('/')."/admin/create_zones/".$this->id."'><span class='label label-warning'>Create Polygon</span></a>";
        });
        $grid->column('View Polygon')->display(function () {
            return "<a href='".URL::to('/')."/admin/view_zones/".$this->id."'><span class='label label-warning'>View Polygon</span></a>";
        });

        $grid->disableExport();
        $grid->actions(function ($actions) {
            $actions->disableView();
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
        $show = new Show(Zone::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('fast_delivery_charge', __('Fast delivery charge'));
        $show->field('min_checkout_amount', __('Min checkout amount'));
        $show->field('name', __('Name'));
        $show->field('polygon', __('Polygon'));
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
        $form = new Form(new Zone());

        $form->decimal('fast_delivery_charge', __('Fast Delivery Charge'))->rules('required');
        $form->decimal('min_checkout_amount', __('Min Checkout Amount'))->rules('required');
        $form->text('name', __('Name'));
        $form->hidden('polygon', __('Polygon'));

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
    protected function create_zones($id)
    {
      
        return view('zones.create_zones',compact('id'));
    }

    protected function view_zones($id)
    {
        return view('zones.view_service_zones')->with(['id'=>$id]);
    }

    function save_polygon(Request $request)
    {
      $coordsrc = $request->coordStr;
      $polygon = Zone::findOrFail($request->id);
      $polygon->polygon = $coordsrc;
      $polygon->save();
      return json_encode(['success'=>"success create"]);
    }
    function get_polygon(Request $request)
    {
      $id = $request->id;
  
      // $polygon = Zone::findOrFail($request->id)->polygon;
      $latlongdata = [];
      $polygon = Zone::findOrFail($id)->polygon;
       $data =  explode(";" , rtrim($polygon, ';'));
       foreach ($data as $key => $values) {
        $latlong =  explode("," , rtrim($values, ','));
          $latlongdata[] = [
            'lat'=>  $latlong[0],
            'lng' => $latlong[1] 
          ];
      }
      return json_encode(['success'=>"success create","data" => $latlongdata]);
    }
}
