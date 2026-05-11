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
    // $id and $postId come from route params
    // $uri is injected as a dependency
    echo "User: $id, Post: $postId";
  }
}