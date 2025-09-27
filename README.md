# Autre - Sistema de Orçamentos

Aplicação PHP 7.2 com MySQL para controle de cadastros, orçamentos e relatórios comerciais/financeiros da Autre.

## Requisitos

- PHP 7.2+
- Servidor web (Apache/Nginx) configurado para apontar para o diretório do projeto
- MySQL 5.7+
- Extensões PHP: `pdo_mysql`, `openssl`, `mbstring`, `gd`

## Instalação

1. Clone este repositório e copie os arquivos para o diretório público do servidor web.
2. Crie um banco de dados MySQL (ex: `autre`).
3. Importe o arquivo [`database.sql`](database.sql) para criar as tabelas e o usuário administrador inicial.
4. Copie `config.php` para `config.local.php` (opcional) e ajuste as credenciais de acesso ao banco:

```php
<?php
$DB_HOST = 'localhost';
$DB_NAME = 'autre';
$DB_USER = 'root';
$DB_PASS = 'senha';
```

5. Certifique-se de que a pasta `uploads/` tenha permissão de escrita para upload de imagens de produtos.
6. Acesse `http://seu-servidor/login.php` e autentique com:
   - **E-mail:** `admin@autre.local`
   - **Senha:** `admin123`

## Estrutura principal

- `index.php` – roteador principal e layout com menu lateral responsivo.
- `pages/` – módulos para cada cadastro, orçamentos e relatórios.
- `budget_pdf.php` – exportação de orçamentos em PDF com fotos dos produtos.
- `assets/` – estilos e scripts (Bootstrap 5 via CDN).
- `vendor/fpdf/` – biblioteca FPDF simplificada para geração de PDFs.

## Funcionalidades

- **Cadastros CRUD** de clientes, obras, arquitetos, vendedores, projetistas/orçamentistas, categorias e produtos.
- **Controle de permissões**: somente administradores gerenciam usuários e produtos.
- **Orçamentos completos** com vinculação Cliente → Obra → Equipe, inclusão/remoção de itens, agrupamento, markup variável, desconto, frete, duplicação para revisões e geração de número de pedido ao fechar.
- **Exportação em PDF** com tabela de itens e fotos dos produtos.
- **Relatórios** de vendas (por mês/vendedor), orçamentos (status, top clientes/arquitetos) e financeiro (lucro, impostos estimados, comissões).
- **Interface responsiva** com menu lateral colapsável e cor predominante `#FFCC66`.

## Observações

- O cálculo de impostos no relatório financeiro utiliza uma alíquota padrão de 8%, ajustável via filtro no relatório.
- Para incluir imagens nos itens do orçamento, utilize o campo "Imagem" informando o caminho local (ex: `uploads/foto.jpg`) ou uma URL acessível.
- Recomenda-se proteger o diretório `uploads/` com regras de segurança no servidor web para evitar execução de arquivos indevidos.
