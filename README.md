# PlayTask

SaaS leve de **todo lists colaborativas em tempo real**, distribuído por convite. Cada usuário gerencia suas listas em um painel administrativo Filament, podendo torná-las públicas (via slug), protegê-las por senha e/ou marcá-las como somente leitura. Listas públicas são consumidas em um Guest Panel sem autenticação, com sincronização em tempo real via Laravel Reverb.

## Stack

- PHP 8.4
- Laravel 13
- Filament 5
- Livewire 4 + Flux UI 2
- Laravel Reverb 1 (WebSockets)
- Pest 4
- Tailwind CSS 4

## Requisitos

- PHP 8.4+
- Composer 2.x
- Node.js 20+ e npm
- SQLite (padrão) ou MySQL/PostgreSQL

## Instalação

Clone o repositório e instale as dependências PHP e JS:

```bash
git clone <url-do-repositorio> playtask
cd playtask
composer setup
```

O script `composer setup` executa:

1. `composer install`
2. Cria o arquivo `.env` a partir de `.env.example`
3. Gera a chave da aplicação (`php artisan key:generate`)
4. Roda as migrations (`php artisan migrate --force`)
5. Instala as dependências do npm
6. Faz o build dos assets (`npm run build`)

## Rodando o ambiente de desenvolvimento

Use o comando abaixo para subir tudo de uma vez — servidor PHP, worker de filas, logs em tempo real (Pail) e Vite — em paralelo, no mesmo terminal:

```bash
composer dev
```

Esse comando executa, simultaneamente, via `concurrently`:

| Processo | Comando                                       | Cor       |
| -------- | --------------------------------------------- | --------- |
| `server` | `php artisan serve`                           | azul      |
| `queue`  | `php artisan queue:listen --tries=1 --timeout=0` | violeta |
| `logs`   | `php artisan pail --timeout=0`                | rosa      |
| `vite`   | `npm run dev`                                 | laranja   |

Para encerrar, pressione `Ctrl+C` — todos os processos são finalizados juntos (`--kill-others`).

> **Observação:** o Reverb (WebSocket) **não** está incluído no `composer dev`. Rode-o separadamente quando precisar testar broadcasting em tempo real:
>
> ```bash
> php artisan reverb:start
> ```

## Testes

```bash
php artisan test --compact
```

Ou, para o pipeline completo (config clear + lint check + testes):

```bash
composer test
```

## Lint / Formatação

```bash
composer lint         # aplica Pint
composer lint:check   # apenas verifica
```

## Painéis

| Painel             | URL          | Acesso                          |
| ------------------ | ------------ | ------------------------------- |
| Landing / Guest    | `/`          | Público                         |
| Lista pública      | `/l/{slug}`  | Público (com password opcional) |
| Admin Panel        | `/admin`     | Usuários convidados             |
| Superadmin Panel   | `/superadmin`| Operadores da plataforma        |

## Documentação adicional

- [`docs/prd.md`](docs/prd.md) — Product Requirements Document
- [`docs/prompts.md`](docs/prompts.md) — histórico de prompts usados na construção
- [`CLAUDE.md`](CLAUDE.md) — diretrizes para assistentes de código

## Licença

MIT.
