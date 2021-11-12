<?php

namespace Fixture;

// use Ranyuen\Little\Request;
use Symfony\Component\HttpFoundation\Request; // HHVM can't understand class_alias() when chaking interfaces.
use Symfony\Component\HttpKernel\HttpKernelInterface;

class FirstMiddleware implements HttpKernelInterface
{
    private $next;

    public function __construct(HttpKernelInterface $app)
    {
        $this->next = $app;
    }

    public function handle(Request $req, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $req->query->set('content', $req->query->get('content', '').' b1');
        $res = $this->next->handle($req, $type, $catch);
        $res->setContent($res->getContent().' a1');
        return $res;
    }
}

class SecondMiddleware implements HttpKernelInterface
{
    private $next;

    public function __construct(HttpKernelInterface $app)
    {
        $this->next = $app;
    }

    public function handle(Request $req, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $req->query->set('content', $req->query->get('content', '').' b2');
        $res = $this->next->handle($req, $type, $catch);
        $res->setContent($res->getContent().' a2');
        return $res;
    }
}

class ThirdMiddleware implements HttpKernelInterface
{
    private $next;

    public function __construct(HttpKernelInterface $app)
    {
        $this->next = $app;
    }

    public function handle(Request $req, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $req->query->set('content', $req->query->get('content', '').' b3');
        $res = $this->next->handle($req, $type, $catch);
        $res->setContent($res->getContent().' a3');
        return $res;
    }
}

/** @Route('/g', stack={'Fixture\ThirdMiddleware'}) */
class StackController
{
    /** @Route('/') */
    public function index($req)
    {
        return $req->query->get('content', '').' /g/';
    }
}
