<?php

namespace app\controllers;

use galastri\modules\Controller;
use galastri\modules\Authentication;
use galastri\modules\Redirect;
use galastri\extensions\Exception;

final class Index extends Controller
{
    protected function main(): array
    {
        try {
            return [];
        } catch (Exception $e) {
            return [];
        }
    }

    protected function protectedRoute(): array
    {
        try {
            vardump($_SESSION, $_COOKIE);
    
            return [];
        } catch (Exception $e) {
            return [];
        }
    }

    protected function login(): array
    {
        try {
            if(Authentication::validate('login')) {
                Redirect::to('/');
            }

            Authentication::setAuthTag('login');
            Authentication::setField('user', 'admin');
            Authentication::create();
            
            return [
                $_COOKIE,
            ];
        } catch (Exception $e) {
            return [];
        }
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
