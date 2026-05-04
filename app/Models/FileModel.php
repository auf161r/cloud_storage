<?php

namespace app\Models;

use Core\Db;

class FileModel
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Db::getInstance()->getConnection();
    }

    /**
     * Создать запись о файле
     * 
     * @param array $data Данные файла (user_id, name, original_name, path, size)
     * @return int|false ID созданной записи или false при ошибке
     */
    public function create(array $data): int|false
    {
        $sql = "INSERT INTO files (user_id, directory_id, name, original_name, path, size) VALUES (:user_id, :directory_id, :name, :original_name, :path, :size)";
        $statement = $this->db->prepare($sql);
        $result = $statement->execute([
            'user_id' => $data['user_id'],
            'directory_id' => $data['directory_id'],
            'name' => $data['name'],
            'original_name' => $data['original_name'],
            'path' => $data['path'],
            'size' => $data['size']
        ]);

        if ($result) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Найти файл по ID
     * 
     * @param int $id ID файла
     * @return array|false Данные файла или false
     */
    public function getById(int $id): array|false
    {
        $sql = 'SELECT * FROM files WHERE id = :id';
        $statement = $this->db->prepare($sql);
        $statement->execute(['id' => $id]);

        return $statement->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Получить все файлы пользователя
     * 
     * @param int $userId ID пользователя
     * @return array Массив файлов
     */
    public function getByUserId(int $userId): array
    {
        $sql = 'SELECT * FROM files WHERE user_id = :user_id';
        $statement = $this->db->prepare($sql);
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Удалить запись о файле
     * 
     * @param int $id ID файла
     * @return bool true при успехе
     */
    public function delete(int $id): bool
    {
        $sql = 'DELETE FROM files WHERE id = :id';
        $statement = $this->db->prepare($sql);

        return $statement->execute(['id' => $id]);
    }

    /**
     * Обновить имя файла
     * 
     * @param int $id ID файла
     * @param string $newName Новое имя
     * @return bool true при успехе
     */
    public function updateName(int $id, string $newName): bool
    {
        $sql = 'UPDATE files SET name = :name WHERE id = :id';
        $statement = $this->db->prepare($sql);

        return $statement->execute(['id' => $id, 'name' => $newName]);
    }

    public function updateOriginalName(int $id, string $originalName): bool
    {
        $sql = 'UPDATE files SET original_name = :original_name WHERE id = :id';
        $statement = $this->db->prepare($sql);
        return $statement->execute(['id' => $id, 'original_name' => $originalName]);
    }

    /**
     * Получить все файлы в папке
     * 
     * @param int $directoryId ID папки
     * @return array Массив файлов
     */
    public function getByDirectoryId(int $directoryId): array
    {
        $sql = 'SELECT * FROM files WHERE directory_id = :directory_id';
        $statement = $this->db->prepare($sql);
        $statement->execute(['directory_id' => $directoryId]);
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Обновить путь файла
     * 
     * @param int $id ID файла
     * @param string $newPath Новый путь
     * @return bool true при успехе
     */
    public function updatePath(int $id, string $newPath): bool
    {
        $sql = 'UPDATE files SET path = :path WHERE id = :id';
        $statement = $this->db->prepare($sql);
        return $statement->execute(['id' => $id, 'path' => $newPath]);
    }

    public function getSharedWithUser(int $userId): array
    {
        $sql = "SELECT f.* FROM files f JOIN file_shares fs ON f.id = fs.file_id WHERE fs.user_id = :user_id";
        $statement = $this->db->prepare($sql);
        $statement->execute(['user_id' => $userId]);
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }
}
