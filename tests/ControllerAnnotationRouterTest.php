<?php
require_once 'tests/Fixture/BlogController.php';

use Ranyuen\Little\Request;
use Ranyuen\Little\Router;

class ControllerAnnotationRouterTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        $prop = new \ReflectionProperty('Ranyuen\Little\Router', 'plugins');
        $prop->setAccessible(true);
        $prop->setValue([]);
    }

    public function testRegisterController()
    {
        Router::plugin('Ranyuen\Little\Plugin\ControllerAnnotationRouter');
        $r = new Router();
        $r->registerController('Fixture\BlogController');

        $req = Request::create('/blog/2');
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('blog index 2', $res->getContent());

        $req = Request::create('/blog/');
        $req->query->set('page', 1);
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('blog index 1', $res->getContent());
        // $res = $r->run('blog_index', $req);
        // $this->assertEquals(200, $res->getStatusCode());
        // $this->assertEquals('blog index 1', $res->getContent());

        $req = Request::create('/blog/show/mOmonga');
        $res = $r->run($req);
        $this->assertEquals(403, $res->getStatusCode());
        $this->assertEquals('blog 403', $res->getContent());

        $req = Request::create('/blog/show/mOmonga', 'POST');
        $res = $r->run($req);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('POST mOmonga', $res->getContent());
    }
}
