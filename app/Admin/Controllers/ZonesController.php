<?php

namespace App\Admin\Controllers;

use App\Models\Manager;
use App\Models\Zone;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use App\Models\Admin as AdminModel;
class ZonesController extends AdminController
{

        /**
         * Title for current resource.
         *
         * @var string
         */
        protected $title = '';
        public function index(Content $content)
        {
            return $content
                ->title(trans('zones'))
                ->body($this->grid());
        }
        /**
         * Make a grid builder.
         *
         * @return Grid
         */
        protected function grid()
        {
            $grid = new Grid(new Zone());
    
            $grid->column('id', __('ID'))->sortable();
            $grid->column('name', __('Name'))->sortable();
            $grid->column('coordinates', __('coordinates'))->display(function ($coordinates) {
                if (!$coordinates) return 'N/A';
    
                // $data = json_decode($coordinates, true);
                // return isset($data['latitude'], $data['longitude']) ? "{$data['latitude']}, {$data['longitude']}" : 'Invalid Data';
            });
            $grid->column('created_at', __('created'))->sortable();
    
            return $grid;
        }

     
    
        /**
         * Get form for creating/editing.
         *
         * @return Form
         */
        protected function form()
        {
            $form = new Form(new Zone());
        
            $form->text('name', __('zone_name'))->required();
            $managers = AdminModel::where('app_manager_id', '!=', 0)->where('zone_id','=',0)
            ->get()
            ->pluck('username', 'id'); 
          
    
            $form->select('manager_id', __('manager'))
            ->options($managers)
            ->required();        
            $form->hidden('coordinates')->default('');


            $form->html('<pre id="coord-display"
            style="
             background:  var(--box-background-color);
                color:  var(--primary-color);
            "
            ></pre>', __('my_zone'));

            $form->html('<div id="map"  style="height: 400px; border: 1px solid #ccc; margin-top: 10px; 
               
            "></div>', __('select_area_on_map'));
        
            $form->saving(function ($form) {
           
                // التحقق من وجود الإحداثيات
                if (!$form->coordinates) {
                    admin_error('error', 'select_area_on_map');
                    return back();
                }
            
                // إذا كانت الإحداثيات على شكل سلسلة مفصولة بفواصل (كما في المثال)
                if (is_string($form->coordinates)) {
                    // تحويل الإحداثيات إلى مصفوفة
                    $coordinates = explode('),(', trim($form->coordinates, '()'));
                    // التأكد من أن الإحداثيات تحتوي على قيم
                    if (count($coordinates) < 3) {
                        admin_error('error', 'coordinates_must_have_three_points');
                        return back();
                    }
            
                    // تحويل الإحداثيات إلى تنسيق Polygon (lng, lat)
                    $polygonCoordinates = [];
                    foreach ($coordinates as $coord) {
                        $coords = explode(',', $coord);
                        if (count($coords) == 2) {
                            // إضافة الإحداثيات [lng, lat]
                            $polygonCoordinates[] = new Point(floatval($coords[1]), floatval($coords[0]));
                        } else {
                            admin_error('error', 'invalid_coordinates_format');
                            return back();
                        }
                    }
            
                    // إضافة النقطة الأولى في النهاية لإغلاق الشكل (Polygon)
                    $polygonCoordinates[] = $polygonCoordinates[0];
            
                    // إنشاء كائن LineString باستخدام الإحداثيات المُعالجة
                    $lineString = new LineString($polygonCoordinates);
            
                    // إنشاء كائن Polygon باستخدام LineString
                    $form->coordinates = new Polygon([$lineString]);


                }
            });
            
            $form->saved(function (Form $form) {
                if (!$form->model()->manager_id) {
                    return;
                }
            
                $managerDash = AdminModel::find($form->model()->manager_id);
            
                if (!$managerDash) {
                    return; // إذا لم يتم العثور على المدير، لا تكمل العملية
                }
            
                $managerDash->zone_id = $form->model()->id;
                $managerDash->save();
            
                if ($managerDash->app_manager_id) {
                    Manager::where('id', $managerDash->app_manager_id)
                        ->update([
                            'zone_id' => $form->model()->id
                        ]);
                }
            });
            
            return $form;
        }
        
      
        
        
    
    
   
}
