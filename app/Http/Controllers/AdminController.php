<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
//Laravel
use DB;
use Auth;
//Controller
use App\Http\Controllers\Lib\FormsController;
//Models
use App\Models\Profile;
use App\Models\Status;
use App\Models\User;
use App\Models\App;
use App\Models\AccessApp;
use App\Models\Customer;
use App\Models\BuildingProperty;


/**
 * Class AdminController
 * @package App\Http\Controllers
 * Post methods in general, secure or private info.
 */
class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @param Request $request
     * record to find update and change position UP;
     * @return string
     * List of all apps.
     */
    public function getAppPositionUp(Request $request)
    {
        $data = $request->params['record'];

        $otherRecord = App::where('position', ($data['position'] - 1))->first();
        $thisRecord  = App::find($data['id']);

        if(!$otherRecord || !$thisRecord)
            return 'ERROR';

        $otherRecord->position = ($otherRecord->position + 1);
        $otherRecord->save();

        $thisRecord->position = $data['position'] - 1;
        $thisRecord->save();

        return App::orderBy('position', 'asc')->get();

    }
    /**
     * @param Request $request
     * record to find update and change position DOWN;
     * @return string
     * List of all apps.
     */
    public function getAppPositionDown(Request $request)
    {
        $data = $request->params['record'];

        $otherRecord = App::where('position', ($data['position'] + 1))->first();
        $thisRecord  = App::find($data['id']);

        if(!$otherRecord || !$thisRecord)
            return 'ERROR';

        $otherRecord->position = ($otherRecord->position - 1);
        $otherRecord->save();

        $thisRecord->position = $data['position'] + 1;
        $thisRecord->save();

        return App::orderBy('position', 'asc')->get();

    }
    /**
     * @return gets logged user info.
     */
    public function getProfileInfo()
    {
        return User::find(Auth::user()->id);
    }
    /**
     * @param Request $request
     * password = new password to set as bcrypt.
     * @return OK, when update is complete.
     */
    public function updateProfileInfo(Request $request)
    {
        $user = User::find(Auth::user()->id);
        $user->password = bcrypt($request->password);
        $user->save();
        return 'OK';
    }
    /**
     * @return List of portal users allowed to interact.
     */
    public function getAdminUsers()
    {
        return User::with('profile')->get();
    }
    /**
     * @return List of profiles with respective access apps.
     */
    public function getAdminProfiles()
    {
        return Profile::with('accessApps')->get();
    }
    /**
     * @param Request $request
     * id = id_users to update.
     * users DB fields to update
     * @return List of admin users.
     */
    public function updateAdminUser(Request $request)
    {
        $data = $request->params['objetos'];
        $user = User::find($data['id']);
        $user->first_name = $data['first_name'];
        $user->last_name  = $data['last_name'];
        $user->email = $data['email'];
        $user->alias = $data['alias'];
        if (!empty($data['password']))
            $user->password = bcrypt($data['password']);

        $user->social_access = $data['social_access'];
        $user->id_status     = $data['id_status'];
        $user->id_profiles   = $data['id_profiles'];
        $user->save();

        return $this->getAdminUsers();

    }
    /**
     * @param Request $request
     * objetos = Users DB table field Values.
     * @return List of admin users.
     */
    public function insertAdminUser(Request $request)
    {
        $data = $request->params['objetos'];
        $user = new User;
        $user->first_name  = $data['first_name'];
        $user->last_name   = $data['last_name'];
        $user->email       = $data['email'];
        $user->alias       = $data['alias'];
        $user->password    = bcrypt($data['password']);
        $user->social_access = $data['social_access'];
        $user->id_status   = $data['id_status'];
        $user->id_profiles = $data['id_profiles'];
        $user->save();

        return $this->getAdminUsers();
    }
    /**
     * @return List of all apps.
     */
    public function getAdminApps()
    {
        return App::orderBy('position', 'asc')->get();
    }
    /**
     * @param Request $request
     * profile_name = vale of profile table to insert.
     * @return List of profiles with access Apps.
     */
    public function insertNewProfile(Request $request)
    {
        $data = $request->params['objects'];

        $profile = new Profile;
        $profile->name = $data['profile_name'];
        $profile->save();

        unset($data['profile_name']);

        $aApps = new AccessApp;

        foreach ($data as $index => $item)
        {
            $aApps = new AccessApp;
            $aApps->id_apps = $index;
            $aApps->id_profiles = $profile->id;
            $aApps->save();
        }

        return Profile::with('accessApps')->get();
    }
    /**
     * @param Request $request
     * id_profiles = id  profile to find and update.
     * profile_name = new value.
     * @return List of profiles with access Apps.
     */
    public function updateAdminProfile(Request $request)
    {
        $data = $request->params['objects'];

        $profile = Profile::find($data['id_profiles']);
        $profile->name = $data['profile_name'];
        $profile->save();

        unset($data['profile_name']);

        AccessApp::where('id_profiles', $data['id_profiles'])->delete();

        $aApps = new AccessApp;

        foreach ($data as $index => $item)
        {
            $aApps = new AccessApp;
            $aApps->id_apps = $index;
            $aApps->id_profiles = $data['id_profiles'];
            $aApps->save();
        }

        return Profile::with('accessApps')->get();

    }
    /**
     * @param Request $request
     * id_profiles = id profile.
     * id_apps = id app.
     * @return Access app requested.
     */
    public function getAppAccess(Request $request)
    {
        $data = $request->params;
        return AccessApp::where('id_profiles', $data['id_profiles'])
                        ->where('id_apps', $data['id_apps'])
                        ->first();
    }
    /**
     * @param Request $request
     * objetos = app table field values.
     * @return List of apps.
     */
    public function insertNewApp(Request $request)
    {
        $data = $request->params['objects'];

        $app = new App;
        $app->name = $data['app_name'];
        $app->icon = $data['icon'];
        $app->url  = $data['url'];
        $app->position  = App::max('position')+1;
        $app->save();

        unset($data['app_name'], $data['icon'], $data['url']);

        $aApps = new AccessApp;

        foreach ($data as $index => $item)
        {
            $aApps = new AccessApp;
            $aApps->id_apps = $app->id;
            $aApps->id_profiles = $index;
            $aApps->save();
        }

        return App::get();
    }
    /**
     * @param Request $request
     * objects = apps table fields to update.
     * @return List of apps.
     */
    public function updateAdminApp(Request $request)
    {
        $data = $request->params['objects'];

        $app = App::find($data['id_apps']);
        $app->name = $data['app_name'];
        if ($data['icon'])
            $app->icon = $data['icon'];

        $app->url = $data['url'];
        $app->save();

        unset($data['app_name'], $data['icon'], $data['url']);

        AccessApp::where('id_apps', $data['id_apps'])->delete();

        $aApps = new AccessApp;

        foreach ($data as $index => $item)
        {
            $aApps = new AccessApp;
            $aApps->id_apps = $data['id_apps'];
            $aApps->id_profiles = $index;
            $aApps->save();
        }

        return App::get();
    }
    /**
     * @return Building properties list.
     */
    public function getAdminBldProperties(){
        return BuildingProperty::get();
    }
    /**
     * @param Request $request
     * objetos = Building Property DB table field Values.
     * @return List of Building Properties.
     */
    public function insertNewBldProperty(Request $request){
        $data = $request->params['objects'];
        $user = new BuildingProperty;
        $user->name         = $data['property_name'];
        $user->description  = $data['property_description'];
        $user->save();

        return $this->getAdminBldProperties();
    }
    /**
     * Creates form from table requested.
     * Not in use for the moment.
     */
    public function getAdminForm(Request $request)
    {
        $dynamicForm = new FormsController();
        $data = $dynamicForm->getFormType($request->table);
        return $data;
        return view('buildings.newbuildingform', ['form' => $data]);
    }
    /**
     * Given form, inserts full table with values.
     */
    public function insertAdminForm(Request $request)
    {
        $data = $request->all();
        unset($data['table']);
        DB::table($request['table'])->insert($data);
    }
    /**
     * @return List of Users
     * REFACTOR AND RENAME, VERIFY USAGE AND UPDATE ASAP.
     */
    public function admin()
    {
        return User::get();
    }
}
