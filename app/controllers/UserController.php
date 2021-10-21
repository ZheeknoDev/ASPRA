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
