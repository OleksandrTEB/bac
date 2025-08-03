<?php
namespace App\Controller;

use App\Database\Database;

class CommentController {
  public function addcomm() {
    $input = json_decode(file_get_contents('php://input'), true);

    $comm = $input['text'];

    $pdo = Database::connect();

    $stmt = $pdo->prepare('INSERT INTO comments (film_id, user_id, text) VALUES (:film_id, :user_id, :text)');
    $stmt->execute([
      'film_id' => $_SESSION['film_id'],
      'user_id' => $_SESSION['user_id'],
      'text' => $comm,
    ]);

    http_response_code(201);
    echo json_encode([
      'sucess' => true
    ]);
  }


  public function getcomment() {
    $film_id = $_SESSION['film_id'];

    $pdo = Database::connect();

    $stmt = $pdo->prepare("SELECT c.id, c.text, DATE(c.created_at) AS created_at, u.username, u.avatar FROM comments c JOIN users u ON c.user_id = u.id WHERE c.film_id = :film_id ORDER BY c.id DESC");
    $stmt->execute(['film_id' => $film_id]);
    $comments = $stmt->fetchAll();

    foreach ($comments as &$comment) {
      $avatarPath = $comment['avatar'];
      if (!empty($avatarPath) && file_exists($avatarPath)) {
        $imageData = file_get_contents($avatarPath);
        $base64 = base64_encode($imageData);
        $mimeType = mime_content_type($avatarPath);
        $comment['avatar'] = 'data:' . $mimeType . ';base64,' . $base64;
      } else {
        $comment['avatar'] = null;
      }
    }
    unset($comment);

    echo json_encode([
      'success' => true,
      'comments' => $comments,
    ]);
  }


  public function searchCommentFromDelete(): void
  {

    $pdo = Database::connect();

    $stmt = $pdo->prepare('SELECT id FROM comments WHERE user_id = :user_id');
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $comments = $stmt->fetchAll();

    http_response_code(200);
    echo json_encode([
      'success' => true,
      'comments' => $comments
    ]);
  }

  public function deleteComment(): void {
    $input = json_decode(file_get_contents('php://input'), true);

    $id = $input['id'];

    $pdo = Database::connect();

    $isAdmin = isset($_COOKIE['admincookie']);

    if ($isAdmin) {
      $stmt = $pdo->prepare('DELETE FROM comments WHERE id = :id');
      $stmt->execute(['id' => $id]);
    } else {
      $stmt = $pdo->prepare('DELETE FROM comments WHERE id = :id AND user_id = :user_id');
      $stmt->execute([
        'id' => $id,
        'user_id' => $_SESSION['user_id'],
      ]);
    }

    http_response_code(200);
    echo json_encode([
      'success' => true,
    ]);
  }
}
