<?php

namespace app\Models;

use Core\Db;

class UserModel
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Db::getInstance()->getConnection();
    }

    /**
     * Создать нового пользователя
     * 
     * @param array $data Данные пользователя (email, password, role)
     * @return int|false ID созданного пользователя или false при ошибке
     */
    public function create(array $data): int|false
    {
        $sql = "INSERT INTO user (email, password, role) VALUES (:email, :password, :role)";
        $statement = $this->db->prepare($sql);
        $result = $statement->execute([
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'] ?? 'user'
        ]);

        if ($result) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Получить список всех пользователей
     * 
     * @return array Массив всех пользователей
     */
    public function getAll(): array
    {
        $statement = $this->db->prepare('SELECT * FROM user');
        $statement->execute();
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getAllPublic(): array
{
    $sql = 'SELECT email, role FROM user';
    $statement = $this->db->prepare($sql);
    $statement->execute();
    return $statement->fetchAll(\PDO::FETCH_ASSOC);
}

    /**
     * Найти пользователя по ID
     * 
     * @param int $id ID пользователя
     * @return array|false Массив с данными пользователя или false, если не найден
     */
    public function getById(int $id): array|false
    {
        $statement = $this->db->prepare('SELECT * FROM user WHERE id = :id');
        $statement->execute(['id' => $id]);
        return $statement->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Найти пользователя по email
     * 
     * @param string $email Email пользователя
     * @return array|false Массив с данными пользователя или false, если не найден
     */
    public function findByEmail(string $email): array|false
    {
        $statement = $this->db->prepare('SELECT * FROM user WHERE email = :email');
        $statement->execute(['email' => $email]);
        return $statement->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Удалить пользователя
     * 
     * @param int $id ID пользователя
     * @return bool true при успехе, false при ошибке
     */
    public function delete(int $id): bool
    {
        $statement = $this->db->prepare('DELETE FROM user WHERE id = :id');
        return $statement->execute(['id' => $id]);
    }

    /**
     * Обновить данные пользователя
     * 
     * @param int $id ID пользователя
     * @param array $data Массив данных для обновления (email, password и т.д.)
     * @return bool true при успехе, false при ошибке
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[$key] = $value;
        }

        $fieldsString = implode(', ', $fields);
        $sql = "UPDATE user SET $fieldsString WHERE id = :id";

        $statement = $this->db->prepare($sql);
        return $statement->execute($params);
    }

    /**
     * Сохранить токен для сброса пароля
     * 
     * @param string $email Email пользователя
     * @param string $token Сгенерированный токен
     * @param string $expires Дата истечения срока токена
     * @return void
     */
    public function saveResetToken(string $email, string $token, string $expires): void
    {
        $statement = $this->db->prepare('UPDATE user SET reset_token = :reset_token, reset_expires = :reset_expires WHERE email = :email');
        $statement->execute([
            'reset_token' => $token,
            'reset_expires' => $expires,
            'email' => $email
        ]);
    }

    /**
     * Найти пользователя по токену сброса пароля
     * 
     * @param string $token Токен сброса пароля
     * @return array|null Массив с данными пользователя или null, если токен недействителен
     */
    public function findByResetToken(string $token): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM user WHERE reset_token = :token');
        $statement->execute(['token' => $token]);
        $user = $statement->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            return null;
        }

        $now = date('Y-m-d H:i:s');
        if ($user['reset_expires'] < $now) {
            return null;
        }

        return $user;
    }
}
