# larashop - Sistema de E-commerce com Laravel 12

Este projeto é um sistema completo de e-commerce desenvolvido com Laravel 12, incluindo gestão de produtos, variações, estoque, carrinho de compras, cupons de desconto, cálculo de frete, integração com ViaCEP, envio de e-mails, atualizações em tempo real com Laravel Reverb e webhooks para atualização de status de pedidos.

## Funcionalidades

### Gestão de Produtos
- Cadastro de produtos com nome, preço, descrição e estoque
- Suporte a variações de produtos (cores, tamanhos, etc.)
- Controle individualizado de estoque por produto e por variação
- Edição e exclusão de produtos
- Visualização detalhada do produto

### Carrinho de Compras
- Adição de produtos ao carrinho com seleção de variações
- Atualização de quantidades
- Remoção de itens
- Verificação automática de disponibilidade em estoque
- Cálculo automático de subtotal, frete e total

### Regras de Frete
- Frete de R$ 15,00 para compras entre R$ 52,00 e R$ 166,59
- Frete grátis para compras acima de R$ 200,00
- Frete de R$ 20,00 para outros valores

### Cupons de Desconto
- Criação de cupons por percentual ou valor fixo
- Configuração de valor mínimo para aplicação
- Definição de data de validade
- Aplicação/remoção de cupons no carrinho

### Checkout e Pedidos
- Verificação e validação de CEP via API ViaCEP
- Preenchimento automático de endereços
- Finalização de compra segura com verificação de estoque
- Listagem e visualização detalhada de pedidos

### Atualizações em Tempo Real
- Integração com Laravel Reverb para WebSockets
- Atualizações de status de pedidos em tempo real
- Notificações em tempo real para o usuário
- Experiência de usuário aprimorada sem necessidade de recarregar a página

### Sistema de Notificações
- Envio automático de e-mail com confirmação do pedido
- E-mail com detalhes completos da compra e endereço de entrega
- Notificações em tempo real via WebSockets

### API REST com Sanctum
- Autenticação segura via tokens
- Endpoints para produtos, pedidos e cupons
- Validação de permissões para operações administrativas

### Webhook
- Endpoint para atualização de status de pedidos
- Tratamento especial para cancelamentos (restauração de estoque)
- Integração fácil com sistemas externos

### Segurança
- Autenticação de usuários com Laravel UI
- Controle de acesso para áreas administrativas
- Validação de dados em todos os formulários
- Proteção contra CSRF

## Requisitos de Sistema

- PHP >= 8.2
- Composer
- MySQL ou outro banco de dados compatível com Laravel
- Node.js (>= 16.x) e NPM
- Extensões PHP: BCMath, Ctype, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML

## Instalação

Siga estas etapas para instalar e configurar o projeto:

### 1. Clone o repositório

```bash
git clone [url-do-repositorio]
cd larashop
```

### 2. Instale as dependências do PHP

```bash
composer install
```

### 3. Instale as dependências do JavaScript

```bash
npm install
```

### 4. Configure o ambiente

Crie uma cópia do arquivo `.env.example` e renomeie para `.env`:

```bash
cp .env.example .env
```

Gere a chave da aplicação:

```bash
php artisan key:generate
```

### 5. Configure o banco de dados

Edite o arquivo `.env` e configure as variáveis de banco de dados:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=larashop
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

### 6. Configure o e-mail

Ainda no arquivo `.env`, configure as variáveis de e-mail:

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=seu_username_mailtrap
MAIL_PASSWORD=sua_senha_mailtrap
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=loja@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

