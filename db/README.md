# Pasta para arquivos SQL do banco de dados

Adicione aqui os arquivos SQL do seu projeto:

- `create_database.sql` - Script de criação do banco
- `create_tables.sql` - Script de criação das tabelas
- `insert_data.sql` - Script de inserção de dados iniciais
- `procedures.sql` - Stored procedures (se necessário)

## Exemplo de estrutura:

```sql
-- create_database.sql
CREATE DATABASE IF NOT EXISTS nome_do_banco_tcc 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE nome_do_banco_tcc;
```

```sql
-- create_tables.sql
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```
