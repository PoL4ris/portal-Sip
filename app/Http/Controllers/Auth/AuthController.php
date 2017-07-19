<?php

namespace App\Http\Controllers\Auth;

//use App\User;
use Validator;
use Socialite;
use Auth;
use DB;
use Redirect;
use App\Models\User;
use Log;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

class AuthController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware($this->guestMiddleware(), ['except' => 'logout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data) {
        
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
    }

     public function logout() {
        
         Auth::logout();

         // Add call to Google logout here - use the social token
         
        return redirect(property_exists($this, 'redirectAfterLogout') ? $this->redirectAfterLogout : '/');
    }

    /**
   * Redirect the user to the google authentication page.
   *
   * @return Response
   */
    public function getSocialRedirect() {
        
        return Socialite::driver('google')->redirect();
    }

    /**
   * Obtain the user information from google.
   *
   * @return Response
   */
    public function getSocialHandle() {

        try {
            $user = Socialite::driver('google')->user();
        }
        catch (Exception $e) {
            return Redirect::to('auth/google');
        }

        $authUser = User::where('email', $user->email)
                        ->where('id_status', config('const.status.active'))
                        ->where('social_access', config('const.status.active'))
                        ->first();

        if($authUser == null){
            return view('auth.login', ['params' => 'Login failed']);
        }
        
        $authUser->update(['social_token' => $user->token,
                         'avatar' => $user->avatar]);  
        
        Auth::login($authUser, true);
        return redirect()->action('MainController@homeView');
    }


    public function AppSocialCredentials(Request $request)
    {

        Auth::login(User::find(1), true);
        return User::find(Auth::user()->id)->accessApps->load('apps')->pluck('apps', 'apps.position')->sortBy('position');







//
//        if($request->sessionTokenAccess == 'polaris')
//            return 'OK';


        $authUser = User::where('email',$request->email)
                        ->where('id_status', config('const.status.active'))
                        ->where('social_access', config('const.status.active'))
                        ->first();

        if(isset($authUser))
        {
            Auth::login($authUser, true);
            return 'OK';
        }
        else
        {
            return 'ERROR';
        }

    }










      /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
//    protected function create(array $data)
//    {
//        return User::create([
//            'name' => $data['name'],
//            'email' => $data['email'],
//            'password' => bcrypt($data['password']),
//        ]);
//    }
    
    
    

    /**
   * Return user if exists; create and return if doesn't
   *
   * @param $googleUser
   * @return User
   */
//    private function updateUserSocialFields($user) {
//        
//        
//        
//        
//        $authUser = User::where('email', $googleUser->email)->first();
//
//        if (isset($authUser)) {
//            
//            if ($authUser['social_access'] == 1) {
//                $first_name = $googleUser->user['name']['givenName']?$googleUser->user['name']['givenName']:'XXX';
//                $last_name  = $googleUser->user['name']['familyName']?$googleUser->user['name']['familyName']:'XXX';
//                $alias      = substr($first_name, 0, 1) . substr($last_name, 0, 1);
//
//                $data = [ 'social_token' => $googleUser->token,
//                         'avatar' => $googleUser->avatar,
//                         'alias' => $alias,
//                         'id_status' => config('const.status.active'),
//                         'id_profiles' => 1
//                        ];
//
//                User::where('id', $authUser['id'])->update($data);
//
//                return $authUser;
//            }
//            else
//                return false;
//        }
//        else
//            return false;
//
//
//
//
//
//        //Create new user OLD
//        //    $first_name = $googleUser->user['name']['givenName']?$googleUser->user['name']['givenName']:'XXX';
//        //    $last_name  = $googleUser->user['name']['familyName']?$googleUser->user['name']['familyName']:'XXX';
//        //    $alias      = substr($first_name, 0, 1) . substr($last_name, 0, 1);
//        //    $record = User::create();
//        //
//        //    $data = ['first_name' => $first_name,
//        //             'last_name' => $last_name,
//        //             'email' => $googleUser->email,
//        //             'social_token' => $googleUser->token,
//        //             'avatar' => $googleUser->avatar,
//        //             'alias' => $alias,
//        //             'id_status' => 3,
//        //             'id_profiles' => 1
//        //             ];
//        //
//        //    User::where('id', $record->id)->update($data);
//        //
//        //    return $record;
//
//    }

}



