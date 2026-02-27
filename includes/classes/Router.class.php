<?php

/**
 * Lightweight front-controller router.
 *
 * Maps integer page IDs to module names (relative paths under `page/` without
 * the `.php` extension) and resolves which module should handle the current
 * request.  This separates the routing concern from the rest of index.php and
 * makes the route table independently testable.
 *
 * Typical usage in index.php:
 *
 *   $router = new Router();
 *   $router->add(1,  'start')
 *          ->add(2,  'news')
 *          ->add(5,  'login');
 *
 *   $module = $router->resolve((int)($_GET['page'] ?? 1));
 *   // $module is now e.g. 'start' or 'turnier/turnier_list'
 *
 *   if (is_file("page/{$module}.php")) {
 *       include "page/{$module}.php";
 *   }
 */
class Router
{
    /** @var array<int,string> Map of page ID → module name */
    private array $routes = [];

    /** Default page ID used when the requested ID is not registered. */
    private int $defaultId;

    public function __construct(int $defaultId = 1)
    {
        $this->defaultId = $defaultId;
    }

    /**
     * Register a route.
     *
     * @param int    $id     Integer page ID (value of the `?page=` query parameter).
     * @param string $module Module name relative to the `page/` directory, without `.php`
     *                       extension (e.g. `'news'`, `'turnier/turnier_list'`).
     * @return static Fluent interface for chaining.
     */
    public function add(int $id, string $module): static
    {
        $this->routes[$id] = $module;
        return $this;
    }

    /**
     * Resolve a page ID to its module name.
     *
     * If the given ID is not registered the default route is returned instead.
     * If the default ID is also not registered, `'error'` is returned as the
     * ultimate fallback.
     *
     * @param int $pageId The page ID from the request (e.g. `(int)$_GET['page']`).
     * @return string Module name (relative path without `.php`).
     */
    public function resolve(int $pageId): string
    {
        if (array_key_exists($pageId, $this->routes)) {
            return $this->routes[$pageId];
        }

        if (array_key_exists($this->defaultId, $this->routes)) {
            return $this->routes[$this->defaultId];
        }

        return 'error';
    }

    /**
     * Return true when the given page ID has a registered route.
     */
    public function has(int $pageId): bool
    {
        return array_key_exists($pageId, $this->routes);
    }

    /**
     * Return the full route map (page ID → module name).
     *
     * @return array<int,string>
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Return the default page ID used when an unknown ID is requested.
     */
    public function getDefaultId(): int
    {
        return $this->defaultId;
    }
}
