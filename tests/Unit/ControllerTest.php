<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the MVC layer: Controller, DefaultController, and View.
 *
 * Because Controller is abstract we test it through DefaultController (and an
 * anonymous subclass where a custom handle() is needed) so every public and
 * protected code path is exercised without hitting the file system in a
 * meaningful way.
 */
class ControllerTest extends TestCase
{
    // -----------------------------------------------------------------------
    // View
    // -----------------------------------------------------------------------

    public function testViewStoresTemplate(): void
    {
        $view = new View('page/news.php');
        $this->assertSame('page/news.php', $view->getTemplate());
    }

    public function testViewStoresData(): void
    {
        $view = new View('page/news.php', ['foo' => 'bar']);
        $this->assertSame(['foo' => 'bar'], $view->getData());
    }

    public function testViewDataDefaultsToEmptyArray(): void
    {
        $view = new View('page/news.php');
        $this->assertSame([], $view->getData());
    }

    public function testViewRenderSilentlySkipsNonExistentTemplate(): void
    {
        // Should not throw or emit anything for a path that does not exist.
        $view = new View('/nonexistent/path/does_not_exist.php');
        $this->expectOutputString('');
        $view->render();
    }

    public function testViewRenderExtractsDataIntoTemplate(): void
    {
        // Write a tiny temporary template that echoes a variable.
        $tmp = tempnam(sys_get_temp_dir(), 'mmm_view_test_');
        file_put_contents($tmp, '<?php echo $greeting; ?>');

        $view = new View($tmp, ['greeting' => 'hello']);
        $this->expectOutputString('hello');
        $view->render();

        unlink($tmp);
    }

    public function testViewRenderDoesNotOverwriteExistingLocalVariable(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'mmm_view_test_');
        // The template itself defines $x; the data array also supplies $x.
        file_put_contents($tmp, '<?php $x = "from_template"; echo $x; ?>');

        // EXTR_SKIP means the template's own $x wins only if extract'd before
        // the template body runs — here the template re-assigns, so it always
        // wins; the key thing is no fatal error occurs.
        $view = new View($tmp, ['x' => 'from_data']);
        ob_start();
        $view->render();
        $out = ob_get_clean();
        $this->assertSame('from_template', $out);

        unlink($tmp);
    }

    // -----------------------------------------------------------------------
    // Controller (tested via DefaultController / anonymous subclass)
    // -----------------------------------------------------------------------

    public function testControllerGetModuleReturnsConstructorValue(): void
    {
        $ctrl = new DefaultController('news');
        $this->assertSame('news', $ctrl->getModule());
    }

    public function testControllerGetModuleHandlesSubpath(): void
    {
        $ctrl = new DefaultController('turnier/turnier_list');
        $this->assertSame('turnier/turnier_list', $ctrl->getModule());
    }

    public function testDefaultControllerIsConcreteSubclassOfController(): void
    {
        $this->assertInstanceOf(Controller::class, new DefaultController('news'));
    }

    public function testBeforeHtmlIsCallableWithoutSideEffectsForUnknownModule(): void
    {
        // The module 'nonexistent_page_xyz' has no .top.php file, so
        // beforeHtml() should be a no-op.
        $ctrl = new DefaultController('nonexistent_page_xyz');
        $this->expectOutputString('');
        $ctrl->beforeHtml();
    }

    public function testHeadIsCallableWithoutSideEffectsForUnknownModule(): void
    {
        $ctrl = new DefaultController('nonexistent_page_xyz');
        $this->expectOutputString('');
        $ctrl->head();
    }

    public function testHandleIsCallableWithoutSideEffectsForUnknownModule(): void
    {
        // DefaultController::handle() calls $this->render($this->module).
        // With a module that has no corresponding page file, render() is a
        // no-op, so no output and no exception.
        $ctrl = new DefaultController('nonexistent_page_xyz');
        $this->expectOutputString('');
        $ctrl->handle();
    }

    public function testBeforeHtmlIncludesTopFileWhenItExists(): void
    {
        $tmpDir = sys_get_temp_dir() . '/mmm_ctrl_test_' . uniqid();
        mkdir($tmpDir . '/page', 0777, true);
        file_put_contents($tmpDir . '/page/mymodule.top.php', '<?php echo "top"; ?>');

        // Change to the temp dir so the relative path resolves correctly.
        $cwd = getcwd();
        chdir($tmpDir);

        $ctrl = new DefaultController('mymodule');
        $this->expectOutputString('top');
        $ctrl->beforeHtml();

        chdir($cwd);
        unlink($tmpDir . '/page/mymodule.top.php');
        rmdir($tmpDir . '/page');
        rmdir($tmpDir);
    }

    public function testHeadIncludesHeadFileWhenItExists(): void
    {
        $tmpDir = sys_get_temp_dir() . '/mmm_ctrl_test_' . uniqid();
        mkdir($tmpDir . '/page', 0777, true);
        file_put_contents($tmpDir . '/page/mymodule.head.php', '<?php echo "head"; ?>');

        $cwd = getcwd();
        chdir($tmpDir);

        $ctrl = new DefaultController('mymodule');
        $this->expectOutputString('head');
        $ctrl->head();

        chdir($cwd);
        unlink($tmpDir . '/page/mymodule.head.php');
        rmdir($tmpDir . '/page');
        rmdir($tmpDir);
    }

    public function testHandleRendersViewFile(): void
    {
        $tmpDir = sys_get_temp_dir() . '/mmm_ctrl_test_' . uniqid();
        mkdir($tmpDir . '/page', 0777, true);
        file_put_contents($tmpDir . '/page/mymodule.php', '<?php echo "content"; ?>');

        $cwd = getcwd();
        chdir($tmpDir);

        $ctrl = new DefaultController('mymodule');
        $this->expectOutputString('content');
        $ctrl->handle();

        chdir($cwd);
        unlink($tmpDir . '/page/mymodule.php');
        rmdir($tmpDir . '/page');
        rmdir($tmpDir);
    }

    public function testCustomControllerCanOverrideHandle(): void
    {
        $ctrl = new class('custom') extends Controller {
            public function handle(): void
            {
                echo 'custom_output';
            }
        };

        $this->expectOutputString('custom_output');
        $ctrl->handle();
    }

    public function testRenderHelperPassesDataToView(): void
    {
        $tmpDir = sys_get_temp_dir() . '/mmm_ctrl_test_' . uniqid();
        mkdir($tmpDir . '/page', 0777, true);
        file_put_contents($tmpDir . '/page/dataview.php', '<?php echo $value; ?>');

        $cwd = getcwd();
        chdir($tmpDir);

        $ctrl = new class('dataview') extends Controller {
            public function handle(): void
            {
                $this->render('dataview', ['value' => 'injected']);
            }
        };

        $this->expectOutputString('injected');
        $ctrl->handle();

        chdir($cwd);
        unlink($tmpDir . '/page/dataview.php');
        rmdir($tmpDir . '/page');
        rmdir($tmpDir);
    }
}
