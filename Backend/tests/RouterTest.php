<?php
use App\Core\Router;
$r=new Router(); $r->get('/api/v1/deportes', fn()=>null);
assert(($r->match('GET','/api/v1/deportes')['error']??null)===null);
assert(($r->match('GET','/api/v1/deportes/')['error']??null)===null);
assert($r->match('POST','/api/v1/deportes')['error']===405);
assert(($r->match('GET','/api/v1//deportes')['error']??null)===null);
echo 'Router tests passed';
