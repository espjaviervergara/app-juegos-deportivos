<?php
namespace App\Core;

interface MiddlewareInterface
{
    public function handle(Request $req, callable $next);
}
