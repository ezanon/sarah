# 🎓 SARaH

**Sistema de Alimentação e Recuperação de Dados Híbridos**

---

##  Sobre o SARaH

O **SARaH** é um sistema complementar desenvolvido pelo Instituto de Geociências da USP para registrar informações acadêmicas e pessoais dos docentes e pesquisadores que não estão disponíveis no sistema replicado oficial.

A ideia é simples: oferecer um espaço onde cada usuário possa manter atualizados seus dados profissionais — como sala de trabalho, veículos, links acadêmicos, ODS que sua pesquisa contempla, nível de produtividade no CNPq e foto de perfil — de forma centralizada, fácil e segura.

---

## ✨ O que o sistema oferece

- 🏢 **Minha Sala** — registro da sala de trabalho, com administração de tipos, blocos e andares
-  **Meus Veículos** — cadastro de carros e motos com validação automática de placas
- 🔗 **Links Acadêmicos** — Lattes, ORCID, Google Scholar, Scopus, ResearchGate, ResearcherID e BV FAPESP, com geração automática de URLs a partir de identificadores
- 🌍 **Minhas ODS** — seleção visual das 17 metas da ONU que seu trabalho contempla
- 🎖️ **Nível CNPq** — registro da bolsa de produtividade em pesquisa vigente
- 📷 **Foto de Perfil** — upload de foto com ajuste automático para o padrão 3x4
- 🏠 **Painel Inicial** — resumo de todas as informações em uma única tela

---

No primeiro acesso, o SARaH já tenta preencher automaticamente seus links do Lattes e ORCID com base nos dados oficiais da USP. Depois é só complementar o que quiser.

---

## 🛠️ Para Desenvolvedores

### Popular a tabela de usuários

O SARaH possui um comando Artisan dedicado para importar e sincronizar usuários diretamente do Replicado da USP. Isso permite pré-popular o banco de dados com docentes, pós-doutorandos, colaboradores e pós-graduandos antes mesmo do primeiro login via Senha Única.

Para executar a importação, utilize o seguinte comando no terminal:

```bash
php artisan sarah:importar-usuarios
```

**O que este comando faz:**
- Busca a lista de usuários ativos no Replicado (USP).
- Cria ou atualiza os registros na tabela `users` (nome e email oficial).
- Sincroniza automaticamente o ID Lattes e o ORCID na tabela de links acadêmicos.
- Exibe um relatório detalhado no terminal com o número de criados, atualizados e eventuais erros.

*(Opcional) Para manter o sistema sempre atualizado, você pode agendar a execução diária adicionando a linha abaixo no `app/Console/Kernel.php`:*

```php
$schedule->command('sarah:importar-usuarios')->dailyAt('03:00');
```

---

## 🚀 Instalação

1. Clone o repositório: `git clone ...`
2. Instale as dependências: `composer install`
3. Copie o arquivo de ambiente: `cp .env.example .env`
4. Gere a chave da aplicação: `php artisan key:generate`
5. Rode as migrações do banco: `php artisan migrate`
6. Crie o link de armazenamento (essencial para as fotos): `php artisan storage:link`

---

## Gerenciamento de Permissões e Roles

Para criar novas permissões ou roles, edite o arquivo:
`app/Providers/AppServiceProvider.php`

---

## 💡 Contribuições e sugestões

O SARaH é um projeto em constante evolução. Sugestões de novas funcionalidades, ajustes ou melhorias são sempre bem-vindas.

---

## 📄 Licença

Projeto desenvolvido para uso institucional no âmbito da Universidade de São Paulo.