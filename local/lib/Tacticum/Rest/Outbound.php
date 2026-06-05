<?php

declare(strict_types=1);

namespace Tacticum\Rest;

final class Outbound
{
    public static function applyCurlDefaults($ch): void
    {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    }

    public static function postJson(string $endpointUrl, array $payload, string $context): array
    {
        $ch = curl_init($endpointUrl);
        self::applyCurlDefaults($ch);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $response = curl_exec($ch);
        $result = [
            'response' => $response,
            'curl_error_no' => curl_errno($ch),
            'curl_error' => curl_error($ch),
            'http_status' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
            'total_time' => curl_getinfo($ch, CURLINFO_TOTAL_TIME),
            'start_transfer_time' => curl_getinfo($ch, CURLINFO_STARTTRANSFER_TIME),
        ];
        curl_close($ch);

        return $result;
    }

    public static function postJsonRetryWithoutGroupId(string $endpointUrl, array $payload, string $context): array
    {
        $result = self::postJson($endpointUrl, $payload, $context);
        $httpStatus = (int)($result['http_status'] ?? 0);
        if ($httpStatus >= 200 && $httpStatus < 300) {
            return $result;
        }
        if ((int)($result['curl_error_no'] ?? 0) !== 0) {
            return $result;
        }
        if (trim((string)($payload['group_id'] ?? '')) === '') {
            return $result;
        }

        $retryPayload = $payload;
        unset($retryPayload['group_id']);
        $retryResult = self::postJson($endpointUrl, $retryPayload, $context . '_without_group_id');
        $retryResult['retried_without_group_id'] = true;
        $retryResult['initial_http_status'] = $httpStatus;

        return $retryResult;
    }

    public static function submitChatAgentSale(
        array $payload,
        string $context,
        ?string $logPrefix = null,
        string $curlErrorMessage = 'Ошибка соединения с внешним сервисом.'
    ): array {
        $baseUrl = Config::requiredHttpsAiUrl('AI_SERVICE_BASE_URL');
        $endpointPath = Config::aiEndpointPath('chat_agent_sale', '/tacticum/v1/chat_agent/sale');
        $endpointUrl = Config::buildUrl($baseUrl, $endpointPath);
        $result = self::postJsonRetryWithoutGroupId($endpointUrl, $payload, $context);
        self::failOnCurlError($result, $context, $curlErrorMessage);

        return $result;
    }

    public static function isSuccessfulUpstreamResponse(array $result): bool
    {
        $httpStatus = (int)($result['http_status'] ?? 0);
        return $httpStatus >= 200 && $httpStatus < 300;
    }

    public static function failChatAgentSaleUpstream(
        array $result,
        string $context,
        string $message = 'Ошибка отправки во внешний сервис.'
    ): void {
        Response::error(502, 'upstream_error', $message);
    }

    public static function failOnCurlError(
        array $result,
        string $context,
        string $message = 'Ошибка соединения с внешним сервисом.'
    ): void {
        $curlErrorNo = (int)($result['curl_error_no'] ?? 0);
        if ($curlErrorNo === 0) {
            return;
        }

        $code = ($curlErrorNo === CURLE_OPERATION_TIMEOUTED) ? 'upstream_timeout' : 'curl_error';
        Response::error(502, $code, $message, [
            'upstream_status' => (int)($result['http_status'] ?? 0),
            'upstream_time' => (float)($result['total_time'] ?? 0),
        ]);
    }
}
