<?php

namespace app\controllers;

use galastri\modules\Controller;
use galastri\modules\Permission;
use galastri\modules\Redirect;
use galastri\modules\Validation;
use galastri\extensions\Exception;

final class Index extends Controller
{
    protected function main(): array
    {
        $validation = Validation::string();
            
        return [];
    }

    protected function protectedRoute(): array
    {
        try {
            return [];
        } catch (Exception $e) {
            return [];
        }
    }

    protected function login(): array
    {
        // try {

            // $permission = new Permission();
            // $permission->checkFor('common')
            //     ->at('admin', 'common')
            //     ->validate();
                
            // if(Authentication::validate('login')) {
            //     Redirect::to('/');
            // }

            // Authentication::setAuthTag('login');
            // Authentication::setField('user', 'admin');
            // Authentication::create();
            
            return [
                $_COOKIE,
            ];
        // } catch (Exception $e) {
        //     return [
        //         'message' => $e->getMessage(),
        //         'code' => $e->getCode(),
        //         'data' => $e->getData(),
        //     ];
        // }
    }

    protected function logout(): array
    {
        try {
            Authentication::destroy();

            return [
                $_COOKIE,
            ];
        } catch (Exception $e) {
            return [];
        }
    }
}
