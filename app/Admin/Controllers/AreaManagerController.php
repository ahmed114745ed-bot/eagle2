<?php

namespace App\Admin\Controllers;

use App\Models\Bd;
use App\Models\User;
use App\Models\Agency;
use App\Models\Charge;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\Country;
use App\Models\AreaManager;
use Encore\Admin\Layout\Row;
use App\Enums\PermissionType;
use Encore\Admin\Widgets\Box;
use Illuminate\Support\Carbon;
use Encore\Admin\Facades\Admin;
use App\Models\SuperAdminReward;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;
use App\Enums\Charges\UserTypeEnum;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use App\Admin\Actions\DeleteSuperAdminAction;
use Modules\Milestones\Helpers\MilestoneHelper;

class AreaManagerController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Area Manager';
    public $permission_name = 'area-manager';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(__($this->title))
            ->row(function (Row $row) {
                $row->column(12, $this->descriptionBox());
            })
            ->row(function ($row) {
                $row->column(12, $this->grid());
            }));
    }

    protected function descriptionBox()
    {
        return new Box(
            title: __('areaManagerDescriptionTitle'),
            content: view('admin.grid.area_manager.description')
        );
    }

    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(__($this->title))
            ->body($this->profile($id)));
    }

    public function showPreview(Content $content)
    {
        return $content
            ->title(__($this->title))
            ->body($this->profilePreview());
    }

    public function edit($id, Content $content)
    {
        return parent::edit($id, $content
            ->title(__($this->title))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(__($this->title))
            ->body($this->form()));
    }

    protected function grid()
    {
        $grid = new Grid(new AreaManager());
        $grid->model()->with(['appUser.packs'])->orderByDesc('id');

        $grid->filter(function ($filter) {
            $filter->like('appUser.uuid', __('App User UUID'));
            $filter->like('appUser.name', __('User Name'));
        });

        $grid->column('id', __('Id'));
        $grid->column('username', __('Area Manager'))->display(function ($name) {
            $id = $this->id ?? '-';
            $name = $this->username ?? __('Unknown');
            $url = getImagePath($this->avatar) ?? asset("images/businessman-icon.jpg");
            if (!isImageExists($url)) $url = asset("images/businessman-icon.jpg");
            $image = handleShowImageWithTypes($id, $url, 40, 40);
            $showUrl = url("admin/area-managers/{$id}");

            return "
                <div style='display:flex; align-items:center; gap:10px;'>
                    $image
                    <div>
                        <a href='{$showUrl}' style='text-decoration:none; color:inherit; display:flex; align-items:center; gap:10px;'>
                            <span style='text-decoration:underline; cursor:pointer;'>$name</span>
                        </a>
                        <span style='font-size:smaller;'>ID: $id</span>
                    </div>
                </div>
            ";
        });

        $grid->column('default', __('default'))->display(function () {
            if ($this->default == 1) {
                return '<span style="color:green;">●</span>';
            }
            return '<span style="color:#999;">●</span>';
        });

        $grid->column('appUser.name', __('User'))->display(function ($name) {
            $user = $this->appUser;
            if (!$user) return "<span style='color:red;'>" . __('Not Linked') . "</span>";
            $uid = $user->uuid ?? __('Unknown');
            $url = getImagePath($user->profile?->avatar) ?? asset("images/businessman-icon.jpg");
            $image = handleShowImageWithTypes($this->id, $url, 40, 40);
            $showUrl = url("admin/users/{$user->id}");

            return "
                <div style='display:flex; align-items:center; gap:10px;'>
                    $image
                    <div>
                        <a href='{$showUrl}' style='text-decoration:none; color:inherit; display:flex; align-items:center; gap:10px;'>
                            <span style='text-decoration:underline; cursor:pointer;'>$name</span>
                        </a>
                        <span style='font-size:smaller;'>UUID: $uid</span>
                    </div>
                </div>
            ";
        });

        $grid->column('created_at', __('Created at'))->display(function ($date) {
            $carbonDate = Carbon::parse($date)->locale(App::getLocale());
            return $carbonDate->translatedFormat('d F Y H:i');
        });

        $grid->disableRowSelector();
        $this->extendGrid($grid);

        return $grid;
    }


    /**
     * Make a form builder.
     *
     * @return Form
     */


    protected function form()
    {
        $form = new Form(new AreaManager());
        $this->disableFormTools($form);

        $form->text('name', __('name'));
        // $form->text('username', __('username'))
        //     ->creationRules(['required', "unique:admin_users,username,{{id}}"])
        //     ->updateRules(['required', "unique:admin_users,username,{{id}}"]);
        $form->text('username', trans('admin.username'))
            ->rules(function ($form) {
                // Get the record ID if editing, otherwise null
                $id = $form->model()?->id ?? null;

                // Get the type from request or from existing model when editing
                $type =  PermissionType::AREA_MANAGER->value ?? $form->model()?->type;

                // Default to empty string if not found (avoids SQL issues)
                $type = $type ?? '';

                // Build unique rule with type condition
                return "required|unique:admin_users,username," . ($id ?? 'NULL') . ",id,type," . $type;
            });
        $form->password('password', __('Password'))->rules('required');
        $form->image('avatar', __('img'));



        if ($form->isEditing()) {
            $form->select('app_id', __('validation.select_user'))->options(function ($value) {
                $ops2 = [];
                foreach (User::Where('id', $value)->get() as $user) {
                    $ops2[$user->id] = $user->uuid . '_' . $user->name;
                }
                return $ops2;
            })->ajax('/api/search/users-area-manager', 'id', 'name')
                ->help('لا يمكن التعديل إلا إذا لم يكن هناك مستخدم مرتبط، أو كان المستخدم مرتبطًا لكن تم حذفه.')
            ;
        } else {
            $form->select('app_id', __('validation.select_user'))->options(function ($value) {
                $ops2 = [];
                foreach (User::Where('id', $value)->get() as $user) {
                    $ops2[$user->id] = $user->uuid . '_' . $user->name;
                }
                return $ops2;
            })->ajax('/api/search/users-area-manager', 'id', 'name');
        }

        $this->addPhoneFields($form);

        $this->addMapField($form);

        $form->hidden('type', __('Type'))->value('area-manager');

        $form->saving(function (Form $form) {
            $isEditing = $form->isEditing();
            $superAdmin = AreaManager::where('phone_code', request('phone_code'))
                ->where('phone', request('phone'));

            if ($isEditing) {
                $superAdmin->where('id', '!=', $form->model()->id);
            }

            $exists = $superAdmin->exists();

            if ($exists) {
                $error = new \Illuminate\Support\MessageBag([
                    'title' => 'Error',
                    'message' => trans('you used this phone before'),
                ]);
                return back()->with(compact('error'))->withInput();
            }

            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
        });

        $form->saved(function (Form $form) {
            $areaManager = $form->model();
            $userId = $form->model()->id;

            $coveredCountries = request('covered_countries');
            if ($coveredCountries) {
                $countries = json_decode($coveredCountries, true);
                $countryIds = array_column($countries, 'id');
                Country::whereIn('id', $countryIds)
                    ->update(['area_manager_id' => $userId]);
            }
            $role = DB::table('admin_roles')->where('slug', 'area-manager')->first();
            if ($role && $userId) {
                $exists = DB::table('admin_role_users')
                    ->where('user_id', $userId)
                    ->where('role_id', $role->id)
                    ->exists();

                if (!$exists) {
                    DB::table('admin_role_users')->insert([
                        'user_id' => $userId,
                        'role_id' => $role->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });



        return $form;
    }

    protected function addMapField(Form $form)
    {
        $form->hidden('polygon_coordinates')->default(function ($form) {
            if ($form->model()->polygon_coordinates) {
                return json_encode($form->model()->polygon_coordinates);
            }
            return '[]';
        });

        $form->hidden('covered_countries')->default(function ($form) {
            if ($form->model()->polygon_coordinates && $form->model()->covered_countries) {
                return json_encode($form->model()->covered_countries);
            }
            return '[]';
        });

        $form->html('<div class="form-group">
            <div class="col-sm-12">
                <div id="map" style="height: 500px; width: 100%; border: 1px solid #ddd;"></div>
                <div style="margin-top: 10px;">
                    <button type="button" class="btn btn-primary" id="clear-polygon">' . __('Clear Selection') . '</button>
                    <button type="button" class="btn btn-info" id="get-countries">' . __('Show Selected Countries') . '</button>
                </div>
                <div id="countries-list" style="margin-top: 15px; padding: 10px; background: #f5f5f5; border-radius: 4px; display: none;">
                    <h4>' . __('Selected Countries') . ':</h4>
                    <div id="countries-content"></div>
                </div>
            </div>
        </div>');

        Admin::script($this->mapJs());
    }

    protected function mapJs()
    {
        $apiKey = env('GOOGLE_MAPS_API_KEY', '');

        $translations = json_encode([
            'please_select_area' => __('Please select an area on the map first'),
            'searching_countries' => __('Searching for countries... This may take a few seconds'),
            'searching' => __('Searching...'),
            'no_countries_found' => __('No countries found in the selected area. Try expanding the area.'),
            'found' => __('Found'),
            'country' => __('country'),
            'arabic_name' => __('Arabic Name'),
            'english_name' => __('English Name'),
            'phone_code' => __('Phone Code'),
            'error_fetching' => __('An error occurred while searching for countries. Please check your Google Maps API Key.'),
            'show_selected_countries' => __('Show Selected Countries'),
        ]);

        return <<<JS
        const translations = {$translations};
        
        if (!window.google || !window.google.maps) {
            const script = document.createElement('script');
            script.src = 'https://maps.googleapis.com/maps/api/js?key={$apiKey}&libraries=drawing,geometry';
            script.async = true;
            script.defer = true;
            document.head.appendChild(script);
            script.onload = initMap;
        } else {
            initMap();
        }
    
        let map, drawingManager, currentPolygon;
        let polygonCoordinates = [];
    
        function initMap() {
            if (!document.getElementById('map')) {
                setTimeout(initMap, 100);
                return;
            }
    
            const center = { lat: 26.8206, lng: 30.8025 };
    
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 6,
                center: center,
                mapTypeId: 'roadmap'
            });
    
            drawingManager = new google.maps.drawing.DrawingManager({
                drawingMode: google.maps.drawing.OverlayType.POLYGON,
                drawingControl: true,
                drawingControlOptions: {
                    position: google.maps.ControlPosition.TOP_CENTER,
                    drawingModes: ['polygon']
                },
                polygonOptions: {
                    fillColor: '#2196F3',
                    fillOpacity: 0.3,
                    strokeWeight: 2,
                    strokeColor: '#1976D2',
                    clickable: true,
                    editable: true,
                    zIndex: 1
                }
            });
    
            drawingManager.setMap(map);
    
            const savedCoordinates = document.querySelector('input[name="polygon_coordinates"]');
            if (savedCoordinates && savedCoordinates.value && savedCoordinates.value !== '[]') {
                try {
                    const coords = JSON.parse(savedCoordinates.value);
                    if (coords && coords.length > 0) {
                        drawSavedPolygon(coords);
                        drawingManager.setDrawingMode(null);
                    }
                } catch (e) {
                    console.error('Error parsing saved coordinates:', e);
                }
            }
    
            google.maps.event.addListener(drawingManager, 'polygoncomplete', function(polygon) {
                if (currentPolygon) {
                    currentPolygon.setMap(null);
                }
                currentPolygon = polygon;
                updatePolygonCoordinates();
                
                drawingManager.setDrawingMode(null);
    
                google.maps.event.addListener(polygon.getPath(), 'set_at', updatePolygonCoordinates);
                google.maps.event.addListener(polygon.getPath(), 'insert_at', updatePolygonCoordinates);
            });
    
            document.getElementById('clear-polygon').addEventListener('click', function() {
                if (currentPolygon) {
                    currentPolygon.setMap(null);
                    currentPolygon = null;
                    polygonCoordinates = [];
                    document.querySelector('input[name="polygon_coordinates"]').value = '[]';
                    document.querySelector('input[name="covered_countries"]').value = '[]';
                    document.getElementById('countries-list').style.display = 'none';
                    drawingManager.setDrawingMode(google.maps.drawing.OverlayType.POLYGON);
                }
            });
    
            document.getElementById('get-countries').addEventListener('click', getCountriesInPolygon);
        }
    
        function drawSavedPolygon(coordinates) {
            const polygonPath = coordinates.map(coord => ({
                lat: parseFloat(coord.lat),
                lng: parseFloat(coord.lng)
            }));
    
            currentPolygon = new google.maps.Polygon({
                paths: polygonPath,
                fillColor: '#2196F3',
                fillOpacity: 0.3,
                strokeWeight: 2,
                strokeColor: '#1976D2',
                clickable: true,
                editable: true,
                zIndex: 1
            });
    
            currentPolygon.setMap(map);
            
            google.maps.event.addListener(currentPolygon.getPath(), 'set_at', updatePolygonCoordinates);
            google.maps.event.addListener(currentPolygon.getPath(), 'insert_at', updatePolygonCoordinates);
    
            const bounds = new google.maps.LatLngBounds();
            polygonPath.forEach(point => bounds.extend(point));
            map.fitBounds(bounds);
    
            polygonCoordinates = coordinates;
        }
    
        function updatePolygonCoordinates() {
            if (!currentPolygon) return;
    
            const path = currentPolygon.getPath();
            polygonCoordinates = [];
    
            for (let i = 0; i < path.getLength(); i++) {
                const point = path.getAt(i);
                polygonCoordinates.push({
                    lat: point.lat(),
                    lng: point.lng()
                });
            }
    
            document.querySelector('input[name="polygon_coordinates"]').value = JSON.stringify(polygonCoordinates);
        }
    
        function getCountriesInPolygon() {
            if (!polygonCoordinates || polygonCoordinates.length === 0) {
                alert(translations.please_select_area);
                return;
            }
    
            document.getElementById('countries-content').innerHTML = '<p><i class="fa fa-spinner fa-spin"></i> ' + translations.searching_countries + '</p>';
            document.getElementById('countries-list').style.display = 'block';
            
            const btn = document.getElementById('get-countries');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> ' + translations.searching;
    
            fetch('/api/countries-in-polygon', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ coordinates: polygonCoordinates })
            })
            .then(response => response.json())
            .then(data => {
                const countries = data.countries;
                
                if (countries.length === 0) {
                    document.getElementById('countries-content').innerHTML = '<div class="alert alert-warning">' + translations.no_countries_found + '</div>';
                } else {
                    let html = '<div class="alert alert-success">' + translations.found + ' ' + countries.length + ' ' + translations.country + '</div>';
                    html += '<table class="table table-bordered table-striped"><thead><tr><th>' + translations.arabic_name + '</th><th>' + translations.english_name + '</th><th>ISO2</th><th>ISO3</th><th>' + translations.phone_code + '</th></tr></thead><tbody>';
                    
                    countries.forEach(country => {
                        html += '<tr>';
                        html += '<td>' + (country.name || '-') + '</td>';
                        html += '<td>' + (country.e_name || '-') + '</td>';
                        html += '<td>' + (country.iso2 || '-') + '</td>';
                        html += '<td>' + (country.iso3 || '-') + '</td>';
                        html += '<td>' + (country.phone_code || '-') + '</td>';
                        html += '</tr>';
                    });
                    
                    html += '</tbody></table>';
                    document.getElementById('countries-content').innerHTML = html;
                    
                    document.querySelector('input[name="covered_countries"]').value = JSON.stringify(countries);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('countries-content').innerHTML = '<div class="alert alert-danger">' + translations.error_fetching + '</div>';
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = translations.show_selected_countries;
            });
        }
    
        $(document).on('pjax:complete', function() {
            setTimeout(initMap, 100);
        });
    JS;
    }

    protected function addPhoneFields(Form $form)
    {
        $form->text('phone', __('whatsApp number'))
            ->rules('required')
            ->attribute('id', 'phone-input')
            ->attribute('maxlength', 12)
            ->default(function ($form) {
                if ($form->model()->phone && $form->model()->phone_code) {
                    return $form->model()->phone;
                }
                return null;
            });

        $form->hidden('phone_code')->default(function ($form) {
            return $form->model()->phone_code ?? '';
        });

        Admin::script($this->phoneJs());
    }

    protected function phoneJs()
    {
        return <<<JS
            function initPhoneInputById(inputId, hiddenId) {
                const input = document.querySelector(inputId);
                const hidden = document.querySelector(hiddenId);
                if (!input || input.classList.contains('iti-initialized')) return;

                const iti = window.intlTelInput(input, {
                    separateDialCode: true, 
                    preferredCountries: ["eg"], 
                    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
                });
                
                input.classList.add('iti-initialized');

                if (input.value && hidden && hidden.value) {
                    iti.setNumber(hidden.value + input.value);
                }

                input.addEventListener("countrychange", function () { 
                    if(hidden) hidden.value = "+" + iti.getSelectedCountryData().dialCode; 
                });
                
                const form = input.closest('form');
                if(form && !form.classList.contains('phone-init')){
                    form.addEventListener('submit', function(){
                        hidden.value = "+" + iti.getSelectedCountryData().dialCode;
                    });
                    form.classList.add('phone-init');
                }
            }

            function initAllPhones() { 
                initPhoneInputById("#phone-input", "input[name='phone_code']"); 
            }
            
            initAllPhones();
            $(document).on('pjax:complete', function () { 
                setTimeout(initAllPhones, 100); 
            });
    JS;
    }

    public function profile($id)
    {
        $tab = request()->query('tab', 'agencies');

        $superAdmin = AreaManager::select(['id', 'name', 'app_id', 'avatar', 'username', 'di', 'default', 'country_id'])->with('country')->findOrFail($id);

        $defaultImage = asset("images/icon-agency.jpg");
        $imageUrl = getImagePath($superAdmin->avatar);
        if (!isImageExists($imageUrl)) {
            $imageUrl = $defaultImage;
        }
        $superAdmin->display_image = $imageUrl;

        $agencies = $transactions = $target_history = null;
        $rewards = null;
        $totals = Charge::selectRaw("
            SUM(CASE WHEN user_type = ? AND user_id = ? THEN amount ELSE 0 END) as total_charges,
            SUM(CASE WHEN charger_type = ? AND charger_id = ? THEN amount ELSE 0 END) as total_spent
        ", [
            UserTypeEnum::SUPER_ADMIN,
            $superAdmin->id,
            UserTypeEnum::SUPER_ADMIN,
            $superAdmin->id
        ])
            ->first();

        $totalCharges = $totals->total_charges;
        $totalSpent   = $totals->total_spent;
        $types = ['vip', 'badge', 'ware'];
        $type = request()->get('type', 'vip');
        switch ($tab) {
            case 'agencies':
                $agencies = $superAdmin->agencies()->paginate(10, ['*'], 'agencies_page');
                break;
            case 'rewards':


                $rewards = SuperAdminReward::where('super_admin_id', $superAdmin->id)->where('type', $type)->with('ware', 'vip', 'badge')->paginate(10, ['*'], 'reward_page');
                break;
        }

        return view('superadmin.super_admin_profile', compact('superAdmin', 'agencies', 'totalCharges', 'totalSpent', 'type', 'types', 'rewards'));
    }

    public function profilePreview()
    {
        if (!session('preview_superadmin') || !session('country_id')) {
            abort(404, __('not found'));
        }

        $tab = request()->query('tab', 'agencies');
        $countryID = session('country_id');

        $superAdmin = AreaManager::select(['id', 'name', 'app_id', 'avatar', 'username', 'default', 'country_id'])
            ->with('country')->where('country_id', $countryID)->firstOrFail();

        $defaultImage = asset("images/icon-agency.jpg");
        $imageUrl = getImagePath($superAdmin->avatar);
        if (!isImageExists($imageUrl)) {
            $imageUrl = $defaultImage;
        }
        $superAdmin->display_image = $imageUrl;

        $agencies = $transactions = $target_history = null;

        $totals = Charge::selectRaw("
            SUM(CASE WHEN user_type = ? AND user_id = ? THEN amount ELSE 0 END) as total_charges,
            SUM(CASE WHEN charger_type = ? AND charger_id = ? THEN amount ELSE 0 END) as total_spent
        ", [
            UserTypeEnum::SUPER_ADMIN,
            $superAdmin->id,
            UserTypeEnum::SUPER_ADMIN,
            $superAdmin->id
        ])
            ->first();

        $totalCharges = $totals->total_charges;
        $totalSpent   = $totals->total_spent;

        switch ($tab) {
            case 'agencies':
                $agencies = $superAdmin->agencies()->paginate(10, ['*'], 'agencies_page');
                break;
        }

        return view('superadmin.super_admin_profile', compact('superAdmin', 'agencies', 'totalCharges', 'totalSpent'));
    }

    protected function detail($id)
    {
        $show = new Show(AreaManager::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('username', __('Username'));
        $show->field('avatar', __('Avatar'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('app_id', __('App id'));

        $this->extendShow($show);

        return $show;
    }
}
