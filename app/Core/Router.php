<?php
namespace app\Core;

use app\ext\AppExtension;
use Twig\Extra\String\StringExtension;

class Router
{
    private array $routeMap = [];

    public function __construct(public Request $request, public Response $response) {}
    public function get(string $url, $callback) { $this->routeMap['get'][$this->normalize($url)] = $callback; }
    public function post(string $url, $callback) { $this->routeMap['post'][$this->normalize($url)] = $callback; }
    public function put(string $url, $callback): void { $this->routeMap['put'][$this->normalize($url)] = $callback; }
    public function patch(string $url, $callback): void { $this->routeMap['patch'][$this->normalize($url)] = $callback; }
    public function delete(string $url, $callback): void { $this->routeMap['delete'][$this->normalize($url)] = $callback; }
    public function getRouteMap($method): array { return $this->routeMap[$method] ?? []; }

    private function normalize(string $path): string { return '/' . trim($path, '/'); }

    private function match(string $route, string $url): ?array
    {
        $pattern = '';
        $offset = 0;
        preg_match_all('/\\{([a-zA-Z_]\\w*)(?::([^{}]+))?\\}/', $route, $tokens, PREG_OFFSET_CAPTURE);
        foreach ($tokens[0] as $index => [$token, $start]) {
            $pattern .= preg_quote(substr($route, $offset, $start - $offset), '~');
            $name = $tokens[1][$index][0];
            $constraint = $tokens[2][$index][0] ?? '';
            $pattern .= '(?P<' . $name . '>' . ($constraint !== '' ? str_replace('~', '\\~', $constraint) : '[^/]+') . ')';
            $offset = $start + strlen($token);
        }
        $pattern .= preg_quote(substr($route, $offset), '~');
        if (!preg_match('~^' . $pattern . '$~D', $url, $values)) return null;
        return array_filter($values, 'is_string', ARRAY_FILTER_USE_KEY);
    }

    private function find(string $method, string $url): mixed
    {
        $routes = $this->getRouteMap($method);
        if (isset($routes[$url])) {
            $this->request->setRouteParams([]);
            $this->request->setAttribute('route_template', $url);
            return $routes[$url];
        }
        foreach ($routes as $route => $callback) {
            $params = $this->match($route, $url);
            if ($params !== null) {
                $this->request->setRouteParams($params);
                $this->request->setAttribute('route_template', $route);
                return $callback;
            }
        }
        return false;
    }

    public function getCallback()
    {
        $method = $this->request->getMethod();
        return $this->find($method === 'head' ? 'get' : $method, $this->normalize($this->request->getUrl()));
    }

    public function resolve()
    {
        $callback = $this->getCallback();
        if ($callback === false) {
            $allowed = [];
            $url = $this->normalize($this->request->getUrl());
            foreach ($this->routeMap as $method => $routes) {
                foreach ($routes as $route => $_) {
                    if ($this->match($route, $url) !== null) { $allowed[] = strtoupper($method); break; }
                }
            }
            if (in_array('GET', $allowed, true)) $allowed[] = 'HEAD';
            if ($allowed) {
                $allowed[] = 'OPTIONS';
                header('Allow: ' . implode(', ', $allowed));
                if ($this->request->getMethod() === 'options') { $this->response->setStatusCode(204); return ''; }
                throw new ApiException('METHOD_NOT_ALLOWED', 'Method not allowed for this endpoint.', 405);
            }
            throw new ApiException('NOT_FOUND', 'Page not found.', 404);
        }
        if (is_string($callback)) return $this->render($callback);
        if (is_array($callback) && is_string($callback[0])) {
            $controller = new $callback[0];
            $controller->action = $callback[1];
            Application::$app->controller = $controller;
            foreach ($controller->getMiddlewares() as $middleware) $middleware->execute();
            $callback[0] = $controller;
        }
        return call_user_func($callback, $this->request, $this->response);
    }

    public function render($view, $data = [])
    {
        //   var_dump($data);
        $loader = new \Twig\Loader\FilesystemLoader(
            Application::$ROOT_DIR . '/public/views/'
        );

        // Instantiate our Twig
        $twig = new \Twig\Environment($loader, [
            'cache' => Application::$ROOT_DIR . '/public/runtime/',
            'auto_reload' => true,
        ]);
        $twig->addExtension(new StringExtension());
        $twig->addExtension(new AppExtension());

        $seo = [
            'base_url' => defined('BASE_URL') ? rtrim(BASE_URL, '/') : '',
            'current_path' => $this->request->getUrl(),
        ];
        $data = array_merge($seo, $data);

        return $twig->render("$view.twig", $data);
    }
}
