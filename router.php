<?php
// Router for the local PHP server; keep local database and tooling files private.
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
if (preg_match('~^/(?:var|database|scripts)(?:/|$)|/\.~', $path)) {
    http_response_code(404);
    exit('Not found');
}
return false;
