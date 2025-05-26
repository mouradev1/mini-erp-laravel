# Mini ERP - Laravel

Sistema simples de ERP para gestão de produtos, pedidos, cupons e estoque, desenvolvido em Laravel 12, com suporte a variações, carrinho de compras, cupons, envio de e-mail e integração via webhook.

---

## Funcionalidades

- Cadastro, edição e listagem de produtos
- Controle de estoque (por produto e variação)
- Cadastro e aplicação de cupons de desconto
- Carrinho de compras com cálculo de frete automático
- Finalização de pedido com envio de e-mail de confirmação
- Consulta de endereço via ViaCEP
- Webhook para atualização/cancelamento de pedidos
- Interface responsiva com Bootstrap

---

## Requisitos

- Docker e Docker Compose instalados
- (Opcional) PHP 8.2+ e Composer, caso queira rodar localmente sem Docker

---

## Subindo o Projeto com Docker

1. **Clone o repositório:**
   ```sh
   git clone https://github.com/mouradev1/mini-erp-laravel.git
   cd mini-erp-laravel
   cp .env.example .env
   composer install
   ```

2. **Crie as pastas necessárias:**
   ```sh
   mkdir -p docker/app docker/mysql docker/config/mailhog
   ```

3. **Suba os containers:**
   ```sh
   docker-compose up --build
   ```

4. **Rode as migrations/seeders:**
   ```sh
    docker exec -it mini-erp-app php artisan migrate --seed && php artisan key:generate

   ```

5. **Acesse o sistema:**
   - Aplicação: [http://localhost:8000](http://localhost:8000)
   - MailHog (visualizar e-mails): [http://localhost:8025](http://localhost:8025)

---

## Como funciona

- **Produtos:** Cadastre produtos, variações e estoques. Edite e compre direto da listagem.
- **Carrinho:** Adicione produtos, escolha variações, controle quantidades. O frete é calculado automaticamente:
  - R$52,00 a R$166,59: frete R$15,00
  - Acima de R$200,00: frete grátis
  - Outros valores: frete R$20,00
- **Cupons:** Cadastre cupons com validade e valor mínimo. Aplique no carrinho.
- **Pedido:** Finalize o pedido preenchendo nome, e-mail, CEP (consulta automática) e endereço. Receba e-mail de confirmação (visualize no MailHog).
- **Webhook:** Endpoint `/webhook` para receber atualizações de status do pedido (ex: cancelamento repõe estoque).

---

## Webhook

- Endpoint: `POST /webhook`
- Exemplo de payload:
  ```json
  {
    "id": 1,
    "status": "cancelado"
  }
  ```
- Exemplo de chamada:
  ```sh
  curl -X POST http://localhost:8000/api/webhook \
    -H "Content-Type: application/json" \
    -d '{"id":1,"status":"cancelado"}'
  ```

---

## Variáveis de ambiente (.env)

- O projeto já vem configurado para rodar com Docker:
  ```
  DB_HOST=db
  DB_PORT=3306
  DB_DATABASE=erp
  DB_USERNAME=root
  DB_PASSWORD=docker

  MAIL_MAILER=smtp
  MAIL_HOST=mailhog
  MAIL_PORT=1025
  ```

---

## Estrutura de Pastas

- `app/Http/Controllers` - Lógica dos controllers
- `app/Models` - Models Eloquent
- `database/migrations` - Migrations das tabelas
- `database/seeders` - Seeders (ex: CupomSeeder)
- `resources/views` - Views Blade (produtos, cupons, carrinho, etc)
- `docker/` - Configurações Docker

---

## Observações

- O MailHog é apenas para desenvolvimento (não envia e-mails reais).
- O sistema foi pensado para ser simples, limpo e fácil de manter.
- Para dúvidas ou sugestões, abra uma issue.


## Acessando o Projeto
- Acesse a aplicação em [http://localhost:8000](http://localhost:8000)
- Visualize os e-mails enviados em [http://localhost:8025](http://localhost:8025)