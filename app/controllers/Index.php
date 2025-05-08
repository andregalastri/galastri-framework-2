<?php

namespace app\controllers;

use galastri\modules\Controller;
use galastri\modules\Permission;
use galastri\modules\Redirect;
use galastri\modules\Database;
use galastri\extensions\Exception;

final class Index extends Controller
{
    protected function main(): array
    {
        $db = Database::createMysql();
        $db->setHost('localhost');
        $db->setDatabase('test');
        $db->setUsername('phpmyadmin');
        $db->setPassword('phpmyadmin#tr3');
        $db->connect();

        $db->query('SELECT * FROM framework WHERE id = :id');
        $db->bind(':id', 1);
        $db->submit();

        vardump($db->getResult());
        
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
