# Talentum

**Plataforma de freelancing para contratação e publicação de serviços**

## 📖 Visão Geral

Talentum é uma aplicação web construída em PHP e SQLite que conecta **clientes** e **freelancers**. Usuários podem:

* **Registrar**-se como cliente ou freelancer.
* **Criar**, **publicar** e **gerenciar** serviços (price, entrega, categoria, galeria de imagens/vídeos).
* **Pesquisar** e **filtrar** serviços (texto, categoria, preço mínimo/máximo, rating, prazo máximo).
* **Contratar** serviços e acompanhar status de pedidos.
* **Enviar mensagens** em tempo real (via AJAX) entre cliente e freelancer.
* **Ofertas personalizadas**: freelancers podem propor preços e prazos diferentes.
* **Avaliar** pedidos com nota (1–5) e comentário.
* **Painel de administração** com estatísticas, promoção de usuários e gerenciamento de categorias.

---

## 🛠 Tecnologias Utilizadas

* **Frontend**: HTML5, CSS3 (mobile-first, responsivo), JavaScript (vanilla + AJAX)
* **Backend**: PHP 8+, PDO e SQLite para persistência de dados
* **Banco de Dados**: SQLite (`project.db`)

---

## 🏗️ Estrutura do Projeto

```
ProjetoLTW/
├── actions/                # Ações (controllers) do CRUD e lógica de negócio
│   ├── add_service_action.php
│   ├── edit_service_action.php
│   ├── delete_service_action.php
│   ├── send_message_action.php  ← AJAX + fallback
│   └── ...
├── assets/
│   └── js/
│       └── app.js           # Código JS para AJAX chat e slider
├── css/
│   └── style.css            # Estilos globais e componentes
├── database/
│   ├── connection.php       # Conexão PDO SQLite
│   └── project.db           # Base de dados SQLite
├── includes/                # Helpers e segurança
│   ├── security.php         # Sessão, CSRF token, validação de entrada
│   ├── auth.php             # Funções de autenticação (`require_login`)
│   └── flash.php            # Mensagens flash (success, error, info)
├── pages/                   # Páginas públicas e privadas
│   ├── list_services.php    # Listagem e filtro de serviços
│   ├── service.php          # Detalhe e galeria com slider
│   └── messages_chat.php    # Chat com AJAX
└── templates/               # Templates de header e footer
    ├── header.php
    └── footer.php
```

## 🔒 Segurança

1. **Proteção contra SQL Injection**

   * Todas as interações com BD usam *prepared statements* do PDO.

2. **Mitigação de XSS (Cross-Site Scripting)**

   * Função `escape()` (wrapper de `htmlspecialchars`) aplicada a toda saída dinâmica.
   * `nl2br(escape(...))` para preservar quebras de linha sem expor HTML.

3. **Proteção CSRF (Cross-Site Request Forgery)**

   * Geração de um `csrf_token` único por sessão em `security.php`.
   * Validação em todas as ações de escrita (POST) via `verify_csrf()` ou manual.
   * No AJAX, o token é enviado no corpo do formulário.

4. **Armazenamento de Senhas**

   * `password_hash(..., PASSWORD_DEFAULT)` com salt e cost automático.
   * `password_verify()` na autenticação.

5. **Gerenciamento de Sessões**

   * `session_start()` centralizado em `security.php` com checagem de estado.
   * `require_login()` força acesso autenticado em páginas restritas.

6. **Validação de Entrada**

   * Função `sanitize()` para limpar `$_GET` e `$_POST` (trim, filtro de tipos).
   * Verificação de tipos (`is_numeric`, `filter_var`) antes de uso.

7. **Tratamento de Erros**

   * Uso de `try/catch` para capturar exceções de PDO.
   * Mensagens amigáveis via flash em vez de `die()`.

---

## ⚙️ Instalação & Configuração

1. Faça clone do repositório:

   ```bash
   git clone https://github.com/usuario/ProjetoLTW.git
   cd ProjetoLTW
   ```
2. Configure seu servidor local (XAMPP, WAMP, etc.) apontando o DocumentRoot para `ProjetoLTW/`.
3. Garanta permissões de escrita na pasta `uploads/`.

---

## 🚀 Uso

* Acesse **[http://localhost/ProjetoLTW/pages/home.php](http://localhost/ProjetoLTW/pages/home.php)** para a página inicial.
* Registre-se e faça login.
* Explore, filtre e contrate serviços.
* Freelancers podem publicar serviços em **Adicionar Serviço**.
* Utilize o chat em tempo real e avalie trabalhos concluídos.
* Admin: acesse **Admin Panel** para estatísticas e gerenciamento.

---

## 🤝 Contribuições

Contruibuições do projeto:
José Teixeira - up202305008
Tiago Ribeiro - up202307438
João Tedim - up202207790

---

## 📄 Licença

Este projeto está licenciado sob a [MIT License](LICENSE). Visite o arquivo LICENSE para mais detalhes.

---

*Desenvolvido com ❤️ por Talentum Team*
