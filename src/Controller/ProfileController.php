<?php

namespace App\Controller;

use App\Database\Database;
use App\BaseUrl\BaseUrlFunction;

class ProfileController
{
  public function displayName(): void
  {
    http_response_code(200);
    $displayName = $_SESSION['username'];
    echo json_encode([
      'success' => true,
      'message' => 'The username is already taken.',
      'username' => $displayName
    ]);
  }

  public function changeusername(): void
  {
    $input = json_decode(file_get_contents('php://input'), true);

    $username = $input['username'];
    $email = $_SESSION['email'];

    $pdo = Database::connect();

    $stmt = $pdo->prepare('UPDATE users SET username = :username WHERE email = :email');
    $stmt->execute([
      'username' => $username,
      'email' => $email,
    ]);

    http_response_code(201);
    echo json_encode([
      'success' => true,
    ]);
  }


  public function uploadawatar(): void
  {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $_SESSION['email'];

    $imageBase64 = $input['image'];
    $name = $input['name'];
    $image = base64_decode($imageBase64);


    $path = BaseUrlFunction::$baseUrl . "avatars/";
    $imageName = $_SESSION['token'] . $name;
    $avatarpath = $path . $imageName;

    $pdo = Database::connect();

    $stmt = $pdo->prepare('SELECT avatar FROM users WHERE email = :email');
    $stmt->execute([
      'email' => $email,
    ]);
    $oldawatar = $stmt->fetchColumn();


    if ($oldawatar && file_exists($oldawatar)) {
      if ($oldawatar === BaseUrlFunction::$baseUrl . "avatars/defaultAvatar.png") {
      } else {
        unlink($oldawatar);
      }
    }


    file_put_contents($path . $imageName, $image);

    $stmt = $pdo->prepare('UPDATE users SET avatar = :avatar WHERE email = :email');
    $stmt->execute([
      'avatar' => $avatarpath,
      'email' => $email,
    ]);
    $_SESSION['avatar'] = $avatarpath;

    http_response_code(200);
    echo json_encode([
      'success' => true,
    ]);
  }
  public function displayAvatar (): void
  {
    $avatarpath = $_SESSION['avatar'];


    $imageData = file_get_contents($avatarpath);
    $base64 = base64_encode($imageData);
    $mimeType = mime_content_type($avatarpath);
    $fullAvatar = 'data:' . $mimeType . ';base64,' . $base64;

    http_response_code(200);
    echo json_encode([
      'success' => true,
      'avatar' => $fullAvatar
    ]);
  }


  public function countReview(): void {

    $pdo = Database::connect();
    $stmt = $pdo->prepare('SELECT COUNT(*) as count_reviews FROM review WHERE user_id = :id');
    $stmt->execute([
      'id' => $_SESSION['user_id'],
    ]);
    $count = $stmt->fetch();
    $count_reviews = $count['count_reviews'];

    http_response_code(200);
    echo json_encode([
      'success' => true,
      'count_reviews' => $count_reviews
    ]);
  }

  public function userFilms(): void {
    $pdo = Database::connect();
    $user_id = $_SESSION['user_id'];

    $stmtComments = $pdo->prepare("
        SELECT f.id AS film_id, f.nazwa AS film_title, f.obraz_filmu AS film_image, c.text AS comment_text, DATE(c.created_at) AS comment_date
        FROM comments c
        JOIN films f ON f.id = c.film_id
        WHERE c.user_id = :user_id
        GROUP BY c.created_at DESC
    ");
    $stmtComments->execute([
      'user_id' => $user_id
    ]);
    $comments = $stmtComments->fetchAll();


    $stmtReviews = $pdo->prepare("
        SELECT f.id AS film_id, f.nazwa AS film_title, f.obraz_filmu AS film_image, r.text AS review_text, r.rating AS review_rating, DATE(r.created_at) AS review_date
        FROM review r
        JOIN films f ON f.id = r.film_id
        WHERE r.user_id = :user_id
        GROUP BY r.created_at DESC
    ");
    $stmtReviews->execute([
      'user_id' => $user_id
    ]);
    $reviews = $stmtReviews->fetchAll();

    $films = [];

    foreach ($comments as $comment) {
      $filmId = $comment['film_id'];
      if (!isset($films[$filmId])) {
        $films[$filmId] = [
          'film_id' => $filmId,
          'film_title' => $comment['film_title'],
          'film_image' => $comment['film_image'],
          'comments' => [],
        ];
      }
      $films[$filmId]['comments'][] = [
        'text' => $comment['comment_text'],
        'date' => $comment['comment_date'],
      ];
    }



    foreach ($reviews as $review) {
      $filmId = $review['film_id'];
      if (!isset($films[$filmId])) {
        $films[$filmId] = [
          'film_id' => $filmId,
          'film_title' => $review['film_title'],
          'film_image' => $review['film_image'],
          'reviews' => []
        ];
      }
      $films[$filmId]['reviews'][] = [
        'text' => $review['review_text'],
        'date' => $review['review_date'],
        'rating' => $review['review_rating'],
      ];
    }

    $result = array_values($films);

    foreach ($result as &$film) {
      $obraz_filmu = $film['film_image'];

      if (file_exists($obraz_filmu)) {
        $imageData = file_get_contents($obraz_filmu);
        $base64 = base64_encode($imageData);
        $mimeType = mime_content_type($obraz_filmu);
        $film['film_image'] = 'data:' . $mimeType . ';base64,' . $base64;
      } else {
        $film['film_image'] = null;
      }
    }
    unset($film);

    http_response_code(200);
    echo json_encode([
      'success' => true,
      'films' => $result
    ]);
  }
}
