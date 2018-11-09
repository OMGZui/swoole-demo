<?php
/**
 * Created by PhpStorm.
 * User: 小粽子
 * Date: 2018/9/14
 * Time: 09:35
 */

require __DIR__ . '/../bootstrap.php'; // Composer's autoloader

$client = \Symfony\Component\Panther\Client::createChromeClient();
$crawler = $client->request('GET', 'http://api-platform.com'); // Yes, this website is 100% in JavaScript

$link = $crawler->selectLink('Support')->link();
$crawler = $client->click($link);

// Wait for an element to be rendered
$client->waitFor('.support');

dump($crawler->filter('.support')->text());
$client->takeScreenshot('screen.png'); // Yeah, screenshot!