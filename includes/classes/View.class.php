<?php

/**
 * View renderer.
 *
 * Wraps a PHP template file and (optionally) a set of data variables that are
 * extracted into local scope before the template is included.  This separates
 * template rendering from the Controller layer and keeps view logic contained
 * within dedicated template files.
 *
 * Typical usage from a Controller:
 *
 *   $view = new View('page/news.php', ['title' => 'News']);
 *   $view->render();
 *
 * Or via the Controller::render() helper which constructs the path automatically.
 */
class View
{
    /** @var string Absolute or relative path to the PHP template file. */
    private string $template;

    /** @var array<string,mixed> Data variables to expose inside the template. */
    private array $data;

    /**
     * @param string             $template Absolute or relative path to the template file.
     * @param array<string,mixed> $data    Key-value pairs exposed as local variables inside
     *                                    the template via extract().  Existing variables are
     *                                    NOT overwritten (EXTR_SKIP).
     */
    public function __construct(string $template, array $data = [])
    {
        $this->template = $template;
        $this->data     = $data;
    }

    /**
     * Include the template file, optionally with extracted data variables.
     *
     * If the template file does not exist the method returns silently (so that
     * pages without a dedicated view file are handled gracefully).
     */
    public function render(): void
    {
        if (!is_file($this->template)) {
            return;
        }

        if (!empty($this->data)) {
            extract($this->data, EXTR_SKIP);
        }

        include $this->template;
    }

    /**
     * Return the configured template path.
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * Return the data array passed to the constructor.
     *
     * @return array<string,mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }
}
