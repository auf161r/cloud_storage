# Облачное хранилище (Cloud Storage)

## Описание проекта

REST API для облачного хранилища файлов, аналогичного Google Drive или Яндекс.Диску. Проект разработан в рамках финальной работы курса PHP-разработчик в Skillbox.

## Функциональность

### Пользователи

- ✅ Регистрация нового пользователя
- ✅ Авторизация (сессии + cookie)
- ✅ Выход из системы
- ✅ Просмотр профиля
- ✅ Редактирование своего профиля
- ✅ Сброс пароля через email (PHPMailer)

### Администраторы

- ✅ Просмотр списка всех пользователей
- ✅ Просмотр информации о пользователе
- ✅ Редактирование пользователей
- ✅ Удаление пользователей

### Файлы и папки

- ✅ Загрузка файлов (до 2 ГБ)
- ✅ Скачивание файлов
- ✅ Переименование файлов
- ✅ Удаление файлов
- ✅ Создание папок
- ✅ Переименование папок (с рекурсивным обновлением путей)
- ✅ Просмотр содержимого папки
- ✅ Удаление папок (только пустых)

### Шаринг

- ✅ Предоставление доступа к файлу другому пользователю
- ✅ Отзыв доступа
- ✅ Просмотр списка пользователей с доступом
- ✅ Отображение расшаренных файлов в общем списке

## Технологии

- PHP 8.2
- MySQL 5.7
- Apache
- XAMPP
- Composer
- PHPMailer

## Архитектура

- MVC
- Единая точка входа (index.php)
- Автозагрузка классов
- Singleton для подключения к БД
- Namespaces (PSR-4)
- Строгая типизация
- PHPDoc аннотации

## Структура проекта

cloud-storage/
├── app/
│ ├── Controllers/ # Контроллеры
│ ├── Models/ # Модели (работа с БД)
│ ├── Services/ # Сервисы (почта)
├── core/ # Ядро (Request, Response, Router, App, Db)
├── public/ # Точка входа (index.php)
├── storage/ # Загруженные файлы (по папкам пользователей). Создаётся автоматически при первом обращении к файлам.
├── .env.example # Переменные окружения
├── .gitignore # Игнорируемые файлы
├── cloud_storage.sql # Дамп базы данных
└── README.md

## API Эндпоинты

| -------------------------------------------------------- |

### Регистрация и авторизация (AUTH)

| ------ |

## POST /register

Регистрация нового пользователя

**Параметры запроса (form-data / x-www-form-urlencoded):**

- `email` (string, required) — email пользователя
- `password` (string, required) — пароль

# Пример запроса:

/register

form-data / x-www-form-urlencoded:

"email": "test@test.com",
"password": "12345678"

| ------ |

## POST /login

Авторизация в существующем аккаунте

**Параметры запроса (form-data / x-www-form-urlencoded):**

- `email` (string, required) — email пользователя
- `password` (string, required) — пароль

# Пример запроса:

/login

form-data / x-www-form-urlencoded:

"email": "test@test.com",
"password": "12345678"

| ------ |

## GET /logout

Выход из аккаунта

**Без параметров**

# Пример запроса:

/logout

| ------ |

## POST /reset_password

Отправить ссылку для сброса пароля на email

**Заголовок:** `Content-Type: application/json`

**Параметры запроса (raw / JSON):**

```json
{
  "email": "user@example.com"
}
```

# Пример запроса

/reset_password

```json
{
  "email": "test@test.com"
}
```

| ------ |

## POST /reset-password/{token}

Изменение пароля после сброса

**{token} необходимо изъять из ссылки, отправленной на указанный ранее email**
Пример ссылки: `http://frontend.local/reset-password?token=5f075e3caf34506cf0d521e6be6459ddaa995759d6aa1c9018ff1c96e2acadaf`
Нам нужно изъять из ссылки токен после `token=`: `5f075e3caf34506cf0d521e6be6459ddaa995759d6aa1c9018ff1c96e2acadaf`
**Параметры запроса (form-data / x-www-form-urlencoded):**

- `password` (string, required) — пароль

# Пример запроса

/reset-password/5f075e3caf34506cf0d521e6be6459ddaa995759d6aa1c9018ff1c96e2acadaf

form-data / x-www-form-urlencoded:

"password": "12345678"


| -------------------------------------------------------- |

### Пользователи (USERS)

| ------ |

## GET /users/list

Список пользователей

**Без параметров**

# Пример запроса:

/users/list

| ------ |

## GET /users/get/{id}

Информация о конкретном пользователе

**Параметр {id} передается в ссылке**

# Пример запроса:

/users/get/1

| ------ |

## GET /user/search/{email}

Поиск пользователя по email

**Параметр {email} передается в ссылке**

# Пример запроса:

/user/search/test@test.ru

| ------ |

## PUT /users/update

Обновление данных пользователя

**Параметры запроса (x-www-form-urlencoded):**

- `email` (string) — email пользователя
- `password` (string) — пароль

# Пример запроса:

/users/update

x-www-form-urlencoded:

"email": "test@test.com",
"password": "12345678"


| -------------------------------------------------------- |

### Администрирование (ADMIN)

| ------ |

## GET /admin/users/list

Список всех пользователей (расширенный) `Только для администратора`

**Без параметров**

# Пример запроса:

/admin/users/list

| ------ |

## GET /admin/users/get/{id}

Информация о конкретном пользователе (расширенная) `Только для администратора`

**Параметр {id} передается в ссылке**

# Пример запроса:

/admin/users/get/1

| ------ |

## PUT /admin/users/update/{id}

Обновление конкретного пользователя `Только для администратора`

