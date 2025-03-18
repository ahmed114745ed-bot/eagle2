<?php

namespace App\Admin\Controllers;

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

class ZonesController extends AdminController
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
    
            $grid->column('id', __('ID'))->sortable();
            $grid->column('name', __('Name'))->sortable();
            $grid->column('coordinates', __('Coordinates'))->display(function ($coordinates) {
                if (!$coordinates) return 'N/A';
    
                // $data = json_decode($coordinates, true);
                // return isset($data['latitude'], $data['longitude']) ? "{$data['latitude']}, {$data['longitude']}" : 'Invalid Data';
            });
            $grid->column('created_at', __('Created At'))->sortable();
    
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
        
            $form->text('name', __('اسم المنطقة'))->required();
        
            $form->hidden('coordinates')->default('');


            $form->html('<pre id="coord-display"
            style="
             background:  var(--box-background-color);
                color:  var(--primary-color);
            "
            ></pre>', __('الإحداثيات الحالية'));

            $form->html('<div id="map"  style="height: 400px; border: 1px solid #ccc; margin-top: 10px; 
               
            "></div>', __('حدد المنطقة على الخريطة'));
        
            $form->saving(function ($form) {
            
                // التحقق من وجود الإحداثيات
                if (!$form->coordinates) {
                    admin_error('خطأ', 'يجب تحديد المنطقة على الخريطة!');
                    return back();
                }
            
                // إذا كانت الإحداثيات على شكل سلسلة مفصولة بفواصل (كما في المثال)
                if (is_string($form->coordinates)) {
                    // تحويل الإحداثيات إلى مصفوفة
                    $coordinates = explode('),(', trim($form->coordinates, '()'));
            
                    // التأكد من أن الإحداثيات تحتوي على قيم
                    if (count($coordinates) < 3) {
                        admin_error('خطأ', 'يجب أن تحتوي الإحداثيات على 3 نقاط على الأقل لتشكيل شكل Polygon.');
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
                            admin_error('خطأ', 'تنسيق الإحداثيات غير صحيح.');
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
            
            
            return $form;
        }
        
      
        
        
    
    
   
}
