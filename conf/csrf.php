<?php

/*
|--------------------------------------------------------------------------
| Cross Site Request Forgery
|--------------------------------------------------------------------------
| Enables a CSRF cookie token to be set. When set to TRUE, token will be
| checked on a submitted form. If you are accepting user data, it is strongly
| recommended CSRF protection be enabled.
|
| 'csrf_token_name' = The token name
| 'csrf_cookie_name' = The cookie name
| 'csrf_expire' = The number in seconds the token should expire.
| 'csrf_regenerate' = Regenerate token on every submission
| 'csrf_exclude_uris' = Array of URIs which ignore CSRF checks
*/
return array(
    "csrf_protection" => false,
    "csrf_token_name" => "csrf_test_name",
    "csrf_cookie_name" => "csrf_cookie_name",
    "csrf_expire" => 7200,
    "csrf_regenerate" => true,
    "csrf_exclude_uris" => array()
);