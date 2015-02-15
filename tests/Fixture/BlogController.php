<?php
namespace Fixture;

use Ranyuen\Little\Request;
use Ranyuen\Little\Router;

/** @Route('/blog') */
 class BlogController
 {
     /**
      * @Route('/{page}',name=blog_index,assert={page='/\A\d+\z/'})
      */
     public function index($page = 1)
     {
         return "blog index $page";
     }

     /** @Route('/show/{id}') */
     public function show(Router $r, Request $req)
     {
         return $r->error(404, $req);
     }

     /** @Route(error=404) */
     public function notFound()
     {
         return 'blog 404';
     }
 }
