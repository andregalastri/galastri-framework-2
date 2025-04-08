<?php

namespace app\controllers;

use galastri\modules\Controller;
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
}
