<?php

declare(strict_types=1);

namespace d4rkstar\arubasms\tests\unit;

use d4rkstar\arubasms\Http;
use d4rkstar\arubasms\SmsStatus;
use PHPUnit\Framework\TestCase;

class HttpTest extends TestCase
{
    private FakeCurl $curl;

    private function createHttp(mixed $response, array $config = []): Http
    {
        $this->curl = new FakeCurl($response);
        $http = new class (array_merge(['user' => 'u', 'pass' => 'p', 'sender' => 'Sender'], $config)) extends Http {
            public ?FakeCurl $fake = null;

            protected function createCurl()
            {
                return $this->fake;
            }
        };
        $http->fake = $this->curl;
        return $http;
    }

    public function testSendSingleSuccess(): void
    {
        $http = $this->createHttp('OK 12345');

        $ok = $http->sendSingle(['rcpt' => '+391234567890', 'data' => 'ciao'], $return);

        $this->assertTrue($ok);
        $this->assertSame(['12345'], $return);
        $this->assertSame($http->urlSingle, $this->curl->capturedUrl);
        $this->assertSame([
            'rcpt' => '+391234567890',
            'data' => 'ciao',
            'user' => 'u',
            'pass' => 'p',
            'return_id' => '1',
            'sender' => 'Sender',
            'qty' => Http::ARUBA_QUALITY_AUTO,
        ], $this->curl->postedFields());
    }

    public function testSendSingleStaticCredentialsOverrideParams(): void
    {
        $http = $this->createHttp('OK 1');

        $http->sendSingle(['rcpt' => '+39123', 'data' => 'x', 'user' => 'other', 'pass' => 'other'], $return);

        $fields = $this->curl->postedFields();
        $this->assertSame('u', $fields['user']);
        $this->assertSame('p', $fields['pass']);
    }

    public function testSendSingleKoReturnsMessage(): void
    {
        $http = $this->createHttp('KO Invalid recipient');

        $ok = $http->sendSingle(['rcpt' => '+39123', 'data' => 'ciao'], $return);

        $this->assertFalse($ok);
        $this->assertSame(['Invalid recipient'], $return);
    }

    public function testSendSingleMissingParamsDoesNotCallCurl(): void
    {
        $http = $this->createHttp('OK 1');

        $ok = $http->sendSingle(['data' => 'ciao'], $return);

        $this->assertFalse($ok);
        $this->assertArrayHasKey('rcpt', $return);
        $this->assertNull($this->curl->capturedUrl);
    }

    public function testSendSingleEmptyParamIsReported(): void
    {
        $http = $this->createHttp('OK 1', ['sender' => '']);

        $ok = $http->sendSingle(['rcpt' => '+39123', 'data' => ''], $return);

        $this->assertFalse($ok);
        $this->assertSame(['data', 'sender'], array_keys($return));
    }

    public function testSendSingleRejectsNonScalarRecipient(): void
    {
        $http = $this->createHttp('OK 1');

        $ok = $http->sendSingle(['rcpt' => ['+39123', '+39456'], 'data' => 'ciao'], $return);

        $this->assertFalse($ok);
        $this->assertArrayHasKey('rcpt', $return);
        $this->assertNull($this->curl->capturedUrl);
    }

    public function testSendSingleHandlesNullResponse(): void
    {
        $http = $this->createHttp(null);

        $ok = $http->sendSingle(['rcpt' => '+39123', 'data' => 'ciao'], $return);

        $this->assertFalse($ok);
        $this->assertNull($return);
    }

    public function testSendSingleLongMessageUsesMultitext(): void
    {
        $http = $this->createHttp('OK 1');

        $http->sendSingle(['rcpt' => '+39123', 'data' => str_repeat('a', 1000)], $return);

        $fields = $this->curl->postedFields();
        $this->assertSame('1', $fields[Http::ARUBA_OPERATION_MULTITEXT]);
        $this->assertSame(Http::ARUBA_SMS_LENGTH_MULTITEXT, strlen($fields['data']));
    }

    public function testSendSingleShortMessageDoesNotUseMultitext(): void
    {
        $http = $this->createHttp('OK 1');

        $http->sendSingle(['rcpt' => '+39123', 'data' => str_repeat('a', Http::ARUBA_SMS_LENGTH_SINGLE)], $return);

        $this->assertArrayNotHasKey(Http::ARUBA_OPERATION_MULTITEXT, $this->curl->postedFields());
    }

    public function testSendSingleExplicitQualityAndSender(): void
    {
        $http = $this->createHttp('OK 1');

        $http->sendSingle([
            'rcpt' => '+39123',
            'data' => 'ciao',
            'sender' => 'Custom',
            'qty' => Http::ARUBA_QUALITY_HIGH,
        ], $return);

        $fields = $this->curl->postedFields();
        $this->assertSame('Custom', $fields['sender']);
        $this->assertSame(Http::ARUBA_QUALITY_HIGH, $fields['qty']);
    }

