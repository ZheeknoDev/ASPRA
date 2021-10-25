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
use App\Core\Database\DB;
use App\Model\User;
use Zheeknodev\Sipher\Sipher;

class UserController extends Controller
{
    public function getUserProfile()
    {
        $userInfo = Auth::getUserInfo();
        $status = !empty($userInfo) ? true : false;
        $httpResponseCode = ($status) ? 200 : 400;
        return $this->json($status, (array) $userInfo, $httpResponseCode);
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
            $errorRespone['warning'] = 'Unable to update the profile';
            return $this->json(false, $errorRespone, 401);
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
                $errorRespone['warning'] = "Unable to update, {$input->email} has already been registered";
                return $this->json(false, $errorRespone, 401);
            } else {
                $info['email'] = $input->email;
            }
        }
        # update user's profile
        if (count($info) > 0) {
            try {
                $update = User::where('id', '=', $userInfo->id)
                    ->update($info)
                    ->run();
                $status = ($update) ? true : false;
                $resposne['message'] = ($status) ? 'The profile has updated success' : 'Something went wrong, unable to update the profile';
                $httpResponseCode = ($status) ? 200 : 401;
                return $this->json($status, $resposne, $httpResponseCode);
            } catch (\Exception $e) {
                # log
                die($e->getMessage());
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
            $errorRespone['message'] = 'Unable to access, because your username, email, or password is incorrect';
            return $this->json(false, $errorRespone, 401);
        } else {
            # whene the email or username is correct
            $userId = $user->id;
            $userToken = Auth::getUserApiToken($userId);
            $status = $userToken->status;
            $httpResponseCode = ($status) ? 200 : 401;
            unset($userToken->status);
            return $this->json($status, (array) $userToken, $httpResponseCode);
        }
    }

    /**
     * User's register
     * @return JSON
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
            $errorResponse['warning'] = $message;
            return $this->json(false, $errorResponse, 400);
        }

        # has existed the username and email or not ?
        $hasExistsEmail = User::where('email', '=', $input->email)->first();
        $hasExistsUsername = User::where('username', '=', $input->username)->first();

        $existEmail = !empty($hasExistsEmail->id) ? true : false;
        $existUsername = !empty($hasExistsUsername->id) ? true : false;

        if ($existEmail || $existUsername) {
            $inputField = ($existEmail) ? "email" : "username";
            $message = "Your {$inputField} has already registered. You should be change to the another {$inputField}.";
            $errorRespone['warning'] = $message;
            return $this->json(false, $errorRespone, 401);
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
            die($e->getMessage());
        }

        # get user's id
        $userId = User::lastId();

        # when not found user's id
        if (!empty($userId)) {
            $errorRespone['warning'] = 'Unable to find the ID of users.';
            return $this->json(false, $errorRespone, 400);
        }

        # get new token
        $userToken = Auth::getUserApiToken($userId);
        $status = $userToken->status;
        $httpResponseCode = ($status) ? 200 : 500;
        unset($userToken->status);
        return $this->json($status, (array) $userToken, $httpResponseCode);
    }
}
