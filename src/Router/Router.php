<?php
namespace App\Router;



class Router {

  private array $routes = [];

  public function get(string $path, callable | array $handler): void {
    $this->addRoute('GET', $path, $handler);
  }

  public function post(string $path, callable | array $handler): void {
    $this->addRoute('POST', $path, $handler);
  }

  public function put(string $path, callable | array $handler): void {
    $this->addRoute('PUT', $path, $handler);
  }

  private function addRoute(string $method, string $path, callable | array $handler): void {
    $this->routes[] = compact('method', 'path', 'handler');
  }

  public function run(): void {

    $fullUrl = $_SERVER['REQUEST_URI'];
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $url = substr($fullUrl, strlen($scriptName));

    $method = $_SERVER['REQUEST_METHOD'];

    foreach ($this->routes as $route) {
      if ($method === $route['method'] && $url === $route['path']) {
        if (is_array($route['handler'])) {
          [$middleware, $action] = $route['handler'];
          $middleware->handle();
          call_user_func($action);
        } else {
          call_user_func($route['handler']);
        }
        return;
      }
    }

    http_response_code(404);
    echo json_encode([
      'success' => false,
      'message' => 'The endpoint does not exist.'
    ]);
  }
}

