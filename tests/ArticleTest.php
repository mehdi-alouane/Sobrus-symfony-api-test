<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class ArticleTest extends ApiTestCase
{
    public function testSomething(): void
    {
        $response = static::createClient()->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['@id' => '/']);
    }
}
