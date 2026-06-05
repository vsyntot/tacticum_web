<?php

namespace Tacticum\Product\Page;

final class Schema
{
    public static function software(
        array $page,
        string $canonicalPath,
        string $applicationCategory = 'BusinessApplication',
        string $description = ''
    ): array {
        $path = Text::canonicalPath($canonicalPath);
        $name = Text::string($page, 'eyebrow');
        if ($name === '') {
            $name = Text::string($page, 'title', 'Tacticum product');
        }

        $schemaDescription = Text::schemaText($description);
        if ($schemaDescription === '') {
            $schemaDescription = Text::schemaText(Text::string($page, 'lead'));
        }

        return [
            '@type' => 'SoftwareApplication',
            '@id' => tacticum_public_url($path . '#software'),
            'name' => $name,
            'applicationCategory' => $applicationCategory !== '' ? $applicationCategory : 'BusinessApplication',
            'operatingSystem' => 'Web',
            'url' => tacticum_public_url($path),
            'description' => $schemaDescription,
            'provider' => [
                '@id' => tacticum_public_url('/#organization'),
            ],
            'isPartOf' => [
                '@id' => tacticum_public_url('/#website'),
            ],
        ];
    }

    public static function faq(array $page, string $canonicalPath): ?array
    {
        $faq = is_array($page['faq'] ?? null) ? $page['faq'] : [];
        $items = is_array($faq['items'] ?? null) ? $faq['items'] : [];
        if (empty($items)) {
            return null;
        }

        $entities = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $question = Text::schemaText($item['question'] ?? '');
            $answer = Text::schemaText($item['answer'] ?? '');
            if ($question === '' || $answer === '') {
                continue;
            }

            $entities[] = [
                '@type' => 'Question',
                'name' => $question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $answer,
                ],
            ];
        }

        if (empty($entities)) {
            return null;
        }

        return [
            '@type' => 'FAQPage',
            '@id' => tacticum_public_url(Text::canonicalPath($canonicalPath) . '#faq'),
            'mainEntity' => $entities,
        ];
    }

    public static function build(
        array $page,
        string $canonicalPath,
        string $applicationCategory = 'BusinessApplication',
        string $description = ''
    ): array {
        $schema = [
            self::software($page, $canonicalPath, $applicationCategory, $description),
        ];
        $faqSchema = self::faq($page, $canonicalPath);
        if ($faqSchema !== null) {
            $schema[] = $faqSchema;
        }

        return $schema;
    }
}
