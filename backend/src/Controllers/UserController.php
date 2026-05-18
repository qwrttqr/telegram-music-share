<?php

namespace App\Controllers;

use QwrttqrHTTP\Attributes\QueryParam;
use QwrttqrHTTP\Attributes\Route;
use QwrttqrHTTP\Wrappers\ControllerWrapper;

class UserController extends ControllerWrapper
{
  /**
   * @throws \Exception
   */
  #[Route('/foo/{userid}/some', 'GET')]
  public function myFunctionFoo(int $userid, #[QueryParam] string $username, #[QueryParam] string $lastname): void
  {
    echo "User ID: $userid Username: $username Lastname: $lastname";
    $this->connection('default');
  }

  #[Route('/users/{id}/posts/{postId}', 'GET')]
  public function getPost(int $id, int $postId): void
  {
    echo $_ENV['JWT_SECRET'];
    echo "User: $id, Post: $postId";
  }
}