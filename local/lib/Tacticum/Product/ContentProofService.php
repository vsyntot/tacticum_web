<?php

namespace Tacticum\Product;

final class ContentProofService
{
    public static function applyPublicProof(array &$page, int $productId): void
    {
        $page['_proof_source'] = self::proofSource($page['proof'] ?? null);
        $publicProofItems = ContentProofRepository::fetchProductProof(self::proofIblockIds(), $productId);
        if (count($publicProofItems) >= 3) {
            $page['proof'] = [
                'source' => 'iblock',
                'eyebrow' => 'Доказательства',
                'title' => 'Связанные кейсы и отзывы',
                'text' => 'Показываем только материалы с продуктовой привязкой и отдельным согласованием на публикацию.',
                'items' => $publicProofItems,
            ];
            $page['_proof_source'] = 'iblock';
            return;
        }

        if (is_array($page['proof'] ?? null)) {
            $page['proof']['source'] = $page['_proof_source'];
        }
    }

    private static function proofIblockIds(): array
    {
        $ids = [];
        foreach (['cases', 'feedback', 'clients'] as $proofKey) {
            $ids[$proofKey] = function_exists('tacticum_rest_get_iblock_id')
                ? tacticum_rest_get_iblock_id($proofKey)
                : 0;
        }

        return $ids;
    }

    private static function proofSource(mixed $proof): string
    {
        if (!is_array($proof)) {
            return 'missing';
        }

        $source = ContentMapper::propertyScalar($proof, 'source');
        if (in_array($source, ['iblock', 'readiness'], true)) {
            return $source;
        }

        return !empty($proof['items']) ? 'readiness' : 'missing';
    }
}
