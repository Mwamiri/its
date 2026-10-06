<?php

use App\Libraries\Sla;
use App\Libraries\Totp;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class EnterpriseTest extends CIUnitTestCase
{
    public function testTotpMatchesRfc6238Vectors(): void
    {
        $secret = Totp::b32encode('12345678901234567890');
        $this->assertSame('287082', Totp::code($secret, 59));
        $this->assertSame('081804', Totp::code($secret, 1111111109));
        $this->assertTrue(Totp::verify($secret, Totp::code($secret)));
        $this->assertFalse(Totp::verify($secret, '000000x'));
    }

    public function testRecoveryCodeIsSingleUse(): void
    {
        [$plain, $json] = Totp::newRecoveryCodes(3);
        $left = Totp::useRecovery($json, $plain[0]);
        $this->assertNotNull($left);
        $this->assertNull(Totp::useRecovery($left, $plain[0]));
    }

    public function testSlaStates(): void
    {
        $now = time();
        $this->assertSame('none', Sla::state(['status' => 'new']));
        $this->assertSame('breached', Sla::state(['status' => 'new', 'due_at' => date('Y-m-d H:i:s', $now - 60), 'created_at' => date('Y-m-d H:i:s', $now - 3600)]));
        $this->assertSame('ok', Sla::state(['status' => 'new', 'due_at' => date('Y-m-d H:i:s', $now + 7200), 'created_at' => date('Y-m-d H:i:s', $now - 60)]));
        $this->assertSame('met', Sla::state(['status' => 'completed', 'due_at' => date('Y-m-d H:i:s', $now + 3600), 'resolved_at' => date('Y-m-d H:i:s', $now), 'created_at' => date('Y-m-d H:i:s', $now - 600)]));
    }
}