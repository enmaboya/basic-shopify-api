<?php

declare(strict_types=1);

namespace Gnikyt\BasicShopifyAPI\Test\Traits;

use Gnikyt\BasicShopifyAPI\Test\BaseTest;
use Gnikyt\BasicShopifyAPI\Traits\IsRequestType;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;

class IsRequestTypeTest extends BaseTest
{
    public function test(): void
    {
        // Create anon class
        $klass = new class {
            use IsRequestType;

            private $self;

            public function setSelf(TestCase $self): void
            {
                $this->self = $self;
            }

            public function testGraph(): void
            {
                $this->self->assertTrue($this->isGraphRequest(new Uri('/admin/api/graphql.json')));
                $this->self->assertFalse($this->isGraphRequest(new Uri('/admin/api/unstable/shop.json')));
            }

            public function testRest(): void
            {
                $this->self->assertFalse($this->isRestRequest(new Uri('/admin/api/graphql.json')));
                $this->self->assertTrue($this->isRestRequest(new Uri('/admin/api/unstable/shop.json')));
            }
        };

        $klass->setSelf($this);
        $klass->testGraph();
        $klass->testRest();
    }
}
