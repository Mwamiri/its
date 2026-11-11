<?php

use App\Libraries\SetupKey;
use CodeIgniter\Test\CIUnitTestCase;

final class SetupKeyTest extends CIUnitTestCase
{
    public function testSetupRequiresMatchingHexKey(): void
    {
        $key = str_repeat('a1', 32);
        $this->assertTrue(SetupKey::valid($key, $key));
        $this->assertFalse(SetupKey::valid($key, str_repeat('b1', 32)));
        $this->assertFalse(SetupKey::valid('', ''));
        $this->assertFalse(SetupKey::valid('short', 'short'));
        $this->assertFalse(SetupKey::valid(str_repeat('z', 64), str_repeat('z', 64)));
    }
}