    public function testSendMultipleJoinsRecipients(): void
    {
        $http = $this->createHttp('OK 999');

        $ok = $http->sendMultiple(['rcpt' => ['+39123', '+39456'], 'data' => 'ciao'], $return);

        $this->assertTrue($ok);
        $this->assertSame(['999'], $return);
        $this->assertSame($http->urlBatch, $this->curl->capturedUrl);
        $this->assertSame('+39123,+39456', $this->curl->postedFields()['rcpt']);
    }

    public function testSendMultipleAcceptsCommaSeparatedString(): void
    {
        $http = $this->createHttp('OK 1');

        $http->sendMultiple(['rcpt' => '+39123,+39456', 'data' => 'ciao'], $return);

        $this->assertSame('+39123,+39456', $this->curl->postedFields()['rcpt']);
    }

    public function testSendMultipleMissingParamsDoesNotCallCurl(): void
    {
        $http = $this->createHttp('OK 1');

        $ok = $http->sendMultiple(['data' => 'ciao'], $return);

        $this->assertFalse($ok);
        $this->assertArrayHasKey('rcpt', $return);
        $this->assertNull($this->curl->capturedUrl);
    }

    public function testCheckStatusParsesReport(): void
    {
        $report = "OK\n"
            . "1,2026-11-16 10:00:00,+39123,DELIVERED,Consegnato\n"
            . "2,2026-11-16 10:00:01,+39456,ERROR,Errore\n"
            . "malformed line\n";
        $http = $this->createHttp($report);

        $ok = $http->checkStatus(['id' => '42'], $return);

        $this->assertTrue($ok);
        $this->assertSame($http->urlBatchStatus, $this->curl->capturedUrl);
        $this->assertSame([
            'id' => '42',
            'user' => 'u',
            'pass' => 'p',
            'schema' => '1',
            'type' => Http::ARUBA_REPORT_TYPE_QUEUE,
        ], $this->curl->postedFields());

        $this->assertCount(1, $return);
        $statuses = $return[0];
        $this->assertCount(2, $statuses);
        $this->assertContainsOnlyInstancesOf(SmsStatus::class, $statuses);
        $this->assertSame('1', $statuses[0]->id);
        $this->assertSame('2026-11-16 10:00:00', $statuses[0]->timestamp);
        $this->assertSame('+39123', $statuses[0]->dest);
        $this->assertSame('DELIVERED', $statuses[0]->status);
        $this->assertSame('Consegnato', $statuses[0]->status_text);
        $this->assertSame('ERROR', $statuses[1]->status);
    }

    public function testCheckStatusKoReturnsMessage(): void
    {
        $http = $this->createHttp('KO Unknown id');

        $ok = $http->checkStatus(['id' => '42', 'type' => Http::ARUBA_REPORT_TYPE_NOTIFY], $return);

        $this->assertFalse($ok);
        $this->assertSame(['Unknown id'], $return);
        $this->assertSame(Http::ARUBA_REPORT_TYPE_NOTIFY, $this->curl->postedFields()['type']);
    }

    public function testCheckStatusMissingIdDoesNotCallCurl(): void
    {
        $http = $this->createHttp('OK');

        $ok = $http->checkStatus([], $return);

        $this->assertFalse($ok);
        $this->assertArrayHasKey('id', $return);
        $this->assertNull($this->curl->capturedUrl);
    }

    public function testCheckStatusHandlesNullResponse(): void
    {
        $http = $this->createHttp(null);

        $ok = $http->checkStatus(['id' => '42'], $return);

        $this->assertTrue($ok);
        $this->assertSame([[]], $return);
    }

    public function testCheckCreditSendsDefaultCreditType(): void
    {
        $http = $this->createHttp('OK 150');

        $ok = $http->checkCredit([], $return);

        $this->assertTrue($ok);
        $this->assertSame(['150'], $return);
        $this->assertSame($http->urlCredit, $this->curl->capturedUrl);
        $this->assertSame([
            'user' => 'u',
            'pass' => 'p',
            'type' => Http::ARUBA_CREDIT_TYPE_CREDIT,
        ], $this->curl->postedFields());
    }

    public function testCheckCreditExplicitTypeWins(): void
    {
        $http = $this->createHttp('OK 10');

        $http->checkCredit(['type' => Http::ARUBA_CREDIT_TYPE_HIGH], $return);

        $this->assertSame(Http::ARUBA_CREDIT_TYPE_HIGH, $this->curl->postedFields()['type']);
    }

    public function testCheckCreditMissingCredentials(): void
    {
        $http = $this->createHttp('OK 10', ['user' => '', 'pass' => '']);

        $ok = $http->checkCredit([], $return);

        $this->assertFalse($ok);
        $this->assertSame(['user', 'pass'], array_keys($return));
        $this->assertNull($this->curl->capturedUrl);
    }

    public function testCheckCreditHandlesNullResponse(): void
    {
        $http = $this->createHttp(null);

        $this->assertFalse($http->checkCredit([], $return));
    }

    public function testReturnArgumentIsOptional(): void
    {
        $http = $this->createHttp('OK 10');

        $this->assertTrue($http->checkCredit());
    }
}
