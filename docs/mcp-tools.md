# MCP Tools & Skills — tacticum.ru

Анализ и выбор MCP-серверов (Model Context Protocol) для команды AI-агентов.  
MCP — стандарт подключения инструментов к LLM-агентам (Claude, Copilot и др.).

---

## ✅ Выбранные MCP-серверы

### 0. `figma` (Figma MCP Server)
**Кто использует:** Designer  
**Зачем:** Работа с макетами, компонентами и дизайн-токенами Figma напрямую из агента

| Возможность | Использование в проекте |
|---|---|
| Чтение макетов и компонентов | Designer читает текущие экраны tacticum.ru в Figma |
| Получение дизайн-токенов | Цвета, отступы, типографика → Tailwind-классы |
| Аннотирование макетов | Designer оставляет комментарии / handoff для Frontend Dev |

**Установка (Claude Desktop):**
```json
{
  "mcpServers": {
    "figma": {
      "command": "npx",
      "args": ["-y", "figma-developer-mcp", "--figma-api-key", "<YOUR_FIGMA_API_KEY>"],
      "env": {
        "FIGMA_API_KEY": "<your_figma_api_key>"
      }
    }
  }
}
```

> **Figma API Key** — создать на https://www.figma.com/settings → Personal access tokens

---

### 1. `github/github-mcp-server`
**Кто использует:** PM, Architect, QA/Reviewer  
**Зачем:** Работа с GitHub напрямую из агента без браузера

| Возможность | Использование в проекте |
|---|---|
| Создание/обновление Issues | PM создаёт задачи по шаблонам из `.github/ISSUE_TEMPLATE/` |
| Управление Projects (Kanban) | PM обновляет статусы: Backlog → In Progress → Review → Done |
| Чтение PR и оставление комментариев | QA/Reviewer ревьюит по чеклисту из `qa-reviewer.md` |
| Создание веток | Backend/Frontend Dev стартует ветку `feature/N-slug` |
| Merge / Close | PM закрывает Issue после деплоя |

**Установка (Claude Desktop `~/.claude/config.json`):**
```json
{
  "mcpServers": {
    "github": {
      "command": "docker",
      "args": ["run", "-i", "--rm", "-e", "GITHUB_PERSONAL_ACCESS_TOKEN", "-e", "GITHUB_TOOLSETS=default,actions,code_security", "ghcr.io/github/github-mcp-server"],
      "env": {
        "GITHUB_PERSONAL_ACCESS_TOKEN": "<your_token>"
      }
    }
  }
}
```

> Используем официальный GitHub MCP Server. Старый пакет `@modelcontextprotocol/server-github` не добавлять в новые конфиги.

---

### 2. `@modelcontextprotocol/server-filesystem`
**Кто использует:** Backend Dev, Frontend Dev, Architect  
**Зачем:** Чтение и запись файлов проекта напрямую из агента

| Возможность | Использование в проекте |
|---|---|
| Чтение файлов | Агент читает `tacticum_form.php` перед созданием нового эндпоинта |
| Создание файлов | Backend Dev создаёт `local/rest/tacticum_XXX.php` |
| Редактирование | Frontend Dev правит `header.php`, `styles/*.css` |
| Листинг директорий | Architect изучает структуру перед декомпозицией |

**Установка:**
```json
{
  "mcpServers": {
    "filesystem": {
      "command": "npx",
      "args": [
        "-y",
        "@modelcontextprotocol/server-filesystem",
        "/Users/ivanmonakhov/PhpstormProjects/tacticum_web"
      ]
    }
  }
}
```

> ⚠️ Ограничить путь только корнем проекта — агент не должен читать за его пределами.

---

### 3. `@modelcontextprotocol/server-fetch`
**Кто использует:** SEO Agent, QA/Reviewer  
**Зачем:** Проверка живых страниц сайта и API-эндпоинтов

| Возможность | Использование в проекте |
|---|---|
| GET-запросы к страницам | SEO проверяет мета-теги, h1, наличие в sitemap |
| GET к API-эндпоинтам | QA проверяет `/local/api/cases.php`, `/local/api/faq.php` |
| Проверка robots.txt | SEO валидирует директивы |
| Проверка sitemap.xml | SEO проверяет актуальность URL |

**Установка:**
```json
{
  "mcpServers": {
    "fetch": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-fetch"]
    }
  }
}
```

---

