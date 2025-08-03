<?php
namespace App\Token;

class FunctionGenerateToken {
  public function generateToken($email): string {
    return base64_encode($email . '|' . bin2hex(random_bytes(16)));
  }
}