**Параметр {id} передается в ссылке**
**Параметры запроса (x-www-form-urlencoded):**

- `email` (string) — email пользователя
- `password` (string) — пароль
- `role` (string) — роль пользователя (`user` or `admin`)

# Пример запроса:

/admin/users/update/1

x-www-form-urlencoded:

"email": "new-test@example.com",
"password": "qwerty12345"
"role": "admin"



| ------ |

## DELETE /admin/users/delete/{id}

Удаление конкретного пользователя `Только для администратора`
(Невозможно удаление самого себя)

**Параметр {id} передается в ссылке**

# Пример запроса:

/admin/users/delete/1

| -------------------------------------------------------- |

### Файлы (FILES)

| ------ |

## GET /files/list

Получить список загруженных файлов (в т.ч к которым предоставлен доступ через Share)

**Без параметров**

# Пример запроса:

/files/list

| ------ |

## GET /files/get/{id}

Скачать файл по id

**Параметр {id} передавать в ссылке**

# Пример запроса:

/files/get/3

| ------ |

## POST /files/add

Загрузить файл

**Параметры запроса (form-data):**

- `file` (type file) — загружаемый файл
- `directory_id` — id папки, в которую нужно положить загружаемый файл

# Пример запроса:

/files/add

form-data:

"file": my-pictures.png
"directory_id": 5


| ------ |

## PUT /files/rename

Переименовать файл

**Параметры запроса (x-www-form-urlencoded):**

- `id` (int, required) — ID файла
- `new_name` (string, required) — новое имя файла

# Пример запроса:

/files/rename

x-www-form-urlencoded:

"id": 2
"new_name": "php_icon.png"


| ------ |

## DELETE /files/remove/{id}

Удалить файл

**Параметр {id} передается в ссылке**

# Пример запроса:

/files/remove/2

| -------------------------------------------------------- |

### Папки (DIRECTORIES)

| ------ |

## POST /directories/add

Создать новую папку

**Параметры запроса (form-data / x-www-form-urlencoded):**

- `name` (string, required) — имя папки
- `parent_id` (int, optional) — ID родительской папки (если нужно создать вложенную папку)

# Пример запроса:

/directories/add

Корневая папка:

form-data / x-www-form-urlencoded:

"name": "Photos"


Вложенная папка

form-data / x-www-form-urlencoded:

"name": "Documents"
"parent_id": 1


| ------ |

### PUT /directories/rename

Переименовать папку (обновляет пути у всех вложенных элементов)

**Параметры запроса (x-www-form-urlencoded):**

- `id` (int, required) — ID папки
- `new_name` (string, required) — новое имя папки

# Пример запроса:

/directories/rename

x-www-form-urlencoded:

"id": 2
"new_name": "Movies"


| ------ |

## GET /directories/get/{id}

Получить содержимое папки (подпапки и файлы)

**Параметр {id} передается в ссылке**

# Пример запроса:

/directories/get/1

| ------ |

## DELETE /directories/delete/{id}

Удалить папку (только пустую)

**Параметр {id} передается в ссылке**

# Пример запроса:

/directories/delete/1

| -------------------------------------------------------- |

### Шаринг (SHARE)

| ------ |

## GET /files/share/{id}

Список пользователей с доступом к файлу с определенным id

**Параметр {id} передается в ссылке**

# Пример запроса:

/files/share/7

| ------ |

## PUT /files/share/{id}/{userId}

Предоставить доступ к файлу {id} пользователю {userId}

**Параметры {id} и {userId} передаются в ссылке**

# Пример запроса:

/files/share/7/2

| ------ |

## DELETE /files/share/{id}/{userId}

Отозвать доступ к файлу {id} пользователю {userId}

**Параметры {id} и {userId} передаются в ссылке**

# Пример запроса:

/files/share/7/2

## Установка и запуск

1. Клонировать репозиторий:

git clone <url-репозитория>

2. Установить зависимости через Composer:

composer install

3. Настроить виртуальный хост в Apache:

apache
<VirtualHost \*:80>
DocumentRoot "/path/to/your/project/public"
ServerName cloud-storage.local # ... остальные настройки
</VirtualHost>

4. Создать базу данных `cloud_storage` и импортировать дамп:

   **Вариант 1: через phpMyAdmin**
   - Зайдите в phpMyAdmin
   - Создайте базу `cloud_storage`
   - Выберите базу → вкладка "Импорт" → выберите файл `cloud_storage.sql`

   **Вариант 2: через командную строку**

   ```bash
   mysql -u root -p cloud_storage < cloud_storage.sql
   ```

5. Настроить .env файл:

DB_HOST=localhost
DB_NAME=cloud_storage
DB_USER=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
UPLOAD_MAX_SIZE=2147483648

MAIL_HOST=smtp.yandex.ru
MAIL_PORT=465
MAIL_USERNAME=ваш@yandex.ru
MAIL_PASSWORD=ваш*пароль
MAIL_ENCRYPTION=ssl
MAIL_FROM=ваш@yandex.ru
MAIL_FROM_NAME="Cloud Storage"
FRONTEND_URL=http://frontend.local

6. Запустить Apache и MySQL

7. Открыть в браузере: http://cloud-storage.local

## Тестирование

Для тестирования API рекомендуется использовать Postman.

Примечание: Фронтенд frontend.local — это демонстрационное приложение, для тестирования можно использовать любой HTTP-клиент (Postman) и извлекать токен из письма.

Автор
Александр Аущенко

В проекте также присутствует фронтенд-часть (React), расположенная в папке /frontend. Она подключается к API через соответствующие эндпоинты.
