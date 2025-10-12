# Projeto TCC - Estrutura Base

Este é um projeto base para desenvolvimento de um sistema web usando PHP, JavaScript, HTML, CSS e Bootstrap.

## 📁 Estrutura do Projeto

```
projeto-tcc/
├── assets/                 # Recursos estáticos
│   ├── images/            # Imagens do sistema
│   └── icons/             # Ícones personalizados
├── config/                # Configurações do sistema
│   ├── config.php         # Configurações gerais
│   └── database.php       # Configuração do banco de dados
├── css/                   # Folhas de estilo
│   └── style.css          # CSS principal customizado
├── db/                    # Arquivos do banco de dados
│   └── (adicionar arquivos SQL aqui)
├── includes/              # Arquivos PHP reutilizáveis
│   ├── header.php         # Cabeçalho comum
│   ├── footer.php         # Rodapé comum
│   └── functions.php      # Funções auxiliares
├── js/                    # Scripts JavaScript
│   └── main.js            # JavaScript principal
├── pages/                 # Páginas do sistema
│   ├── admin/             # Área administrativa
│   │   ├── login.php      # Login administrativo
│   │   └── dashboard.php  # Painel administrativo
│   └── user/              # Área do usuário
│       ├── login.php      # Login do usuário
│       ├── cadastro.php   # Cadastro de usuário
│       └── dashboard.php  # Dashboard do usuário
├── index.php              # Página inicial
└── README.md              # Este arquivo
```

## 🚀 Como Começar

### 1. Configuração do Ambiente

1. **Servidor Web**: Configure um servidor web (Apache/Nginx) com PHP 7.4+
2. **Banco de Dados**: Configure MySQL/MariaDB
3. **Dependências**: Certifique-se de que as extensões PHP necessárias estão instaladas:
   - PDO
   - PDO_MySQL
   - mbstring
   - openssl

### 2. Configuração do Banco de Dados

1. Crie um banco de dados MySQL
2. Adicione os arquivos SQL na pasta `db/`
3. Configure as credenciais em `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'nome_do_seu_banco');
   define('DB_USER', 'seu_usuario');
   define('DB_PASS', 'sua_senha');
   ```

### 3. Configuração do Sistema

1. Edite `config/config.php` com as configurações do seu ambiente
2. Ajuste a `SITE_URL` para o endereço do seu projeto
3. Configure o ambiente (`development` ou `production`)

### 4. Estrutura de Desenvolvimento

#### Páginas de Usuário (`pages/user/`)
- `login.php` - Formulário de login
- `cadastro.php` - Formulário de cadastro
- `dashboard.php` - Área principal do usuário

#### Páginas Administrativas (`pages/admin/`)
- `login.php` - Login administrativo
- `dashboard.php` - Painel de controle

#### Arquivos de Configuração (`config/`)
- `config.php` - Configurações gerais do sistema
- `database.php` - Conexão com banco de dados

#### Arquivos Reutilizáveis (`includes/`)
- `header.php` - Cabeçalho HTML comum
- `footer.php` - Rodapé HTML comum
- `functions.php` - Funções PHP auxiliares

## 🎨 Recursos Incluídos

### Frontend
- **Bootstrap 5.3.0** - Framework CSS responsivo
- **Font Awesome 6.0** - Ícones
- **jQuery 3.6.0** - Biblioteca JavaScript
- **CSS Customizado** - Estilos personalizados

### Backend
- **PHP 7.4+** - Linguagem de programação
- **PDO** - Conexão segura com banco de dados
- **Sessões** - Gerenciamento de login
- **Funções de Segurança** - Sanitização e validação

### Funcionalidades Base
- Sistema de login/logout
- Validação de formulários
- Mensagens de feedback
- Design responsivo
- Estrutura MVC básica

## 📝 Próximos Passos

### Para Implementar:

1. **Banco de Dados**
   - [ ] Criar tabelas de usuários
   - [ ] Criar tabelas específicas do projeto
   - [ ] Implementar relacionamentos

2. **Autenticação**
   - [ ] Implementar lógica de login
   - [ ] Implementar cadastro de usuários
   - [ ] Implementar recuperação de senha
   - [ ] Implementar controle de sessão

3. **Funcionalidades Principais**
   - [ ] Definir e implementar funcionalidades específicas
   - [ ] Criar formulários de entrada de dados
   - [ ] Implementar operações CRUD
   - [ ] Criar relatórios e dashboards

4. **Segurança**
   - [ ] Implementar proteção CSRF
   - [ ] Validar e sanitizar todas as entradas
   - [ ] Implementar controle de acesso
   - [ ] Configurar HTTPS

5. **Interface**
   - [ ] Personalizar design conforme necessário
   - [ ] Adicionar imagens e ícones
   - [ ] Implementar feedback visual
   - [ ] Otimizar para dispositivos móveis

## 🛠️ Tecnologias Utilizadas

- **PHP** - Linguagem de programação backend
- **MySQL** - Sistema de gerenciamento de banco de dados
- **HTML5** - Estrutura das páginas
- **CSS3** - Estilização
- **Bootstrap 5** - Framework CSS
- **JavaScript** - Interatividade frontend
- **jQuery** - Biblioteca JavaScript

## 📋 Convenções de Código

### PHP
- Use camelCase para variáveis: `$nomeUsuario`
- Use snake_case para nomes de arquivos: `config_database.php`
- Sempre use `<?php` para abrir tags PHP
- Comente funções e classes adequadamente

### JavaScript
- Use camelCase para variáveis e funções
- Use const/let ao invés de var
- Comente código complexo

### CSS
- Use kebab-case para classes: `.btn-primary`
- Organize estilos por seções
- Use variáveis CSS quando possível

### Banco de Dados
- Use snake_case para nomes de tabelas e colunas
- Sempre use chaves primárias
- Documente relacionamentos

## 🤝 Contribuição

Este é um projeto de TCC. Para contribuir:

1. Discuta mudanças com o grupo
2. Mantenha a estrutura organizada
3. Documente novas funcionalidades
4. Teste antes de fazer commit

## 📞 Suporte

Para dúvidas sobre a estrutura do projeto, consulte:
- Documentação do PHP: https://www.php.net/docs.php
- Documentação do Bootstrap: https://getbootstrap.com/docs/
- Documentação do MySQL: https://dev.mysql.com/doc/

---

**Boa sorte com o desenvolvimento do seu TCC! 🎓**
