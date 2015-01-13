<?php
namespace Fixture;

use Ranyuen\Little\HttpKernelInterface;
use Ranyuen\Little\Request;

class HelloMiddleware implements HttpKernelInterface
{
    private $app;

    public function __construct(HttpKernelInterface $app)
    {
        $this->app = $app;
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $request->query->set('name', 'World');
        $res = $this->app->handle($request, $type, $catch);
        $res->setContent('Hello '.$res->getContent());
        return $res;
    }
}
