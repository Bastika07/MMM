<?php

/**
 * Base Controller for the MVC layer.
 *
 * Each page module should be handled by a Controller subclass.  The base class
 * provides three lifecycle hooks that index.php calls at different points during
 * the request/response cycle:
 *
 *   1. beforeHtml()  – called before any HTML is emitted (suitable for early
 *                      exits, redirects, or pre-processing form submissions).
 *   2. head()        – called inside the <head> element (suitable for injecting
 *                      page-specific scripts or stylesheets).
 *   3. handle()      – called inside the page content area; must be implemented
 *                      by every concrete controller.
 *
 * The default implementations of beforeHtml() and head() preserve backward
 * compatibility by including the legacy `page/{module}.top.php` and
 * `page/{module}.head.php` files when they exist.
 *
 * The render() helper constructs a View for `page/{view}.php` and renders it,
 * keeping view rendering in one place.
 *
 * Typical usage in index.php:
 *
 *   $controller = new DefaultController($pageModule);
 *   // or, for pages with a dedicated controller file:
 *   // require_once "controllers/{$pageModule}Controller.php";
 *   // $controller = new NewsController($pageModule);
 *
 *   $controller->beforeHtml();   // before <!DOCTYPE html>
 *   // … HTML layout …
 *   $controller->head();         // inside <head>
 *   // … HTML layout …
 *   $controller->handle();       // inside the content <div>
 */
abstract class Controller
{
    /**
     * The resolved module name (relative path under `page/` without `.php`),
     * e.g. `'news'`, `'turnier/turnier_list'`.
     */
    protected string $module;

    public function __construct(string $module)
    {
        $this->module = $module;
    }

    /**
     * Pre-HTML hook.
     *
     * Called before any HTML is sent to the client.  Use this for HTTP
     * redirects, early exits, or form-submission processing that must happen
     * before output starts.
     *
     * Default: includes `page/{module}.top.php` if the file exists.
     */
    public function beforeHtml(): void
    {
        $top = "page/{$this->module}.top.php";
        if (is_file($top)) {
            include $top;
        }
    }

    /**
     * Head hook.
     *
     * Called from within the HTML `<head>` element.  Use this for page-specific
     * `<script>` or `<link>` tags.
     *
     * Default: includes `page/{module}.head.php` if the file exists.
     */
    public function head(): void
    {
        $head = "page/{$this->module}.head.php";
        if (is_file($head)) {
            include $head;
        }
    }

    /**
     * Content handler.
     *
     * Called inside the main content area of the page.  Implement this in
     * every concrete controller to run business logic and render the response.
     */
    abstract public function handle(): void;

    /**
     * Render a view template.
     *
     * Constructs a View for `page/{$view}.php` and calls its render() method.
     *
     * @param string              $view Relative view name (without `page/` prefix and
     *                                  `.php` extension), e.g. `'news'`.
     * @param array<string,mixed> $data Optional data to expose as local variables
     *                                  inside the template.
     */
    protected function render(string $view, array $data = []): void
    {
        (new View("page/{$view}.php", $data))->render();
    }

    /**
     * Return the module name this controller was constructed with.
     */
    public function getModule(): string
    {
        return $this->module;
    }
}

// ---------------------------------------------------------------------------
// Default controller
// ---------------------------------------------------------------------------

/**
 * Generic controller used for all pages that do not have a dedicated
 * Controller subclass.
 *
 * Delegates the three lifecycle hooks to the corresponding legacy
 * `page/{module}.{top,head,}.php` files (via the base-class defaults), so
 * existing pages continue to work without any changes.
 */
class DefaultController extends Controller
{
    /**
     * Render the page view by including `page/{module}.php`.
     */
    public function handle(): void
    {
        $this->render($this->module);
    }
}
