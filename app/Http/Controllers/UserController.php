<?php

namespace App\Http\Controllers;

use App\Media;
use App\User;
use App\Utils\ModuleUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | UserController
    |--------------------------------------------------------------------------
    |
    | This controller handles the manipualtion of user
    |
    */

    /**
     * All Utils instance.
     */
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Shows profile of logged in user
     *
     * @return \Illuminate\Http\Response
     */
    public function getProfile()
    {
        $user_id = request()->session()->get('user.id');
        $user = User::where('id', $user_id)->with(['media'])->first();
        $config_languages = config('constants.langs');
        $languages = [];
        foreach ($config_languages as $key => $value) {
            $languages[$key] = $value['full_name'];
        }

        return view('user.profile', compact('user', 'languages'));
    }

    /**
     * updates user profile
     *
     * @return \Illuminate\Http\Response
     */
    public function updateProfile(Request $request)
    {
        //Disable in demo
        $notAllowed = $this->moduleUtil->notAllowedInDemo();
        if (! empty($notAllowed)) {
            return $notAllowed;
        }

        try {
            $user_id = $request->session()->get('user.id');
            $input = $request->only(['surname', 'first_name', 'last_name', 'email', 'language', 'marital_status',
                'blood_group', 'contact_number', 'fb_link', 'twitter_link', 'social_media_1',
                'social_media_2', 'permanent_address', 'current_address',
                'guardian_name', 'custom_field_1', 'custom_field_2',
                'custom_field_3', 'custom_field_4', 'id_proof_name', 'id_proof_number', 'gender', 'family_number', 'alt_number', ]);

            if (! empty($request->input('dob'))) {
                $input['dob'] = $this->moduleUtil->uf_date($request->input('dob'));
            }
            if (! empty($request->input('bank_details'))) {
                $input['bank_details'] = json_encode($request->input('bank_details'));
            }

            $user = User::find($user_id);
            $user->update($input);

            Media::uploadMedia($user->business_id, $user, request(), 'profile_photo', true);

            //update session
            $input['id'] = $user_id;
            $business_id = request()->session()->get('user.business_id');
            $input['business_id'] = $business_id;
            session()->put('user', $input);

            $output = ['success' => 1,
                'msg' => __('lang_v1.profile_updated_successfully'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect('user/profile')->with('status', $output);
    }

    /**
     * updates user password
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * Switch current business in session
     */
    public function switchBusiness($business_id)
    {
        try {
            $user = auth()->user();
            $target_business = \App\Business::findOrFail($business_id);

            // Check authorization
            $administrator_list = config('constants.administrator_usernames');
            $administrator_usernames = array_map('trim', explode(',', $administrator_list));
            $is_admin = in_array($user->username, $administrator_usernames);

            $can_switch = false;
            if ($is_admin) {
                $can_switch = true;
            } else {
                // Check if target business is linked to user's primary business or if user has account in target business
                $linked_business_ids = \App\BusinessIntercompanyLink::where('business_id', $user->business_id)
                    ->pluck('linked_business_id')
                    ->toArray();

                $same_username_businesses = User::where('username', $user->username)
                    ->pluck('business_id')
                    ->toArray();

                if (in_array($business_id, $linked_business_ids) || in_array($business_id, $same_username_businesses) || $user->business_id == $business_id) {
                    $can_switch = true;
                }
            }

            if (!$can_switch) {
                abort(403, 'Unauthorized business switch');
            }

            // Update session data
            $business_util = new \App\Utils\BusinessUtil();
            $currency = $target_business->currency;
            $currency_data = [
                'id' => $currency->id,
                'code' => $currency->code,
                'symbol' => $currency->symbol,
                'thousand_separator' => $currency->thousand_separator,
                'decimal_separator' => $currency->decimal_separator,
            ];

            session()->put('business', $target_business);
            session()->put('currency', $currency_data);

            $session_user = session('user');
            $session_user['business_id'] = $target_business->id;
            session()->put('user', $session_user);

            $financial_year = $business_util->getCurrentFinancialYear($target_business->id);
            session()->put('financial_year', $financial_year);

            $output = ['success' => 1, 'msg' => 'Berhasil berpindah bisnis ke ' . $target_business->name];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = ['success' => 0, 'msg' => __('messages.something_went_wrong')];
        }

        return redirect('/home')->with('status', $output);
    }

    public function updatePassword(Request $request)
    {
        //Disable in demo
        $notAllowed = $this->moduleUtil->notAllowedInDemo();
        if (! empty($notAllowed)) {
            return $notAllowed;
        }

        try {
            $user_id = $request->session()->get('user.id');
            $user = User::where('id', $user_id)->first();

            if (Hash::check($request->input('current_password'), $user->password)) {
                $user->password = Hash::make($request->input('new_password'));
                $user->save();
                $output = ['success' => 1,
                    'msg' => __('lang_v1.password_updated_successfully'),
                ];
            } else {
                $output = ['success' => 0,
                    'msg' => __('lang_v1.u_have_entered_wrong_password'),
                ];
            }
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect('user/profile')->with('status', $output);
    }
}
