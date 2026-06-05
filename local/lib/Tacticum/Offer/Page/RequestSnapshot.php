<?php

namespace Tacticum\Offer\Page;

final class RequestSnapshot
{
    public static function current(): array
    {
        $request = self::bitrixRequest();
        if ($request === null) {
            return self::fromArrays([], [], '', '');
        }

        return self::fromBitrixRequest($request);
    }

    public static function currentRequestUri(): string
    {
        return (string)(self::current()['request_uri'] ?? '');
    }

    public static function fromArrays(
        ?array $request = null,
        ?array $query = null,
        ?string $requestUri = null,
        ?string $queryString = null
    ): array {
        $query = self::normalizeArray($query ?? []);
        $request = self::normalizeArray($request ?? $query);
        $queryString = $queryString ?? http_build_query($query);

        return [
            'request' => $request,
            'query' => $query,
            'request_uri' => $requestUri ?? '',
            'query_string' => $queryString,
        ];
    }

    private static function bitrixRequest(): ?object
    {
        if (!class_exists('\Bitrix\Main\Context')) {
            return null;
        }

        try {
            $context = \Bitrix\Main\Context::getCurrent();
            return is_object($context) && method_exists($context, 'getRequest')
                ? $context->getRequest()
                : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private static function fromBitrixRequest(object $request): array
    {
        $query = method_exists($request, 'getQueryList')
            ? self::dictionaryToArray($request->getQueryList())
            : [];
        $requestValues = method_exists($request, 'toArray')
            ? self::normalizeArray($request->toArray())
            : self::requestValuesFromDictionaries($request, $query);

        return self::fromArrays(
            $requestValues,
            $query,
            self::requestUri($request, $query),
            self::queryString($request, $query)
        );
    }

    private static function requestValuesFromDictionaries(object $request, array $query): array
    {
        $post = method_exists($request, 'getPostList')
            ? self::dictionaryToArray($request->getPostList())
            : [];

        return array_merge($query, $post);
    }

    private static function dictionaryToArray(mixed $dictionary): array
    {
        if (is_object($dictionary) && method_exists($dictionary, 'toArray')) {
            return self::normalizeArray($dictionary->toArray());
        }

        return [];
    }

    private static function requestUri(object $request, array $query): string
    {
        if (method_exists($request, 'getRequestUri')) {
            return (string)$request->getRequestUri();
        }

        $page = method_exists($request, 'getRequestedPage') ? (string)$request->getRequestedPage() : '';
        $queryString = self::queryString($request, $query);

        return $page . ($queryString !== '' ? '?' . $queryString : '');
    }

    private static function queryString(object $request, array $query): string
    {
        if (method_exists($request, 'getQueryString')) {
            return (string)$request->getQueryString();
        }

        return http_build_query($query);
    }

    private static function normalizeArray(array $values): array
    {
        $normalized = [];
        foreach ($values as $key => $value) {
            if (is_string($key) || is_int($key)) {
                $normalized[(string)$key] = $value;
            }
        }

        return $normalized;
    }
}
