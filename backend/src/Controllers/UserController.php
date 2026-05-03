<?php

namespace App\Controllers;

use QwrttqrHTTP\src\Route;

class UserController
{
  public function __construct()
  {
    // Or inject dependencies:
    // public function __construct(private DatabaseService $db)
  }

  #[Route('/foo/{userid}/123', 'GET')]
  public function myFunctionFoo(int $userid): void
  {
    echo "User ID: $userid";
  }

  #[Route('/users/{id}/posts/{postId}', 'GET')]
  public function getPost(int $id, int $postId): void
  {
    // $id and $postId come from route params
    // $uri is injected as a dependency
    echo "User: $id, Post: $postId";
  }
}