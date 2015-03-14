<?php
namespace Fixture;

use Ranyuen\Little\Request;
use Ranyuen\Little\Router;

/** @Route('/blog') */
 class BlogController
 {
     /**
      * @Route('/:page',name=blog_index,assert={page='/\d+/'})
      */
     public function index($page = 1)
     {
         return "blog index $page";
     }

     /** @Route('/show/:id') */
     public function show(Router $r, Request $req)
     {
         return $r->error(403, $req);
     }

     /** @Route('/show/:id',via=POST) */
     public function showPost($id)
     {
         return "POST $id";
     }

     /** @Route(error=403) */
     public function notFound()
     {
         return 'blog 403';
     }
 }
