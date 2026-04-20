<?php

namespace App\Controllers;

use Framework\src\Route;

class UserController
{
    public function __construct()
    {
    }

    #[Route('/foo', 'GET')]
    public function myFunctionFoo(): void
    {
        echo 1;
    }

    public function myFunctionBar(): void
    {
        echo 1;
    }

    public function myFunctionEpsilon(): void
    {
        echo 1;
    }
}