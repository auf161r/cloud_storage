<?php

namespace app\Models;

use Core\Db;

class DirectoryModel
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Db::getInstance()->getConnection();
    }

    /**
     * Создать новую папку
     * 
     * @param array $data Данные папки (user_id, name, parent_id, path)
     * @return int|false ID созданной папки или false
     */
    public function create(array $data): int|false
    {
        $sql = 'INSERT INTO directories (user_id, name, parent_id, path) VALUES (:user_id, :name, :parent_id, :path)';
        $userId = $data['user_id'];
        $name = $data['name'];
        $parentId = $data['parent_id'];
        $path = $data['path'];
        $statement = $this->db->prepare($sql);
        $result = $statement->execute(['user_id' => $userId, 'name' => $name, 'parent_id' => $parentId, 'path' => $path]);

        if ($result) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Найти папку по ID
     * 
     * @param int $id ID папки
     * @return array|false Данные папки или false
     */
    public function getById(int $id): array|false
    {
        $sql = 'SELECT * FROM directories WHERE id = :id';

        $statement = $this->db->prepare($sql);
        $statement->execute(['id' => $id]);
        return $statement->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Получить все папки пользователя
     * 
     * @param int $userId ID пользователя
     * @return array Массив папок
     */
    public function getByUserId(int $userId): array
    {
        $sql = 'SELECT * FROM directories WHERE user_id = :user_id';
        $statement = $this->db->prepare($sql);
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Получить содержимое папки (файлы + подпапки)
     * 
     * @param int $id ID папки
     * @return array Массив с ключами 'directories' и 'files'
     */
    public function getContents(int $id): array
    {
        $sql = 'SELECT * FROM directories WHERE parent_id = :id';
        $directoriesStmt = $this->db->prepare($sql);
        $directoriesStmt->execute(['id' => $id]);
        $directories = $directoriesStmt->fetchAll(\PDO::FETCH_ASSOC);

        $sql = 'SELECT * FROM files WHERE directory_id = :id';
        $filesStmt = $this->db->prepare($sql);
        $filesStmt->execute(['id' => $id]);
        $files = $filesStmt->fetchAll(\PDO::FETCH_ASSOC);

        $data = ['directories' => $directories, 'files' => $files];

        return $data;
    }

    /**
     * Переименовать папку
     * 
     * @param int $id ID папки
     * @param string $newName Новое имя
     * @return bool true при успехе
     */
    public function updateName(int $id, string $newName): bool
    {
        $sql = 'UPDATE directories SET name = :name WHERE id = :id';
        $statement = $this->db->prepare($sql);
        return $statement->execute(['name' => $newName, 'id' => $id]);
    }

    /**
     * Удалить папку
     * 
     * @param int $id ID папки
     * @return bool true при успехе
     */
    public function delete(int $id): bool
    {
        $sql = 'DELETE FROM directories WHERE id = :id';
        $statement = $this->db->prepare($sql);
        return $statement->execute(['id' => $id]);
    }

    /**
     * Получить все подпапки по parent_id
     * 
     * @param int $parentId ID родительской папки
     * @return array Массив подпапок
     */
    public function getByParentId(int $parentId): array
    {
        $sql = 'SELECT * FROM directories WHERE parent_id = :parent_id';
        $statement = $this->db->prepare($sql);
        $statement->execute(['parent_id' => $parentId]);
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Обновить путь папки
     * 
     * @param int $id ID папки
     * @param string $newPath Новый путь
     * @return bool true при успехе
     */
    public function updatePath(int $id, string $newPath): bool
    {
        $sql = 'UPDATE directories SET path = :path WHERE id = :id';
        $statement = $this->db->prepare($sql);
        return $statement->execute(['id' => $id, 'path' => $newPath]);
    }
}
