# Order-Voucher case study

This repository contains demonstration of a case study with two microservices - Order and Voucher. 

The business logic behind it says that for each created Order greater or equal to 100 EUR, there should be a Voucher of 5 EUR created as well.

Both microservices work independently, and expose a simple API to show single/all order(s) and vouchers, respectively.

Order API also exposes endpoint to create new Order. When a new order is created, an event is published to underlying RabbitMQ queue.

There is also a simple CLI app which listens to RabbitMQ queue, processes messages from it and creates a voucher if the business rule is matched.

The RabbitMQ consumer supports orders with currencies other than EUR as well - in such case it converts the order amount to EUR first, and then checks the business condition.

All the implementations are based on [Laminas Mezzio](https://docs.mezzio.dev/).

## Requirements

*   Docker on the host machine

## Initial setup

1. Run composer install in both order and voucher sub-folders:

   ```shell
   cd order
   composer install
    ```
   ```shell
   cd voucher
   composer install
    ```
   
2. Run docker-compose in the root folder:

    ```shell
    docker-compose up
    ```

## Configuration

Application configuration can be modified in the [order/config/autoload/local.php] and [voucher/config/autoload/local.php] files, respectively, as well in the [docker-compose.yml] file.


## Available endpoints

1. Get orders

    ```shell
   curl --location --request GET 'localhost:8000/orders'
    ```

2. Get specific order (with id 1 in this case)

    ```shell
   curl --location --request GET 'localhost:8000/order/1'
    ```
   
3. Create new order

    ```shell
   curl --location --request POST 'localhost:8000/orders' \
    --header 'Content-Type: application/json' \
    --data-raw '{
      "amount": 10000,
      "currency": "EUR"
    }'
    ```

4. Get vouchers

    ```shell
   curl --location --request GET 'localhost:8001/vouchers'
    ```

5. Get specific voucher (with id 10 in this case)

    ```shell
   curl --location --request GET 'localhost:8001/voucher/10'
    ```

## Manually push pending orders to queue

Have the docker-compose running, and execute:

```shell
docker exec -it order-voucher-case-study_php-order-api_1 bash
```

When on docker container execute:

```shell
cd vendor/bin/
./laminas order:publish:pending
```
