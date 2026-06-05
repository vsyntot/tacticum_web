<?php

use Tacticum\CalcRequests\Access;
use Tacticum\CalcRequests\CodeGenerator;
use Tacticum\CalcRequests\Registrar;
use Tacticum\CalcRequests\Response;
use Tacticum\CalcRequests\Runtime;
use Tacticum\CalcRequests\Service;
use Tacticum\CalcRequests\Validator;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

function tacticum_calcrequests_build_error(string $code, string $message, array $extra = []): array
{
    return Response::error($code, $message, $extra);
}

function tacticum_calcrequests_build_success(array $extra = []): array
{
    return Response::success($extra);
}

function tacticum_calcrequests_get_config(): array
{
    return Runtime::config();
}

function tacticum_calcrequests_get_offer_iblock_id(): int
{
    return Runtime::offerIblockId();
}

function tacticum_calcrequests_user_has_access(int $iblockId): bool
{
    return Access::userHasAccess($iblockId);
}

function tacticum_calcrequests_check_access(array $params, int $iblockId = 0): ?array
{
    return Access::check($params, $iblockId);
}

function tacticum_calcrequests_validate_payload(array $params): array
{
    return Validator::validate($params);
}

function tacticum_calcrequests_is_code_unique(string $code, int $iblockId): bool
{
    return CodeGenerator::isUnique($code, $iblockId);
}

function tacticum_calcrequests_normalize_code(string $raw, int $iblockId, int $maxLength = 100): string
{
    return CodeGenerator::normalize($raw, $iblockId, $maxLength);
}

function tacticum_calcrequests_build_offer_code(array $params, int $iblockId, int $maxLength = 100): string
{
    return CodeGenerator::offerCode($params, $iblockId, $maxLength);
}

function tacticum_calcrequests_list(array $params): array
{
    return Service::list($params);
}

function tacticum_calcrequests_add(array $params): array
{
    return Service::add($params);
}

function tacticum_register_calcrequests_rest(): void
{
    Registrar::register();
}