Para testes, recomendamos usar o [Mailtrap](https://mailtrap.io/).

### 7. Configure o Laravel Reverb (WebSockets)

Adicione estas variáveis ao arquivo `.env`:

```
BROADCAST_DRIVER=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_SERVER_HOST=127.0.0.1
REVERB_SERVER_PORT=8080
REVERB_SERVER_SCHEME=http

# Para o cliente Pusher/Reverb (frontend)
PUSHER_APP_ID="${REVERB_APP_ID}"
PUSHER_APP_KEY="${REVERB_APP_KEY}"
PUSHER_APP_SECRET="${REVERB_APP_SECRET}"
PUSHER_HOST="${REVERB_SERVER_HOST}"
PUSHER_PORT="${REVERB_SERVER_PORT}"
PUSHER_SCHEME="${REVERB_SERVER_SCHEME}"
PUSHER_APP_CLUSTER=mt1

# Para o frontend (Vite)
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

Para gerar valores seguros para as chaves do Reverb:

```bash
php artisan reverb:key
```

### 8. Execute as migrações e seeders

```bash
php artisan migrate --seed
```

### 9. Compile os assets

```bash
npm run dev
```

### 10. Inicie o servidor Laravel

```bash
php artisan serve
```

### 11. Inicie o servidor Reverb (em outro terminal)

```bash
php artisan reverb:start
```

Acesse o sistema em `http://localhost:8000`

## Estrutura do Banco de Dados

### Tabelas Principais

1. **users**
   - Armazena informações de usuários e administradores
   - Inclui campo `is_admin` para controle de acesso

2. **categories**
   - Armazena categorias de produtos
   - Relacionada com produtos

3. **products**
   - Armazena produtos com nome, preço e descrição
   - Relacionado com variações e estoque

4. **product_variations**
   - Armazena variações de produtos (cores, tamanhos, etc.)
   - Relacionado com produtos e estoque

5. **stocks**
   - Controla o estoque de produtos e suas variações
   - Pode estar relacionado diretamente ao produto ou a uma variação específica

6. **stock_movements**
   - Registra todas as alterações de estoque com auditoria
   - Rastreabilidade completa de movimentações

7. **orders**
   - Armazena pedidos com informações de valores, status e endereço
   - Relacionado com usuários e itens de pedido

8. **order_items**
   - Armazena os itens de cada pedido com quantidade e preço
   - Relacionado com pedidos, produtos e variações

9. **coupons**
   - Armazena cupons de desconto com tipo, valor e validade

10. **wishlists**
    - Lista de desejos dos usuários
    - Relaciona usuários com produtos desejados

## Tempo Real com Laravel Reverb

O sistema utiliza o Laravel Reverb para fornecer atualizações em tempo real para os usuários. Isso é especialmente útil para o acompanhamento do status de pedidos.

### Como o Reverb é utilizado

1. **Canais de broadcast**:
   - Canais privados por usuário (`private-user.{id}`)
   - Canais de pedidos específicos (`private-order.{id}`)
   - Canal geral de administração (`orders`)

2. **Eventos transmitidos**:
   - `OrderStatusUpdated`: Enviado quando o status de um pedido é alterado

3. **Interação do usuário**:
   - Atualização do status de pedidos em tempo real na interface
   - Notificações toast para informar o usuário sobre mudanças
   - Destaque visual para pedidos atualizados recentemente

### Configuração do Reverb

O Laravel Reverb é configurado para trabalhar como um servidor WebSocket que:

1. Executa em segundo plano (`php artisan reverb:start`)
2. Gerencia autenticação para canais privados
3. Transmite eventos para os clientes conectados
4. Mantém rastreamento de conexões e estatísticas

## Guia de Uso

### Gestão de Produtos (Administrador)

#### Criar Produtos
1. Faça login como administrador
2. Acesse a página inicial de produtos
3. Clique em "Novo Produto"
4. Preencha os campos obrigatórios (nome, preço, estoque)
5. Se necessário, adicione variações clicando em "Adicionar Variação" e preencha os campos
6. Clique em "Salvar Produto"

#### Editar Produtos
1. Na página de produtos, clique em "Ver Detalhes" de um produto
2. Clique em "Editar"
3. Atualize os campos desejados
4. Adicione ou remova variações conforme necessário
5. Clique em "Atualizar Produto"

#### Excluir Produtos
1. Na página de detalhes do produto, clique em "Excluir"
2. Confirme a exclusão

### Compras (Cliente)

#### Adicionar Produtos ao Carrinho
1. Navegue até a página de produtos
2. Clique em "Ver Detalhes" de um produto
3. Se o produto tiver variações, selecione uma
4. Defina a quantidade desejada
5. Clique em "Adicionar ao Carrinho"

#### Gerenciar Carrinho
1. Clique em "Carrinho" na barra de navegação
2. Atualize quantidades usando o campo de quantidade e o botão "Atualizar"
3. Remova itens clicando em "Remover"
4. Para aplicar um cupom, digite o código no campo e clique em "Aplicar"

#### Finalizar Compra
1. No carrinho, clique em "Finalizar Compra"
2. Digite seu CEP e clique em "Buscar" para preencher automaticamente o endereço
3. Complete os campos restantes (número, complemento, etc.)
4. Clique em "Finalizar Compra"
5. Você receberá um e-mail de confirmação com os detalhes do pedido

### Gestão de Cupons (Administrador)

#### Criar Cupons
1. Faça login como administrador
2. No menu, clique em "Cupons"
3. Clique em "Novo Cupom"
4. Preencha os campos:
   - Código (ex: DESCONTO10)
   - Tipo (percentual ou valor fixo)
   - Valor do desconto
   - Valor mínimo de compra
   - Data de expiração (opcional)
5. Clique em "Salvar Cupom"

#### Editar/Excluir Cupons
1. Na lista de cupons, clique em "Editar" ou "Excluir"
2. Confirme as alterações ou a exclusão

## API REST

A API REST está disponível para integração com outros sistemas:

### Autenticação

Para obter um token de acesso:

```
POST /api/login
Body: {
  "email": "seu@email.com",
  "password": "senha"
}
```

Use o token nas requisições seguintes no header:
```
Authorization: Bearer seu_token_aqui
```

### Endpoints Principais

#### Produtos
- `GET /api/products` - Listar produtos
- `GET /api/products/{id}` - Detalhes de um produto
- `POST /api/products` - Criar produto (requer autenticação admin)
- `PUT /api/products/{id}` - Atualizar produto (requer autenticação admin)
- `DELETE /api/products/{id}` - Excluir produto (requer autenticação admin)

#### Pedidos
- `GET /api/orders` - Listar pedidos do usuário autenticado
- `POST /api/orders` - Criar novo pedido
- `GET /api/orders/{id}` - Detalhes de um pedido

#### Cupons
- `POST /api/validate-coupon` - Validar um cupom
- `GET /api/coupons` - Listar cupons (requer autenticação admin)
- `POST /api/coupons` - Criar cupom (requer autenticação admin)
- `PUT /api/coupons/{id}` - Atualizar cupom (requer autenticação admin)
- `DELETE /api/coupons/{id}` - Excluir cupom (requer autenticação admin)

## Webhook para Status de Pedido

O webhook para atualização de status está disponível em:

```
POST /webhook/order
Body: {
  "order_id": 123,
  "status": "canceled"
}
```

Status possíveis:
- pending (pendente)
- processing (em processamento)
- completed (concluído)
- canceled (cancelado)

Quando um pedido é marcado como "canceled", o sistema automaticamente:
1. Restaura os itens ao estoque
2. Registra a movimentação de estoque
3. Atualiza o status do pedido
4. Notifica o usuário em tempo real via WebSockets

## Personalizando o Sistema

### Configurações de Frete

Para alterar as regras de frete, edite o método `getShipping()` no arquivo `app/Services/CartService.php`:

```php
public function getShipping()
{
    $subtotal = $this->getSubtotal();
    
    if ($subtotal >= 200) {
        return 0;
    } elseif ($subtotal >= 52 && $subtotal <= 166.59) {
        return 15;
    }
    
    return 20;
}
```

### Adicionando Mais Campos aos Produtos

Para adicionar novos campos aos produtos:

1. Crie uma migração para adicionar os campos na tabela `products`:

```bash
php artisan make:migration add_fields_to_products_table
```

2. Edite a migração:

```php
public function up()
{
    Schema::table('products', function (Blueprint $table) {
        $table->string('sku')->nullable();
        $table->boolean('featured')->default(false);
        // Outros campos...
    });
}
```

3. Adicione os campos no modelo `Product`:

```php
protected $fillable = [
    'name', 'price', 'description', 'sku', 'featured', 'category_id'
];
```

4. Execute a migração:

```bash
php artisan migrate
```

5. Atualize os formulários e views para incluir os novos campos.

## Solução de Problemas

### O sistema não está enviando e-mails

1. Verifique as configurações de e-mail no arquivo `.env`
2. Para testes, use o Mailtrap que captura os e-mails sem realmente enviá-los
3. Execute `php artisan config:clear` após alterar as configurações

### Problemas com WebSockets (Laravel Reverb)

1. Verifique se o servidor Reverb está rodando: `php artisan reverb:start`
2. Verifique as chaves de aplicativo no `.env`
3. Verifique os logs do Laravel: `tail -f storage/logs/laravel.log`
4. Certifique-se de que as rotas de broadcasting estão registradas: `php artisan route:list | grep broadcasting`
5. Verifique se o BroadcastServiceProvider está registrado em `bootstrap/providers.php`
6. Certifique-se de que o CSRF token está disponível na página

### Problemas com permissões

Se houver problemas de permissão nos diretórios `storage` ou `bootstrap/cache`:

```bash
chmod -R 775 storage bootstrap/cache
chown -R $USER:www-data storage bootstrap/cache
```

### Erros na migração de banco de dados

Se ocorrerem erros durante as migrações:

1. Verifique a conexão com o banco de dados
2. Tente resetar as migrações: `php artisan migrate:fresh --seed`
3. Verifique se não há erros de sintaxe nas migrações

### A API não está autenticando corretamente

1. Verifique se o middleware Sanctum está configurado corretamente
2. Certifique-se de enviar o token no formato correto: `Authorization: Bearer seu_token_aqui`
3. Verifique se o token não expirou

## Melhores Práticas Aplicadas no Projeto

- **MVC**: Separação clara de responsabilidades entre Models, Views e Controllers
- **Repository Pattern**: Centralização da lógica de acesso a dados
- **Service Layer**: Centralização da lógica de negócios em serviços reutilizáveis
- **Events & Listeners**: Uso de eventos para operações assíncronas e notificações
- **Real-time Updates**: Atualizações em tempo real com WebSockets
- **Validação**: Validação de dados em todas as entradas de formulários
- **Tratamento de Erros**: Feedback claro para o usuário em caso de erros
- **Controle de Estoque**: Verificação de disponibilidade em tempo real
- **Segurança**: Proteção contra CSRF, XSS e injeção de SQL
- **Otimização de Banco de Dados**: Índices apropriados e relacionamentos otimizados

## Considerações para Produção

Antes de colocar o sistema em produção, certifique-se de:

1. Configurar um servidor SMTP real para envio de e-mails
2. Configurar o arquivo `.env` com as variáveis de produção
3. Otimizar a aplicação:
   ```bash
   php artisan optimize
   php artisan route:cache
   php artisan view:cache
   ```
4. Configurar um SSL válido
5. Configurar backups regulares do banco de dados
6. Configurar o servidor Reverb com um gerenciador de processos como Supervisor:

```
[program:reverb]
process_name=%(program_name)s
command=php /path/to/your/project/artisan reverb:start
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/reverb.log
stopwaitsecs=3600
```

7. Configurar proxy WebSocket com Nginx:

```nginx
map $http_upgrade $connection_upgrade {
    default upgrade;
    ''      close;
}

server {
    # ... outras configurações ...

    # Proxy para o Laravel Reverb
    location /reverb {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection $connection_upgrade;
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }
}
```

## Contribuindo com o Projeto

Se deseja contribuir com o projeto:

1. Faça um fork do repositório
2. Crie uma branch para sua feature (`git checkout -b feature/nova-funcionalidade`)
3. Faça commit das suas alterações (`git commit -m 'Adicionando nova funcionalidade'`)
4. Envie para o branch remoto (`git push origin feature/nova-funcionalidade`)
5. Abra um Pull Request

## Licença

Este projeto está licenciado sob a licença MIT.
