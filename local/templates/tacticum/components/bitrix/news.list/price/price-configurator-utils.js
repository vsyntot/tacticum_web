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
        teamPresets: {},
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
    ns.readTeamPresets = (root) => {
        const script = root?.querySelector('[data-price-team-presets-json]');
        if (!script) return {};

        try {
            const parsed = JSON.parse(script.textContent || '{}');
            const presets = Array.isArray(parsed?.presets) ? parsed.presets : [];
            return presets.reduce((indexed, preset) => {
                const code = String(preset?.code || '').trim();
                if (code) indexed[code] = preset;
                return indexed;
            }, {});
        } catch (error) {
            return {};
        }
    };
})();
