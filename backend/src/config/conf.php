<?php

return [
  "db" => [
    "default" => [
      "dsn" => $_ENV["DB_DEFAULT"],
      "entities" => "src/Entities"
    ]
  ]
];