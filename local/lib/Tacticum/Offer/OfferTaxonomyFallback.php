<?php

declare(strict_types=1);

namespace Tacticum\Offer;

final class OfferTaxonomyFallback
{
    public static function terms(): array
    {
        return [
            ['dimension' => 'sector', 'code' => 'meditsina', 'publicLabel' => 'медицина', 'shortLabel' => 'медицина', 'aliases' => ['медицина', 'meditsina'], 'sort' => 100, 'active' => true, 'featured' => true, 'productFamily' => ''],
            ['dimension' => 'sector', 'code' => 'riteyl', 'publicLabel' => 'ритейл', 'shortLabel' => 'ритейл', 'aliases' => ['ритейл', 'riteyl'], 'sort' => 200, 'active' => true, 'featured' => true, 'productFamily' => ''],
            ['dimension' => 'sector', 'code' => 'proizvodstvo', 'publicLabel' => 'производство', 'shortLabel' => 'производство', 'aliases' => ['производство', 'proizvodstvo'], 'sort' => 300, 'active' => true, 'featured' => true, 'productFamily' => ''],
            ['dimension' => 'sector', 'code' => 'finansy', 'publicLabel' => 'финансы', 'shortLabel' => 'финансы', 'aliases' => ['финансы', 'finansy'], 'sort' => 400, 'active' => true, 'featured' => true, 'productFamily' => ''],
            ['dimension' => 'sector', 'code' => 'logistika', 'publicLabel' => 'логистика', 'shortLabel' => 'логистика', 'aliases' => ['логистика', 'logistika'], 'sort' => 500, 'active' => true, 'featured' => true, 'productFamily' => ''],
            ['dimension' => 'sector', 'code' => 'e-commerce', 'publicLabel' => 'онлайн-торговля', 'shortLabel' => 'онлайн-торговля', 'aliases' => ['e-commerce', 'онлайн-торговля', 'электронная торговля'], 'sort' => 600, 'active' => true, 'featured' => true, 'productFamily' => ''],
            ['dimension' => 'sector', 'code' => 'nedvizhimost', 'publicLabel' => 'недвижимость', 'shortLabel' => 'недвижимость', 'aliases' => ['недвижимость', 'nedvizhimost'], 'sort' => 700, 'active' => true, 'featured' => true, 'productFamily' => ''],
            ['dimension' => 'sector', 'code' => 'obrazovanie', 'publicLabel' => 'образование', 'shortLabel' => 'образование', 'aliases' => ['образование', 'obrazovanie'], 'sort' => 800, 'active' => true, 'featured' => true, 'productFamily' => ''],
            ['dimension' => 'sector', 'code' => 'beauty', 'publicLabel' => 'бьюти и салоны', 'shortLabel' => 'бьюти', 'aliases' => ['beauty', 'бьюти', 'салоны красоты'], 'sort' => 900, 'active' => true, 'featured' => false, 'productFamily' => ''],
            ['dimension' => 'scenario', 'code' => 'ai-assistent-podderzhki', 'publicLabel' => 'AI-ассистент поддержки', 'shortLabel' => 'AI-поддержка', 'aliases' => ['AI-ассистент поддержки', 'ai-assistent-podderzhki'], 'sort' => 100, 'active' => true, 'featured' => true, 'productFamily' => ''],
            ['dimension' => 'scenario', 'code' => 'ai-kopaylot-dlya-sotrudnikov', 'publicLabel' => 'AI-копайлот для сотрудников', 'shortLabel' => 'AI-копайлот', 'aliases' => ['AI-копайлот для сотрудников', 'ai-kopaylot-dlya-sotrudnikov'], 'sort' => 200, 'active' => true, 'featured' => true, 'productFamily' => ''],
            ['dimension' => 'scenario', 'code' => 'ai-poisk-po-korporativnym-znaniyam', 'publicLabel' => 'AI-поиск по корпоративным знаниям', 'shortLabel' => 'AI-поиск', 'aliases' => ['AI-поиск по корпоративным знаниям', 'ai-poisk-po-korporativnym-znaniyam'], 'sort' => 300, 'active' => true, 'featured' => true, 'productFamily' => ''],
            ['dimension' => 'scenario', 'code' => 'rpa-i-dokumentooborot', 'publicLabel' => 'RPA и документооборот', 'shortLabel' => 'RPA', 'aliases' => ['RPA и документооборот', 'rpa-i-dokumentooborot'], 'sort' => 400, 'active' => true, 'featured' => true, 'productFamily' => ''],
            ['dimension' => 'scenario', 'code' => 'bi-i-upravlencheskaya-analitika', 'publicLabel' => 'BI и управленческая аналитика', 'shortLabel' => 'BI-аналитика', 'aliases' => ['BI и управленческая аналитика', 'bi-i-upravlencheskaya-analitika'], 'sort' => 500, 'active' => true, 'featured' => true, 'productFamily' => ''],
            ['dimension' => 'scenario', 'code' => 'prognozirovanie-sprosa', 'publicLabel' => 'прогнозирование спроса', 'shortLabel' => 'прогноз спроса', 'aliases' => ['прогнозирование спроса', 'prognozirovanie-sprosa'], 'sort' => 600, 'active' => true, 'featured' => true, 'productFamily' => ''],
            ['dimension' => 'scenario', 'code' => 'integratsionnaya-shina-i-api', 'publicLabel' => 'интеграционная шина и API', 'shortLabel' => 'интеграции', 'aliases' => ['интеграционная шина и API', 'integratsionnaya-shina-i-api'], 'sort' => 700, 'active' => true, 'featured' => true, 'productFamily' => ''],
            ['dimension' => 'scenario', 'code' => 'prediktivnaya-analitika-oborudovaniya', 'publicLabel' => 'предиктивная аналитика оборудования', 'shortLabel' => 'предиктивная аналитика', 'aliases' => ['предиктивная аналитика оборудования', 'prediktivnaya-analitika-oborudovaniya'], 'sort' => 800, 'active' => true, 'featured' => true, 'productFamily' => ''],
            ['dimension' => 'scenario', 'code' => 'data-platform-i-mlops', 'publicLabel' => 'Платформа данных и MLOps', 'shortLabel' => 'данные и MLOps', 'aliases' => ['data platform и MLOps', 'Платформа данных и MLOps', 'data-platform-i-mlops'], 'sort' => 900, 'active' => true, 'featured' => false, 'productFamily' => ''],
            ['dimension' => 'scenario', 'code' => 'voice-analytics-i-kontrol-kachestva', 'publicLabel' => 'Голосовая аналитика и контроль качества', 'shortLabel' => 'голосовая аналитика', 'aliases' => ['voice analytics и контроль качества', 'Голосовая аналитика и контроль качества', 'voice-analytics-i-kontrol-kachestva'], 'sort' => 1000, 'active' => true, 'featured' => false, 'productFamily' => ''],
            ['dimension' => 'phase', 'code' => 'discovery', 'publicLabel' => 'обследование и постановка задачи', 'shortLabel' => 'обследование', 'aliases' => ['discovery', 'обследование', 'постановка задачи'], 'sort' => 100, 'active' => true, 'featured' => false, 'productFamily' => ''],
            ['dimension' => 'phase', 'code' => 'pilot', 'publicLabel' => 'пилотный запуск', 'shortLabel' => 'пилот', 'aliases' => ['pilot', 'пилот', 'пилотный запуск'], 'sort' => 200, 'active' => true, 'featured' => false, 'productFamily' => ''],
            ['dimension' => 'phase', 'code' => 'production-vnedrenie', 'publicLabel' => 'внедрение в рабочую эксплуатацию', 'shortLabel' => 'рабочая эксплуатация', 'aliases' => ['production-внедрение', 'production-vnedrenie', 'внедрение в рабочую эксплуатацию'], 'sort' => 300, 'active' => true, 'featured' => false, 'productFamily' => ''],
        ];
    }
}
