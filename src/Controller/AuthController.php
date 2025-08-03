<?php

namespace App\Controller;

use App\Database\Database;
use App\Token\FunctionGenerateToken;
use PDO;


class AuthController
{

//------------------------------register---------------------------

  public function checkcookie(): void
  {
    if (isset($_COOKIE['admincookie'])) {

      $pdo = Database::connect();

      $token = $_COOKIE['admincookie'];

      $stmt = $pdo->prepare("SELECT * FROM users WHERE token = :token");
      $stmt->execute(['token' => $token]);
      $result = $stmt->fetch();

      if ($result) {

        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => 'admin']);

        $admin = $stmt->fetch();
        $_SESSION['avatar'] = $admin['avatar'];
        $_SESSION['username'] = $admin['username'];
        $_SESSION['email'] = $admin['email'];
        $_SESSION['user_id'] = $admin['id'];


        if (empty($_SESSION['avatar'])) {
          $stmt = $pdo->prepare('SELECT base_avatar FROM baza');
          $stmt->execute();
          $result = $stmt->fetch();

          $stmt = $pdo->prepare('UPDATE users SET avatar = :avatar WHERE id = :id');
          $stmt->execute([
              'avatar' => $result['base_avatar'],
              'id' => $_SESSION['user_id']]
          );

          $_SESSION['avatar'] = $result['base_avatar'];
        }

        http_response_code(200);
        echo json_encode([
          'success' => true,
          'admin' => true,
        ]);
        exit;
      } else {
        http_response_code(403);
        echo json_encode([
          'success' => false,
          'message' => 'Invalid admin token'
        ]);
        exit;
      }
    }
    if (isset($_COOKIE['cookietoken'])) {
      $token = $_COOKIE['cookietoken'];

      $pdo = Database::connect();

      $stmt = $pdo->prepare('SELECT * FROM users WHERE token = :token LIMIT 1');
      $stmt->execute(['token' => $token]);
      $user = $stmt->fetch();
      if ($user) {
        $_SESSION['avatar'] = $user['avatar'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_id'] = $user['id'];


        if (empty($_SESSION['avatar'])) {
          $stmt = $pdo->prepare('SELECT base_avatar FROM baza');
          $stmt->execute();
          $result = $stmt->fetch();

          $stmt = $pdo->prepare('UPDATE users SET avatar = :avatar WHERE id = :id');
          $stmt->execute([
              'avatar' => $result['base_avatar'],
              'id' => $_SESSION['user_id']]
          );

          $_SESSION['avatar'] = $result['base_avatar'];
        }


        http_response_code(200);
        echo json_encode([
          'success' => true
        ]);
        exit;
      } else {
        http_response_code(403);
        echo json_encode([
          'success' => false,
          'message' => 'Invalid user token']);
        exit;
      }
    } else {
      http_response_code(403);
      echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to access this page!'
      ]);
    }
  }

//------------------------------register---------------------------

  public function register(): void
  {
    $input = json_decode(file_get_contents('php://input'), true);


    $email = $input['email'];
    $password = $input['password'];
    $username = $input['username'];
    $country = $input['country'];
    $license = $input['checkboxStatus'];


    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      http_response_code(400);
      echo json_encode([
        'success' => false,
        'messageEmail' => 'Invalid email address',
        'isErrorEmail' => true,
      ]);
      return;
    }

    $pdo = Database::connect();

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
      http_response_code(409);
      echo json_encode([
        'success' => false,
        'messageAlready' => 'A user with this email already exists.',
        'isErrorAlready' => true,
      ]);
      return;
    }


    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $password)) {
      http_response_code(400);
      echo json_encode([
        'success' => false,
        'messagePassword' => 'The password must be at least 8 characters long and contain both a letter and a number.',
        'isErrorPassword' => true,
      ]);
      return;
    }

    if (strlen($username) < 3 || strlen($username) > 50) {
      http_response_code(400);
      echo json_encode([
        'success' => false,
        'messageUsername' => 'Username must be between 3 and 50 characters long.',
        'isErrorUsername' => true,
      ]);
      return;
    }


    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);


    $stmt = $pdo->prepare('INSERT INTO users (email, password, username, country, license) VALUES (:email, :password, :username,  :country, :license)');
    $stmt->execute([
      'email' => $email,
      'password' => $hashedPassword,
      'username' => $username,
      'country' => $country,
      'license' => $license
    ]);


    http_response_code(201);
    echo json_encode([
      'success' => true,
    ]);

  }

//------------------------------login---------------------------

  public function login(): void
  {

    $input = json_decode(file_get_contents('php://input'), true);

    $email = $input['email'];
    $password = $input['password'];

    $pdo = Database::connect();

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && $user['email'] === 'admin' && $password === 'ZAQ!2wsx') {
      $genToken = new FunctionGenerateToken();

      $token = $genToken->generateToken($password);
      setcookie("admincookie", $token, time() + 3600 * 24 * 30, "/");

      $stmt = $pdo->prepare('UPDATE users SET token = :token WHERE email = :email');
      $stmt->execute([
        'token' => $token,
        'email' => $email
      ]);

      http_response_code(200);
      echo json_encode([
        'success' => true,
        'admin' => true
      ]);

      return;
    }

    if ($user && password_verify($password, $user['password'])) {
      $_SESSION['email'] = $email;
      $_SESSION['username'] = $user['username'];
      $_SESSION['user_id'] = $user['id'];

      $genToken = new FunctionGenerateToken();

      $token = $genToken->generateToken($email);
      $_SESSION['token'] = $token;

      $stmt = $pdo->prepare('UPDATE users SET token = :token WHERE email = :email');
      $stmt->execute([
        'token' => $token,
        'email' => $email
      ]);

      setcookie("cookietoken", $token, time() + 3600 * 24 * 30, "/");

      http_response_code(200);
      echo json_encode([
        'success' => true,
      ]);

    } else {
      http_response_code(401);
      echo json_encode([
        'success' => false,
        'message' => 'Invalid email or password.'
      ]);
    }
  }

  public function logout(): void
  {
    $pdo = Database::connect();

    $stmt = $pdo->prepare('UPDATE users SET token = NULL WHERE email = :email');
    $stmt->execute([
      'email' => $_SESSION['email'],
    ]);

    session_destroy();
    setcookie('cookietoken', '', time() - 3600 * 24 * 30, '/');
    setcookie('admincookie', '', time() - 3600 * 24 * 30, '/');

    http_response_code(200);
    echo json_encode([
      'success' => true,
    ]);
  }
}
