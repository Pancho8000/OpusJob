<?php
require_once __DIR__ . '/../app/core/App.php';

$r1 = App::resolveAliases(['empleos']);
$runner->assertEquals($r1[0] ?? null, 'Home', 'Alias empleos -> Home');
$runner->assertEquals($r1[1] ?? null, 'index', 'Alias empleos default method index');

$r2 = App::resolveAliases(['empleos', 'feed']);
$runner->assertEquals($r2[0] ?? null, 'Home', 'Alias empleos/feed -> Home');
$runner->assertEquals($r2[1] ?? null, 'feed', 'Alias empleos/feed preserva method');

$r3 = App::resolveAliases(['login']);
$runner->assertEquals($r3[0] ?? null, 'Users', 'Alias login -> Users');
$runner->assertEquals($r3[1] ?? null, 'login', 'Alias login -> login');

$r4 = App::resolveAliases(['mi-perfil']);
$runner->assertEquals($r4[0] ?? null, 'Users', 'Alias mi-perfil -> Users');
$runner->assertEquals($r4[1] ?? null, 'me', 'Alias mi-perfil -> me');

$r5 = App::resolveAliases(['reclutamiento']);
$runner->assertEquals($r5[0] ?? null, 'Recruiter', 'Alias reclutamiento -> Recruiter');
$runner->assertEquals($r5[1] ?? null, 'pipeline', 'Alias reclutamiento -> pipeline');

$r6 = App::resolveAliases(['mis-ofertas']);
$runner->assertEquals($r6[0] ?? null, 'Recruiter', 'Alias mis-ofertas -> Recruiter');
$runner->assertEquals($r6[1] ?? null, 'myJobs', 'Alias mis-ofertas -> myJobs');

$r7 = App::resolveAliases(['reclutador', '2']);
$runner->assertEquals($r7[0] ?? null, 'Users', 'Alias reclutador -> Users');
$runner->assertEquals($r7[1] ?? null, 'public', 'Alias reclutador/{id} -> public');
$runner->assertEquals($r7[2] ?? null, '2', 'Alias reclutador/{id} preserva id');
