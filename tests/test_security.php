<?php
$runner->assertTrue(function_exists('generate_csrf_token'), 'generate_csrf_token existe');
$runner->assertTrue(function_exists('verify_csrf_token'), 'verify_csrf_token existe');
$runner->assertTrue(function_exists('esc'), 'esc existe');

$t1 = generate_csrf_token();
$t2 = generate_csrf_token();
$runner->assertEquals($t1, $t2, 'CSRF token es estable en la sesión');
$runner->assertTrue(strlen($t1) >= 32, 'CSRF token tiene longitud mínima');

$runner->assertTrue(verify_csrf_token($t1) === true, 'CSRF token válido verifica correctamente');
$runner->assertTrue(verify_csrf_token('invalid') === false, 'CSRF token inválido falla correctamente');

$runner->assertEquals(esc('<b>hola</b>'), '&lt;b&gt;hola&lt;/b&gt;', 'esc escapa HTML');

