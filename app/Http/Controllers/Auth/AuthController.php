<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Validator;
use Socialite;
use Auth;
use DB;
use Redirect;

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
    public function __construct()
    {
        $this->middleware($this->guestMiddleware(), ['except' => 'logout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }


  /**
   * Redirect the user to the google authentication page.
   *
   * @return Response
   */
  public function getSocialRedirect()
  {
    return Socialite::driver('google')->redirect();
  }

  /**
   * Obtain the user information from google.
   *
   * @return Response
   */
  public function getSocialHandle()
  {
    try
    {
      $user = Socialite::driver('google')->user();
    }
    catch (Exception $e)
    {
      return Redirect::to('auth/google');
    }

    $authUser = $this->findOrCreateUser($user);

    Auth::login($authUser, true);

//    return Redirect::to('dummy');

    return view('dummy');

  }


  /**
   * Return user if exists; create and return if doesn't
   *
   * @param $googleUser
   * @return User
   */
  private function findOrCreateUser($googleUser)
  {



    if ($authUser = User::where('email', $googleUser->email)->first())
    {
      return $authUser;
    }

    $first_name = $googleUser->user['name']['givenName'];
    $last_name = $googleUser->user['name']['familyName'];
    $alias = substr($first_name, 0, 1) . substr($last_name, 0, 1);


    $record = User::create();

    $data = ['first_name' => $first_name,
             'last_name' => $last_name,
             'email' => $googleUser->email,
             'password' => $googleUser->token,
             'social_token' => $googleUser->token,
             'avatar' => $googleUser->avatar,
             'alias' => $alias,
             'id_status' => 3,
             'id_profiles' => 1
             ];

    User::where('id', $record->id)->update($data);

    return $record;

  }

}



