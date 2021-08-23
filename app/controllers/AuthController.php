<?php

namespace App\Controllers;

use App\Core\Auth\Auth;
use App\Core\Controller;
use App\Core\Database\DB;

class AuthController extends Controller
{
    public function login()
    {
        $input = $this->request->body();
        $validation = $this->validator->validate((array) $input, [
            'username' => 'required|alpha_num',
            'password' => 'required'
        ]);
        # validation is failed
        if ($validation->fails()) {
            # handling errors
            $errors = $validation->errors();
            foreach ($errors->firstOfAll() as $key => $value) {
                $error_messages[] = $value;
            }
            # return validation errors
            return $this->response->json_form_response([
                'Errors' => $error_messages
            ], false, 400);
        }

        # Has authorized or not ?
        return $this->validateGetUserToken($input->username, $input->password);
    }

    public function register()
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
            $listOfInputFields = implode(', ', $errorKeys);
            $message = "The input field ({$listOfInputFields}) " . (count($errorKeys) > 1 ? 'are' : 'is') . " required";
            return $this->response->json_form_response($message, false, 400);
        }

        # Has existed the username and email or not ?
        $getExistsUser = DB::table('users')
            ->where('username', '=', $input->username)
            ->or_where('email', '=', $input->email)
            ->first();
        if (!empty($getExistsUser)) {
            $inputField = ($getExistsUser->username == $input->username) ? 'username' : 'email';
            $message = "Your {$inputField} has already registered. You should be change to the another {$inputField}.";
            return $this->response->json_form_response($message, false, 401);
        }

        # register new user
        try {
            DB::table('users')
                ->insert([
                    'firstname' => $input->firstname,
                    'lastname' => $input->lastname,
                    'email' => $input->email,
                    'username' => $input->username,
                    'password' => Auth::hash_password($input->password),
                    'remember' => $this->random_string(32)
                ])->run();
            # get the authorize and user's token
            return $this->validateGetUserToken($input->username, $input->password);
        } catch (\Exception $e) {;
            return $this->response->json_form_response($e->getMessage(), false, 500);
        }
    }

    private function validateGetUserToken(string $username, string $password)
    {
        if (Auth::via('users')->access($username, $password)) {
            if (Auth::users()->getToken() !== null) {
                $userToken = Auth::users()->getToken();
                $response = ['username' => $username];
                $response = array_merge($response, $userToken);
                return $this->response->json_form_response($response, true);
            }
        }
        return $this->response->redirect('/401');
    }
}
