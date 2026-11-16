<?php

declare(strict_types=1);

namespace d4rkstar\arubasms\tests\unit;

use d4rkstar\arubasms\SmsStatus;
use PHPUnit\Framework\TestCase;

class SmsStatusTest extends TestCase
{
    public function testMassiveAssignment(): void
    {
        $status = new SmsStatus();
        $status->attributes = [
            'id' => '1',
            'timestamp' => '2026-11-16 10:00:00',
            'dest' => '+39123',
            'status' => 'DELIVERED',
            'status_text' => 'Consegnato',
            'unknown' => 'ignored',
        ];

        $this->assertSame([
            'id' => '1',
            'timestamp' => '2026-11-16 10:00:00',
            'dest' => '+39123',
            'status' => 'DELIVERED',
            'status_text' => 'Consegnato',
        ], $status->getAttributes());
        $this->assertTrue($status->validate());
    }

    public function testAttributeLabels(): void
    {
        $status = new SmsStatus();

        $this->assertSame('Status Text', $status->getAttributeLabel('status_text'));
        $this->assertSame(
            ['id', 'timestamp', 'dest', 'status', 'status_text'],
            array_keys($status->attributeLabels())
        );
    }
}
