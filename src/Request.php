<?php

declare(strict_types=1);

namespace DiyorbekUz\Telelib;

use DiyorbekUz\Telelib\Exceptions\ApiError;
use DiyorbekUz\Telelib\Exceptions\CurlError;
use DiyorbekUz\Telelib\Exceptions\Error;

/**
 * Class Request
 *
 * @package DiyorbekUz\Telelib
 * @author DiyorbekDev
 */
abstract class Request
{
    public const TIMEOUT_DEFAULT = 10;
    public const CONNECT_TIMEOUT_DEFAULT = 7;

    public int $timeout = self::TIMEOUT_DEFAULT;

    public int $connect_timeout = self::CONNECT_TIMEOUT_DEFAULT;

    public ?array $last_response_info = null;

    protected string $token;

    protected array $params;

    public function __construct(string $token, array $params = [])
    {
        $this->token = $token;
        $this->params = $params;
    }

    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function setConnectTimeout(int $connect_timeout): self
    {
        $this->connect_timeout = $connect_timeout;
        return $this;
    }

    abstract public function sendRequest(int $attempts = 1);

    public function tryToSend(int $attempts = 1): bool
    {
        try {
            $this->request($attempts);
            return true;
        } catch (CurlError $curl_error) {
            return false;
        }
    }

    public function __toString(): string
    {
        return (string)json_encode($this->getRequestData(), JSON_THROW_ON_ERROR);
    }

    public function getRequestData(): array
    {
        $params = self::processingParams($this->params);
        $params['method'] = static::getApiMethodName();
        return $params;
    }

    final private static function processingParams(array $params): array
    {
        $result = [];
        foreach ($params as $param_key => $param_value) {
            if ($param_value === null) {
                continue;
            }

            if ($param_value instanceof Types\TypeInterface) {
                $object_data = self::processingParams($param_value->getRequestArray());
                if (!empty($object_data)) {
                    $result[$param_key] = $object_data;
                }
            } elseif (is_array($param_value)) {
                $array_data = self::processingParams($param_value);
                if (!empty($array_data)) {
                    $result[$param_key] = $array_data;
                }
            } elseif (is_scalar($param_value)) {
                $result[$param_key] = $param_value;
            } else {
                $type = gettype($param_value);
                throw new Error("Param $param_key has unsupported type $type ($param_value)");
            }
        }
        return $result;
    }

    final protected function request(int $attempts = 1)
    {
        $url = "https://api.telegram.org/bot{$this->token}/" . static::getApiMethodName();

        if ($attempts < 1) {
            throw new Error('The number of attempts can not be less than one');
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $this->connect_timeout,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_POSTFIELDS => json_encode($this->getRequestData(), JSON_THROW_ON_ERROR),
            CURLOPT_ENCODING => 'gzip',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
        ]);

        $response = false;
        for ($i = 1; $i <= $attempts; $i++) {
            $response = curl_exec($ch);
            if ($response !== false) {
                break;
            }
        }

        $this->last_response_info = curl_getinfo($ch);
        if ($response === false) {
            throw CurlError::make($ch);
        }

        $response_decoded = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        curl_close($ch);

        if ($response_decoded === null) {
            $response_start = mb_substr($response, 0, 200);
            throw new Exceptions\Error("Result is not JSON: $response_start");
        }

        if ($response_decoded['ok'] !== true) {
            $response_parameters = null;
            if (isset($response_decoded['parameters'])) {
                $response_parameters = new Types\ResponseParameters($response_decoded['parameters']);
            }

            throw new Exceptions\ApiError($response_decoded['error_code'], $response_decoded['description'],
                $response_parameters);
        }

        if ($this->last_response_info['http_code'] !== 200) {
            $error_message = "Incorrect HTTP code: {$this->last_response_info['http_code']} (must be 200)";
            throw new CurlError(CURLE_HTTP_RETURNED_ERROR, $error_message);
        }

        return $response_decoded['result'];
    }
}
