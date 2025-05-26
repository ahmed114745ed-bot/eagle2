<?php 

namespace App\Admin\Actions;

use App\Models\Agency;
use App\Models\Bd;
use App\Models\UserSallary;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class DeleteBdAction extends RowAction
{
   
        public $name;


        protected $agencyCount = 0;
    
        public function __construct()
        {
            parent::__construct();
            $this->name = __('delete');
        }
    
        public function setModel(Model $model)
        {
            $this->agencyCount = Agency::where('bd_id', $model->app_id)->count();
            return parent::setModel($model);
        }
        public function confirm()
        {
            if ($this->agencyCount > 0) {
                return "This BD has {$this->agencyCount} agencies. If you delete it, agencies will be transferred to the default BD, and salaries deleted. Are you sure?";
            }
    
            return 'Are you sure you want to delete this BD?';
        }
    
        public function handle(Model $model, Request $request)
        {
            if ($model->default == 1) {
                return $this->response()->error('You cannot delete the default BD.')->refresh();
            }
    
            if ($model->created_by == 'owner') {
                return $this->response()->error('You cannot delete a BD created by the owner.')->refresh();
            }
    
            if ($this->agencyCount > 0) {
                $defaultBd = Bd::where('default', 1)->where('id', '!=', $model->app_id)->first();
                if (!$defaultBd) {
                    return $this->response()->error('No default BD found to transfer agencies to.')->refresh();
                }
    
                Agency::where('bd_id', $model->id)->update(['bd_id' => $defaultBd->app_id]);
            }
    
            $model->delete();
    
            return $this->response()->success('BD deleted successfully.')->refresh();
        }
    
       
}