### 4. `@playwright/mcp` (Playwright MCP Server)
**Кто использует:** Frontend Dev, Designer  
**Зачем:** Управление реальным браузером — клики, заполнение форм, скриншоты, проверка UI

| Возможность | Использование в проекте |
|---|---|
| Открытие страниц в браузере | Frontend Dev проверяет вёрстку на `https://tacticum.ru` после изменений |
| Клики и заполнение форм | Frontend Dev тестирует отправку формы `data-tacticum-form` end-to-end |
| Скриншоты страниц и блоков | Designer делает снимки текущего UI для сравнения с макетом |
| Проверка адаптивности | Designer проверяет мобильный вид (viewport 375px) |
| Визуальный регресс | QA сравнивает UI до и после правок |
| Отладка JS-ошибок | Frontend Dev видит console.log и ошибки прямо в агенте |

**Установка:**
```json
{
  "mcpServers": {
    "playwright": {
      "command": "npx",
      "args": ["-y", "@playwright/mcp"]
    }
  }
}
```

> ⚠️ Playwright требует установленных браузеров: `npx playwright install chromium`

---

### 5. `@modelcontextprotocol/server-sequential-thinking`
**Кто использует:** Architect, PM  
**Зачем:** Сложные многошаговые задачи — декомпозиция фич, написание ADR

| Возможность | Использование в проекте |
|---|---|
| Пошаговое рассуждение | Architect анализирует задачу перед написанием ADR |
| Планирование спринта | PM оценивает объём и расставляет приоритеты |
| Декомпозиция | Разбивка крупной фичи на подзадачи для разных агентов |

**Установка:**
```json
{
  "mcpServers": {
    "sequential-thinking": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-sequential-thinking"]
    }
  }
}
```

---

### 6. `mcp-server-git`
**Кто использует:** Architect, Backend Dev, Frontend Dev, QA/Reviewer, SEO  
**Зачем:** Локальная история Git, диффы, статус рабочей директории, ревью изменений до PR

| Возможность | Использование в проекте |
|---|---|
| `git_status` | Проверить, какие файлы изменены агентом и нет ли случайных правок |
| `git_diff_unstaged` / `git_diff_staged` | QA и разработчики смотрят точный дифф перед PR |
| `git_log` / `git_show` | Architect проверяет историю решений и контекст прошлых изменений |
| `git_branch` | PM/Dev проверяют ветку перед созданием задачи или PR |

**Установка:**
```json
{
  "mcpServers": {
    "git": {
      "command": "uvx",
      "args": ["mcp-server-git", "--repository", "/Users/ivanmonakhov/PhpstormProjects/tacticum_web"]
    }
  }
}
```

> Использовать для чтения статуса/диффов и обычных коммитов. Не выполнять destructive-операции (`reset`, `checkout` с потерей правок) без явного подтверждения владельца.

---

### 7. `@modelcontextprotocol/server-memory`
**Кто использует:** PM, Analyst, Designer, Architect, QA/Reviewer, SEO  
**Зачем:** Проектная память между сессиями: устойчивые решения, ограничения, договорённости, повторяющиеся проблемы

| Возможность | Использование в проекте |
|---|---|
| Хранение фактов проекта | PM и Analyst не теряют договорённости по требованиям |
| Архитектурный контекст | Architect помнит принятые паттерны до оформления/после оформления ADR |
| Дизайн-решения | Designer хранит токены, ограничения макетов, ссылки на актуальные Figma-файлы |
| Повторяющиеся дефекты | QA фиксирует типовые нарушения чеклистов и ссылки на эталоны |

**Установка:**
```json
{
  "mcpServers": {
    "memory": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-memory"],
      "env": {
        "MEMORY_FILE_PATH": "/Users/ivanmonakhov/PhpstormProjects/tacticum_web/.mcp/memory.jsonl"
      }
    }
  }
}
```

> `.mcp/` исключён из git. Не сохранять в memory токены, пароли, приватные ключи, PII клиентов и содержимое `tacticum_config.php`.

---

### 8. `mcp-server-time`
**Кто использует:** PM, DevOps, SEO  
**Зачем:** Точные даты и часовые пояса для спринтов, релизов, sitemap `lastmod`, отчётов и деплой-окон

| Возможность | Использование в проекте |
|---|---|
| Текущее время | PM формирует спринты и еженедельные отчёты с корректной датой |
| Конвертация часовых поясов | DevOps планирует деплой-окна и релизные коммуникации |
| Даты sitemap | SEO ставит корректные `lastmod` без ручных ошибок |

