<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PingController
{
    public function handle(Request $request): Response
    {
        return new Response('ok');
    }
}