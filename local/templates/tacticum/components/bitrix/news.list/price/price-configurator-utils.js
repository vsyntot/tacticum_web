(() => {
    const ns = window.TacticumPriceConfigurator = window.TacticumPriceConfigurator || {};
    const pluralize = (count, one, few, many) => {
        const abs = Math.abs(count) % 100;
        const last = abs % 10;
        if (abs > 10 && abs < 20) return many;
        if (last === 1) return one;
        if (last > 1 && last < 5) return few;
        return many;
    };

    ns.constants = {
        legacy: {
            card: 'pricing-card',
            tab: 'filter-tab',
            select: 'level-select',
            price: 'price-value',
            prices: 'level-prices-json',
            button: 'order-specialist-btn',
        },
        activeTabClasses: ['bg-primary', 'text-white', 'border-primary', 'hover:bg-primary/90'],
        inactiveTabClasses: ['bg-white', 'border', 'border-gray-200', 'text-gray-700', 'hover:bg-gray-50'],
        workloadMonthlyHours: {
            'part-time': 80,
            'full-time': 160,
        },
        teamPresets: {
            mvp: {
                label: 'MVP',
                roles: [
                    { keywords: ['бизнес-аналит', 'аналитик'] },
                    { keywords: ['ux', 'ui', 'дизайн', 'designer'] },
                    { keywords: ['frontend', 'front-end', 'фронтенд'] },
                    { keywords: ['backend', 'back-end', 'python', 'php', 'java', 'node', 'разработчик', 'developer'] },
                    { keywords: ['qa', 'quality', 'тест', 'тестирование'] },
                ],
            },
            discovery: {
                label: 'Discovery',
                roles: [
                    { keywords: ['бизнес-аналит', 'аналитик'] },
                    { keywords: ['архитектор', 'architect', 'tech lead', 'lead'] },
                    { keywords: ['ux', 'ui', 'дизайн', 'designer'] },
                ],
            },
            support: {
                label: 'Support',
                roles: [
                    { keywords: ['backend', 'back-end', 'python', 'php', 'java', 'node', 'разработчик', 'developer'] },
                    { keywords: ['devops', 'инфраструктура', 'sre'] },
                    { keywords: ['qa', 'quality', 'тест', 'тестирование'] },
                ],
            },
            'qa-burst': {
                label: 'QA burst',
                roles: [
                    { keywords: ['qa', 'quality', 'тест', 'тестирование'], quantity: 2 },
                    { keywords: ['автоматиз', 'automation', 'автотест'] },
                ],
            },
        },
    };

    ns.classSelector = (className) => `.${className}`;
    ns.hasOwn = (object, key) => Object.prototype.hasOwnProperty.call(object, key);
    ns.normalizeText = (value) => String(value || '').toLowerCase().replace(/ё/g, 'е');
    ns.parsePriceNumber = (value) => {
        const normalized = String(value || '')
            .replace(/\s/g, '')
            .replace(',', '.')
            .replace(/[^\d.]/g, '');
        const price = parseFloat(normalized);
        return Number.isFinite(price) ? price : 0;
    };
    ns.formatPrice = (value) => ns.parsePriceNumber(value).toLocaleString('ru-RU', { maximumFractionDigits: 0 });
    ns.pluralizeSpecialist = (count) => pluralize(count, 'специалист', 'специалиста', 'специалистов');
    ns.pluralizeRole = (count) => pluralize(count, 'роль', 'роли', 'ролей');
    ns.pluralizeTechnology = (count) => pluralize(count, 'технология', 'технологии', 'технологий');
    ns.splitSpecialistName = (value) => {
        const full = String(value || '').trim();
        const parts = full.split(';').map((part) => part.trim()).filter(Boolean);
        return {
            full,
            primary: parts[0] || full || 'Специалист',
            extrasCount: Math.max(0, parts.length - 1),
        };
    };
})();
