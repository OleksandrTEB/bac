<?php
namespace App\Middleware;

class AdminMiddelware {

  public static function handle(): void {

    if (!isset($_COOKIE['admincookie'])) {
      http_response_code(401);
      echo json_encode([
        'success' => false
      ]);
      exit;
    }
  }
}
