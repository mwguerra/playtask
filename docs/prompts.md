# Prompts PlayTask

## Prompt 1 — PRD

Crie o PRD `docs/prd.md` (escrito em Português do Brasil) para uma aplicação chamada PlayTask que tem a stack Laravel 13, Filament 5, Pest e Reverb. Esta aplicação será um SaaS, somente por convite (sem registro pelo site).

Toda a autenticação será feita através do sistema de autenticação do Filament, portanto não usaremos diretamente nenhum sistema padrão fornecido pelo Laravel, como Fortify ou Sanctum.

No Filament 5, teremos 3 painéis: Guest Panel (LP e todas as todo lists públicas, sem autenticação), Admin Panel (painel do usuário para criar, gerenciar e manter todo lists) e Superadmin Panel (painel SaaS que visualiza os e-mails registrados para o Beta e permite criar usuários com senha aleatória gerada e botão de copy — sem envio de senha ou e-mail para o usuário). Em todos os painéis Filament 5, deve ser instalado um custom theme conforme a documentação oficial do Filament.

Na LP, deve haver uma seção para que visitantes possam deixar seu e-mail para inscrição no Beta em modelo single opt-in, ou seja, sem confirmação por e-mail (os e-mails inseridos serão armazenados no banco de dados e exibidos em um resource no painel Filament Superadmin).

No painel admin, os usuários farão a gestão de suas todo lists por meio de uma custom page do Filament. O isolamento entre usuários deve ser estritamente por dono (`user_id`), de forma que cada usuário só visualize e gerencie suas próprias todo lists. No painel admin, o menu deve ser colapsável, com default collapsed até o breakpoint 2XL (a partir de 2XL, o menu deve carregar não colapsado por padrão). Nessa custom page, do lado esquerdo haverá uma sidebar com as todo lists, ordenadas por `created_at`, com a mais recente primeiro. Ao clicar em uma todo list, ela deve ser exibida no espaço principal da página, com ações como criação e edição de cada item realizadas em um slideover. Todas as actions das páginas de task e task list devem estar no formato ícone, conforme a documentação do Filament 5.

O dashboard do painel admin deve conter, no mínimo, stats com as seguintes informações: minhas listas (contagem total, com a quantidade de listas públicas e a quantidade de listas com senha), itens pendentes (quantos concluídos e quantos no total) e progresso geral (em %). Além das stats, o dashboard deve conter ao menos 2 gráficos com informações relevantes, como distribuição por tags, listas em aberto, entre outras métricas pertinentes.

O Reverb manterá a lista das todo lists, seus itens e seus status atualizados em tempo real (no painel admin e nas páginas públicas das todo lists).

Cada todo list terá uma action de configuração que abrirá um slideover com as seguintes opções: slug (único na aplicação), é pública, é readonly, precisa de senha e campo de senha (armazenado o hash do bcrypt, como uma senha tradicional). Páginas públicas (Guest Panel) devem permitir edição pelos visitantes quando a todo list não for readonly. Se uma página pública precisar de senha para edição, esta deverá ser solicitada no primeiro acesso, como uma password wall, não sendo mais requerida durante a mesma sessão — a autorização deve ser persistida na session do Laravel por slug.

Cada todo deve ter as seguintes variáveis: título, complexidade (baixa/média/alta), estimativa para conclusão (horas/dias/semanas — sem números, apenas estes 3 itens de enum), tags, `started_at` e `completed_at`. O status "concluído" de um todo deve ser representado apenas pelos timestamps (`started_at` e `completed_at`), sem coluna de status dedicada. O sistema de tags deve ser free-form e global por usuário, ou seja, cada usuário mantém seu próprio conjunto de tags reutilizáveis entre suas todo lists. Dentro de uma todo list, os itens devem ser ordenados por `created_at`, com os mais antigos primeiro. Não utilize colunas Enum no banco; prefira colunas string e use classes e cast Enum.

Utilize sempre componentes Filament nas views para manter UI e UX consistentes. Todo form schema deve fazer uso de sections e fieldsets quando o agrupamento de inputs fizer sentido.

---

## Prompt 2 — Planejamento e implementação

A partir do arquivo `docs/prd.md`, crie e priorize as atividades para implementar o webapp descrito. Considere a divisão por fases e atividades.

Ao criar novos arquivos, sempre que possível, dê preferência ao uso de comandos `artisan` em vez de criá-los diretamente.

Configure e teste também o Reverb e o Echo, garantindo que a atualização em tempo real das todo lists, itens e status esteja plenamente funcional tanto no Admin Panel quanto nas páginas públicas do Guest Panel. Todo o PRD deve ser implementado e estar funcional ao final da execução.

Crie Seeders para todos os dados e testes Pest para os fluxos principais da aplicação.

Execute tudo de forma autônoma, diretamente na branch `main`.

---

## Prompt 3 — Documentação de testes E2E

Analise a aplicação e escreva um documento `docs/e2e-testing.md` contendo todos os principais fluxos de uso dos usuários da aplicação (não super-admins).

Os fluxos devem incluir: login, criação de todo lists e itens, configuração de slugs, edição e conclusão de atividades, e visualização de todo lists públicas com e sem senha.

---

## Prompt 4 — Execução dos testes E2E

Utilize o Playwright MCP, executando todos os fluxos de teste detalhados em `docs/e2e-testing.md`.

Enquanto realiza os testes no browser, tire screenshots de todas as telas populadas, colocando-os na pasta `resources/images` com nomes descritivos.

Resolva qualquer problema que encontrar ao longo do caminho, sempre com foco nas features entregues e em UI e UX.

---

## Prompt 5 — Refatoração da LP e documentação do usuário

Utilize as imagens capturadas durante os testes e2e e refatore a LP seguindo os padrões de mercado para LPs deste tipo de aplicação.

Crie também um novo item de menu (Docs), com uma página de documentação que explique de forma simples e navegável como operar o app, utilizando as imagens dos testes e2e, o fluxo e2e e seu conhecimento geral.