<?php

namespace app\Models;

use Core\Db;

class ShareModel
{

    private \PDO $db;

    public function __construct()
    {
        $this->db = Db::getInstance()->getConnection();
    }

    public function getSharedUsers(int $fileId): array
    {
        $sql = 'SELECT user.id, user.email, user.role FROM file_shares JOIN user ON user.id = file_shares.user_id WHERE file_shares.file_id = :file_id';


        $statement = $this->db->prepare($sql);
        $statement->execute(['file_id' => $fileId]);

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function share(int $fileId, int $userId, int $sharedBy): bool
    {
        $sql = 'INSERT INTO file_shares (file_id, user_id, shared_by) VALUES (:file_id, :user_id, :shared_by)';

        $statement = $this->db->prepare($sql);

        return $statement->execute(['file_id' => $fileId, 'user_id' => $userId, 'shared_by' => $sharedBy]);
    }

    public function unshare(int $fileId, int $userId): bool
    {
        $sql = 'DELETE FROM file_shares WHERE file_id = :file_id AND user_id = :user_id';

        $statement = $this->db->prepare($sql);
        return $statement->execute(['file_id' => $fileId, 'user_id' => $userId]);
    }

    public function checkAccess(int $fileId, int $userId): bool
    {
        $sql = 'SELECT COUNT(*) as count FROM file_shares WHERE file_id = :file_id AND user_id = :user_id';

        $statement = $this->db->prepare($sql);

        $statement->execute(['file_id' => $fileId, 'user_id' => $userId]);

        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        return $result['count'] > 0;
    }
}
