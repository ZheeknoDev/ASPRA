<?php

/**
 * @category Class
 * @package  zheeknodev/aspra
 * @author   ZheeknoDev <million8.me@gmail.com>
 * @license  https://opensource.org/licenses/MIT - MIT License 
 * @link     https://github.com/ZheeknoDev/Aspra
 */

namespace App\Controller;

use App\Core\Auth;
use App\Core\Controller;
use App\Model\User;
use Zheeknodev\Sipher\Sipher;

class AuthController extends Controller
{
    public function getUserProfile()
    {
        # get user data
        $userInfo = Auth::getUserInfo();

        # return user data
        if (!empty($userInfo)) {
            return $this->response->respond([
                'status' => true,
                'response' => (array) $userInfo,
            ]);
        }

        # return not found user data
        return $this->response->failNotFound(['error' => 'Unable to find the user information.']);
    }

    public function postUpdateUserProfile()
    {
        $input = $this->request->body();
        $validation = $this->validator->validate((array) $input, [
            'firstname' => 'nullable|alpha',
            'lastname' => 'nullable|alpha',
            'email' => 'nullable|email'
        ]);

        # validation is failed
        if ($validation->fails()) {
            $this->invalid($validation);
        }

        # get user's info
        $userInfo = Auth::getUserInfo();
        if (empty($userInfo)) {
            # error response message
            return $this->response->failNotFound(['error' => 'Unable to update the profile']);
        }

        $info = array();
        # firstname
        if (!empty($input->firstname)) {
            $info['firstname'] = $input->firstname;
        }
        # lastname
        if (!empty($input->lastname)) {
            $info['lastname'] = $input->lastname;
        }
        # email
        if (!empty($input->email)) {
            $hasExistsEmail = User::where('email', '=', $input->email)
                ->where('id', '!=', $userInfo->id)
                ->run();
            if (count($hasExistsEmail) > 0) {
                # error response message
                return $this->response->failUnauthorized(['error' => "Unable to update, {$input->email} has already been registered"]);
            } else {
                $info['email'] = $input->email;
            }
        }
        # update user's profile
        if (count($info) == 3) {
            try {
                $update = User::where('id', '=', $userInfo->id)
                    ->update($info)
                    ->run();
                $message = ($update) ? 'The profile has updated success' : 'Something went wrong, unable to update the profile';
                if ($update) {
                    return $this->response->respond([
                        'status' => true,
                        'response' => ['message' => $message],
                    ]);
                }
                return $this->response->fail(['error' => $message], 500);
            } catch (\Exception $e) {
                # log
                # return error
                return $this->response->fail(['error' => $e->getMessage()], $e->getCode());
            }
        }
    }

    public function postUserRenewToken()
    {
        $input = $this->request->body();
        $validation = $this->validator->validate((array) $input, [
            'username' => 'required_without:email',
            'email' => 'required_without:username',
            'password' => 'required'
        ]);

        # validation is failed
        if ($validation->fails()) {
            $this->invalid($validation);
        }

        $query = new User();

        # find by username
        if (!empty($input->username)) {
            $query = $query::where('username', '=', $input->username);
        }

        # find by email
        if (!empty($input->email)) {
            $query = $query::where('email', '=', $input->email);
        }

        # get the user's detail
        $user = $query->first();

        # when not found the user.
        $passwordIsCorrect = Auth::check_password($input->password, $user->password);
        if (empty($user->id) || !$passwordIsCorrect) {
            # error response message
            return $this->response->failUnauthorized(['error' => 'Unable to access, because your username, email, or password is incorrect']);
        } else {
            # whene the email or username is correct
            $userToken = Auth::getUserApiToken($user->id);

            # if return status = false
            if ($userToken->status === false) {
                $errors = (!empty($userToken->errors) ? $userToken->errors : []);
                return $this->response->fail(['error' => $errors], 500);
            }

            # return user data
            unset($userToken->status);
            return $this->response->respond([
                'status' => true,
                'response' => (array) $userToken,
            ]);
        }
    }

    /**
     * postUserRegister
     *
     * @return void
     */
    public function postUserRegister()
    {
        $input = $this->request->body();
        $validation = $this->validator->validate((array) $input, [
            'firstname' => 'required|alpha',
            'lastname' => 'required|alpha',
            'email' => 'required|email',
            'username' => 'required|alpha_num',
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password'
        ]);

        # validation is failed
        if ($validation->fails()) {
            # handling errors
            $errors = $validation->errors();
            $errorKeys = [];
            foreach ($errors->firstOfAll() as $key => $value) {
                $errorKeys[] = $key;
            }

            # return validation errors
            # return confirmation password errors
            if ($input->password != $input->confirm_password) {
                $message = "The password does not match with confirmation.";
            } else {
                $listOfInputFields = implode(', ', $errorKeys);
                $message = "The input field ({$listOfInputFields}) " . (count($errorKeys) > 1 ? 'are' : 'is') . " required";
            }
            return $this->response->failBadRequest(['error' => $message]);
        }

        # has duplicated the username and email or not ?
        $getUserByEmail = User::where('email', '=', $input->email)->first();
        $getUserByUsername = User::where('username', '=', $input->username)->first();

        if (!empty($getUserByEmail->id) || !empty($getUserByUsername->id)) {
            $field = (!empty($getUserByEmail->id)) ? "email" : "username";
            $message = "Your {$field} has already registered. You should be change to the another {$field}.";
            return $this->response->failBadRequest(['error' => $message], 400);
        }

        # register new users
        unset($input->confirm_password);
        try {
            $registerUser = User::insert([
                'firstname' => $input->firstname,
                'lastname' => $input->lastname,
                'email' => $input->email,
                'username' => $input->username,
                'password' => Auth::hash_password($input->password),
                'remember' => Sipher::randomString(32)
            ])->run();
        } catch (\Exception $e) {
            # log
            # return error
            return $this->response->fail(['error' => $e->getMessage()], $e->getCode());
        }

        # get user's id
        $userId = User::lastId();

        # when not found user's id
        if (!empty($userId)) {
            return $this->response->failNotFound(['error' => 'Unable to find the ID of users.'], 404);
        }

        # get new token
        $userToken = Auth::getUserApiToken($userId);

        # if return status = false
        if ($userToken->status === false) {
            $errors = (!empty($userToken->errors) ? $userToken->errors : []);
            return $this->response->fail(['error' => $errors], 500);
        }

        # return user data
        unset($userToken->status);
        return $this->response->respond([
            'status' => true,
            'response' => (array) $userToken,
        ]);
    }
}
