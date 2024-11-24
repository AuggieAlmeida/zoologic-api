# Zoo Management API

A REST API built with PHP for managing a zoo's animals and staff.

## Pré-requisitos

- PHP 8.0 or higher
- MySQL/MariaDB
- Composer
- Apache/Nginx web server
- mod_rewrite enabled (for Apache)

## Instalação

1. Clone o repositório
2. Execute `composer install` no CMD para instalar as dependências
3. Configure as informações do banco de dados no arquivo `app/Config/Database.php`
4. Execute `php -S localhost:8000 -t public/` no CMD ou adicione o arquivo ao seu servidor web
5. Veja o status da api em `http://localhost:8000/api/health`
6. Para rodar os testes, execute `php vendor/bin/phpunit` no CMD