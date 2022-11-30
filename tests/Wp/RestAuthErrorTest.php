<?php

namespace RebelCode\WpSdk\Tests\Wp;

use RebelCode\WpSdk\Wp\RestAuthError;
use PHPUnit\Framework\TestCase;

class RestAuthErrorTest extends TestCase
{
    public function testCtorShouldSetProperties()
    {
        $error = new RestAuthError(401, ['Invalid credentials', 'User does not exist']);

        $this->assertSame(401, $error->status);
        $this->assertSame(['Invalid credentials', 'User does not exist'], $error->reasons);
    }
}
