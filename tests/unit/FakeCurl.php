<?php

declare(strict_types=1);

namespace d4rkstar\arubasms\tests\unit;

use linslin\yii2\curl\Curl;

class FakeCurl extends Curl
{
    public mixed $fakeResponse = null;

    public array $capturedOptions = [];

    public ?string $capturedUrl = null;

    public function __construct(mixed $fakeResponse = null)
    {
        $this->fakeResponse = $fakeResponse;
    }

    public function setOption($key, $value)
    {
        $this->capturedOptions[$key] = $value;
        return $this;
    }

    public function post($url, $raw = true)
    {
        $this->capturedUrl = $url;
        return $this->fakeResponse;
    }

    public function postedFields(): array
    {
        parse_str((string) ($this->capturedOptions[CURLOPT_POSTFIELDS] ?? ''), $fields);
        return $fields;
    }
}