**Установка:**
```json
{
  "mcpServers": {
    "time": {
      "command": "uvx",
      "args": ["mcp-server-time", "--local-timezone=Europe/Moscow"]
    }
  }
}
```

---

## 🔄 Матрица: агент → MCP-инструменты

| Агент | filesystem | github | git | fetch | sequential-thinking | memory | time | figma | playwright |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| **PM** | — | ✅ | — | — | ✅ | ✅ | ✅ | — | — |
| **Analyst** | ✅ | ✅ | — | ✅ | ✅ | ✅ | — | — | — |
| **Designer** | ✅ | ✅ | — | ✅ | — | ✅ | — | ✅ | ✅ |
| **Architect** | ✅ | ✅ | ✅ | — | ✅ | ✅ | — | — | — |
| **Backend Dev** | ✅ | ✅ | ✅ | — | — | — | — | — | — |
| **Frontend Dev** | ✅ | ✅ | ✅ | — | — | — | — | — | ✅ |
| **QA/Reviewer** | ✅ | ✅ | ✅ | ✅ | — | ✅ | — | — | ✅ |
| **DevOps** | — | ✅ | — | — | — | — | ✅ | — | — |
| **SEO** | ✅ | ✅ | ✅ | ✅ | — | ✅ | ✅ | — | — |

---

## ⏳ Рассмотренные, но не выбранные

| MCP-сервер | Почему не выбран |
|---|---|
| `server-postgres` / `server-mysql` | Bitrix работает с БД через ORM/API — прямой доступ к БД из агента небезопасен |
| `server-slack` | Команда общается через другие каналы (Telegram) |
| `server-puppeteer` | Заменён на `@playwright/mcp` — Playwright мощнее и активно развивается |
| `server-sentry` | Не выбран до появления Sentry в production-контуре проекта |
| `server-redis` | Прямой доступ к кешу Bitrix агентам не нужен; очистка кеша выполняется workflow деплоя |

---

## Установка всех выбранных MCP (одним блоком)

Полный `~/.claude/config.json` для Claude Desktop:

```json
{
  "mcpServers": {
    "figma": {
      "command": "npx",
      "args": ["-y", "figma-developer-mcp", "--figma-api-key", "<YOUR_FIGMA_API_KEY>"]
    },
    "github": {
      "command": "docker",
      "args": ["run", "-i", "--rm", "-e", "GITHUB_PERSONAL_ACCESS_TOKEN", "-e", "GITHUB_TOOLSETS=default,actions,code_security", "ghcr.io/github/github-mcp-server"],
      "env": {
        "GITHUB_PERSONAL_ACCESS_TOKEN": "<your_github_pat>"
      }
    },
    "filesystem": {
      "command": "npx",
      "args": [
        "-y",
        "@modelcontextprotocol/server-filesystem",
        "/Users/ivanmonakhov/PhpstormProjects/tacticum_web"
      ]
    },
    "fetch": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-fetch"]
    },
    "sequential-thinking": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-sequential-thinking"]
    },
    "git": {
      "command": "uvx",
      "args": ["mcp-server-git", "--repository", "/Users/ivanmonakhov/PhpstormProjects/tacticum_web"]
    },
    "memory": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-memory"],
      "env": {
        "MEMORY_FILE_PATH": "/Users/ivanmonakhov/PhpstormProjects/tacticum_web/.mcp/memory.jsonl"
      }
    },
    "time": {
      "command": "uvx",
      "args": ["mcp-server-time", "--local-timezone=Europe/Moscow"]
    },
    "playwright": {
      "command": "npx",
      "args": ["-y", "@playwright/mcp"]
    }
  }
}
```

> **GitHub PAT** — создать на https://github.com/settings/tokens, права: `repo`, `project`  
> **Figma API Key** — создать на https://www.figma.com/settings → Personal access tokens  
> **uv/uvx** — нужен для `mcp-server-git` и `mcp-server-time`  
> **Docker** — нужен для официального `github/github-mcp-server`  
> **Playwright браузеры** — установить один раз: `npx playwright install chromium`

---

## Copilot в PHPStorm

Copilot Agent Mode не требует ручной настройки MCP — он работает через встроенные инструменты JetBrains:
- **Чтение/запись файлов** — нативно через IDE
- **Запуск терминала** — нативно
- **Поиск по коду** — нативно

Для Copilot главный инструмент контекста — `.github/copilot-instructions.md`.
