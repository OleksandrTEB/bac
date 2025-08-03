<?php

namespace App\Controller;

use App\Database\Database;
use App\BaseUrl\BaseUrlFunction;

class FilmsController
{
  public function uploadFilm(): void
  {
    $input = json_decode(file_get_contents('php://input'), true);

    $imageFilm = $input['imageFilm'];
    $imageBase64 = base64_decode($imageFilm);
    $name = $input['name'];
    $title = $input['title'];
    $gatunek = $input['gatunek'];
    $year = $input['year'];
    $opis = $input['opis'];
    $trailer = $input['zwiastun'];
    $video = $input['video'];

    $path = BaseUrlFunction::$baseUrl . "films/";
    $imageName = $name;
    $filmPath = $path . $imageName;

    file_put_contents($path . $imageName, $imageBase64);

    $pdo = Database::connect();

    $stmt = $pdo->prepare('INSERT INTO films (nazwa, rok, gatunek, opis, obraz_filmu, trailer, video) VALUES (:nazwa, :rok, :gatunek, :opis, :obraz_filmu, :trailer, :video)');
    $stmt->execute([
      'nazwa' => $title,
      'rok' => $year,
      'gatunek' => $gatunek,
      'opis' => $opis,
      'obraz_filmu' => $filmPath,
      'trailer' => $trailer,
      'video' => $video,
    ]);

    http_response_code(201);
    echo json_encode([
      'success' => true,
    ]);
  }


  public function loadfilm(): void
  {

    $pdo = Database::connect();

    $stmt = $pdo->prepare('SELECT * FROM films');
    $stmt->execute();
    $films = $stmt->fetchAll();

    foreach ($films as &$film) {
      $obraz_filmu = $film['obraz_filmu'];

      $imageData = file_get_contents($obraz_filmu);
      $base64 = base64_encode($imageData);
      $mimeType = mime_content_type($obraz_filmu);
      $obraz_filmu = 'data:' . $mimeType . ';base64,' . $base64;

      $film['obraz_filmu'] = $obraz_filmu;
    }
    unset($film);

    http_response_code(200);
    echo json_encode([
      'success' => true,
      'films' => $films,
    ]);
  }


  public function searchfilm(): void
  {
    $input = json_decode(file_get_contents('php://input'), true);

    $film_id = $input['filmId'];

    $pdo = Database::connect();

    $stmt = $pdo->prepare('SELECT * FROM films WHERE id = :id');
    $stmt->execute(['id' => $film_id]);
    $films = $stmt->fetch();

    $film_id = $films['id'];
    $_SESSION['film_id'] = $film_id;
    $nazwa = $films['nazwa'];
    $rok = $films['rok'];
    $gatunek = $films['gatunek'];
    $opis = $films['opis'];
    $obraz_filmu = $films['obraz_filmu'];


    $imageData = file_get_contents($obraz_filmu);
    $base64 = base64_encode($imageData);
    $mimeType = mime_content_type($obraz_filmu);
    $obraz_filmu = 'data:' . $mimeType . ';base64,' . $base64;


    $trailer = $films['trailer'];
    $video = $films['video'];
    $ocena = $films['ocena'];

    http_response_code(200);
    echo json_encode([
      'success' => true,
      'id' => $film_id,
      'nazwa' => $nazwa,
      'rok' => $rok,
      'gatunek' => $gatunek,
      'opis' => $opis,
      'obraz_filmu' => $obraz_filmu,
      'trailer' => $trailer,
      'video' => $video,
      'ocena' => $ocena,
    ]);
  }
}
