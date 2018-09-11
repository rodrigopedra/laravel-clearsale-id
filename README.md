# API ClearSale

Integração com o WebService da ClearSale ID para Laravel.

**Implementação do manual de integração do ClearSale ID versão 9.6**

## Requisitos

PHP 5.6+

Extensões PHP

```
ext-openssl
ext-soap
ext-xml
```

No Ubuntu, você pode instalar através do seguinte comando:

```bash
sudo apt-get install openssl php-soap php-xml
```

A extensão `openssl` deve ser habilitada em seu arquivo `php.ini`.

## Instalação

A maneira mais fácil de instalar a biblioteca é através do [Composer](http://getcomposer.org/).

```bash
composer require rodrigopedra/laravel-clearsale-id
```
