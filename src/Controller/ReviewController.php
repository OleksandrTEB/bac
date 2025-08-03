<?php
namespace App\Controller;

use App\Database\Database;

class ReviewController {
  public function addReview(): Void {
    $input = json_decode(file_get_contents('php://input'), true);

    $text = $input['text'];
    $rating = $input['rating'];

    if ($rating < 1 || $rating > 5) return;


    $pdo = Database::connect();

    $stmt = $pdo->prepare('INSERT INTO review (film_id, user_id, text, rating) VALUES (:film_id, :user_id, :text, :rating)');
    $stmt->execute([
      'film_id' => $_SESSION['film_id'],
      'user_id' => $_SESSION['user_id'],
      'text' => $text,
      'rating' => $rating
    ]);

    http_response_code(201);
    echo json_encode([
      'sucess' => true
    ]);

    $this->countRating();
  }

  public function countRating(): void {

    $pdo = Database::connect();

    $stmt = $pdo->prepare('SELECT AVG(rating) AS ocena FROM review WHERE film_id = :film_id');
    $stmt->execute([
      'film_id' => $_SESSION['film_id']
    ]);
    $row = $stmt->fetch();

    $ocena = $row['ocena'];

    if ($row) {
      $stmt = $pdo->prepare('UPDATE films SET ocena = :ocena WHERE id = :film_id');
      $stmt->execute([
        'film_id' => $_SESSION['film_id'],
        'ocena' => $ocena
      ]);
    }
  }


  public function getReview() {
    $film_id = $_SESSION['film_id'];

    $pdo = Database::connect();

    $stmt = $pdo->prepare("SELECT r.id, r.text, DATE(r.created_at) AS created_at, u.username, u.avatar, r.rating FROM review r JOIN users u ON r.user_id = u.id WHERE r.film_id = :film_id ORDER BY r.id DESC");
    $stmt->execute(['film_id' => $film_id]);
    $reviews = $stmt->fetchAll();

    foreach ($reviews as &$review) {
      $avatarPath = $review['avatar'];
      if (!empty($avatarPath) && file_exists($avatarPath)) {
        $imageData = file_get_contents($avatarPath);
        $base64 = base64_encode($imageData);
        $mimeType = mime_content_type($avatarPath);
        $review['avatar'] = 'data:' . $mimeType . ';base64,' . $base64;
      } else {
        $review['avatar'] = null;
      }
    }
    unset($comment);

    echo json_encode([
      'success' => true,
      'reviews' => $reviews,
    ]);
  }


  public function searchReviewFromDelete(): void
  {

    $pdo = Database::connect();

    $stmt = $pdo->prepare('SELECT id FROM review WHERE user_id = :user_id');
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $reviews = $stmt->fetchAll();

    http_response_code(200);
    echo json_encode([
      'success' => true,
      'reviews' => $reviews
    ]);
  }


  public function deleteReview(): void {
    $input = json_decode(file_get_contents('php://input'), true);

    $id = $input['id'];

    $pdo = Database::connect();

    $isAdmin = isset($_COOKIE['admincookie']);

    if ($isAdmin) {
      $stmt = $pdo->prepare('DELETE FROM review WHERE id = :id');
      $stmt->execute(['id' => $id]);
    } else {
      $stmt = $pdo->prepare('DELETE FROM review WHERE id = :id AND user_id = :user_id');
      $stmt->execute([
        'id' => $id,
        'user_id' => $_SESSION['user_id'],
      ]);
    }

    http_response_code(200);
    echo json_encode([
      'success' => true,
    ]);
    $this->countRating();
  }
}
