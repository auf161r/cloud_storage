<?php

namespace app\Controllers;

use app\Controllers\BaseController;
use app\Models\DirectoryModel;
use Core\Response;
use app\Models\FileModel;
use app\Models\ShareModel;
use app\Models\UserModel;

class File extends BaseController
{
    /**
     * Получить список файлов текущего пользователя
     * 
     * @return Response JSON-ответ со списком файлов
     */
    public function list(): Response
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $userId = $_SESSION['user_id'];
        $userDir = __DIR__ . '/../../storage/user_' . $userId . '/';

        if (!is_dir($userDir)) {
            mkdir($userDir, 0777, true);
        }

        $fileModel = new FileModel();

        $myFiles = $fileModel->getByUserId($userId);
        $sharedFiles = $fileModel->getSharedWithUser($userId);
        $allFiles = array_merge($myFiles, $sharedFiles);
        return $this->json(['files' => array_values($allFiles)]);
    }

    /**
     * Скачать файл
     * 
     * @param int $id ID файла
     * @return Response JSON-ответ с ошибкой или вывод файла
     */
    public function get(int $id): Response
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $fileModel = new FileModel();
        $file = $fileModel->getById($id);

        if (!$file) {
            return $this->error('File not found', 404);
        }

        if ($file['user_id'] === $_SESSION['user_id']) {
        } else {
            $shareModel = new ShareModel();
            if (!$shareModel->checkAccess($id, $_SESSION['user_id'])) {
                return $this->error('Forbidden', 403);
            }
        }

        $fullPath = __DIR__ . '/../../' . $file['path'];

        if (!file_exists($fullPath)) {
            return $this->error('File not found on disk', 404);
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"; filename*=UTF-8\'\'' . rawurlencode($file['original_name']));
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    /**
     * Загрузить новый файл
     * 
     * @return Response JSON-ответ о результате загрузки
     */
    public function add(): Response
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $file = $this->request->getFiles()['file'] ?? null;

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return $this->error('File upload failed', 400);
        }

        $config = parse_ini_file(__DIR__ . '/../../.env');
        $maxSize = $config['UPLOAD_MAX_SIZE'] ?? 2 * 1024 * 1024 * 1024;

        if ($file['size'] > $maxSize) {
            return $this->error('File size exceeds 2GB limit', 400);
        }

        $userId = $_SESSION['user_id'];
        $originalName = $file['name'];
        $size = $file['size'];
        $directoryId = $this->request->post('directory_id') ?? null;
        $fileName = time() . '_' . bin2hex(random_bytes(4));
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newName = $fileName . '.' . $extension;

        if ($directoryId) {
            $directoryModel = new DirectoryModel();
            $parentDir = $directoryModel->getById($directoryId);
            if (!$parentDir) {
                return $this->error('Parent directory not found', 404);
            }
            $path = $parentDir['path'] . '/' . $newName;
        } else {
            $path = 'storage/user_' . $userId . '/' . $newName;
        }

        $destination = __DIR__ . '/../../' . $path;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $data = [
                'user_id' => $userId,
                'directory_id' => $directoryId,
                'name' => $newName,
                'original_name' => $originalName,
                'path' => $path,
                'size' => $size,
            ];
            $fileModel = new FileModel();
            $fileId = $fileModel->create($data);

            return $this->json([
                'message' => 'File uploaded successfully',
                'file_id' => $fileId,
                'file' => $newName
            ]);
        } else {
            return $this->error('Failed to save file', 500);
        }
    }

    /**
     * Переименовать файл
     * 
     * @return Response JSON-ответ о результате переименования
     */
    public function rename(): Response
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $id = $this->request->post('id');
        $newOriginalName = $this->request->post('new_name');

        $fileModel = new FileModel();
        $file = $fileModel->getById($id);

        if (!$file) {
            return $this->error('File not found', 404);
        }

        if ($file['user_id'] !== $_SESSION['user_id']) {
            return $this->error('Forbidden', 403);
        }

        if (empty($newOriginalName) || preg_match('/[\/\\\\:*?"<>|]/', $newOriginalName)) {
            return $this->error('Invalid characters in filename', 400);
        }

        $result = $fileModel->updateOriginalName($id, $newOriginalName);

        if ($result) {
            return $this->json(['message' => 'File renamed successfully']);
        }

        return $this->error('Failed to rename file', 500);
    }

    /**
     * Удалить файл
     * 
     * @param int $id ID файла
     * @return Response JSON-ответ о результате удаления
     */
    public function remove(int $id): Response
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $fileModel = new FileModel();
        $file = $fileModel->getById($id);

        if (!$file) {
            return $this->error('File not found', 404);
        }

        if ($file['user_id'] !== $_SESSION['user_id']) {
            return $this->error('Forbidden', 403);
        }

        $fullPath = __DIR__ . '/../../' . $file['path'];

        if (!file_exists($fullPath)) {
            return $this->error('File not found on disk', 404);
        }

        unlink($fullPath);
        $fileModel->delete($id);

        return $this->json(['message' => 'File deleted successfully']);
    }

    /**
     * Создать новую папку
     * 
     * @return Response JSON-ответ о результате создания папки
     */
    public function addDirectory(): Response
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $name = $this->request->post('name');
        $userId = $_SESSION['user_id'];
        $parentId = $this->request->post('parent_id') ?? null;

        if (empty($name) || preg_match('/[\/\\\\:*?"<>|]/', $name)) {
            return $this->error('Invalid characters in directory name', 400);
        }

        $directoryModel = new DirectoryModel();

        if ($parentId) {
            $parentDir = $directoryModel->getById($parentId);
            if (!$parentDir) {
                return $this->error('Parent directory not found', 404);
            }
            $path = $parentDir['path'] . '/' . $name;
        } else {
            $path = 'storage/user_' . $userId . '/' . $name;
        }

        $data = ['user_id' => $userId, 'name' => $name, 'parent_id' => $parentId, 'path' => $path];

        $fullPath = __DIR__ . '/../../' . $path;
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0777, true);
        }

        $directoryId = $directoryModel->create($data);

        return $this->json([
            'message' => 'Directory created successfully',
            'directory_id' => $directoryId
        ]);
    }

    /**
     * Переименовать папку
     * 
     * @return Response JSON-ответ о результате переименования папки
     */
    public function renameDirectory(): Response
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $newName = $this->request->post('new_name');
        $id = $this->request->post('id');

        $directoryModel = new DirectoryModel();
        $directory = $directoryModel->getById($id);

        if (!$directory) {
            return $this->error('Directory not found', 404);
        }

        if ($directory['user_id'] !== $_SESSION['user_id']) {
            return $this->error('Forbidden', 403);
        }

        if (empty($newName) || preg_match('/[\/\\\\:*?"<>|]/', $newName)) {
            return $this->error('Invalid characters in directory name', 400);
        }

        $oldFullPath = __DIR__ . '/../../' . $directory['path'];
        $newFullPath = dirname($oldFullPath) . '/' . $newName;

        if (!rename($oldFullPath, $newFullPath)) {
            return $this->error('Failed to rename directory on disk', 500);
        }

        $newPath = dirname($directory['path']) . '/' . $newName;
        $directoryModel->updatePath($id, $newPath);

        $directoryModel->updateName($id, $newName);

        $this->updateChildPaths($directoryModel, $id, $directory['path'], $newPath);

        return $this->json(['message' => 'Directory renamed successfully']);
    }

    /**
     * Рекурсивно обновить пути у всех вложенных элементов
     */
    private function updateChildPaths(DirectoryModel $directoryModel, int $parentId, string $oldParentPath, string $newParentPath): void
    {
        $subdirs = $directoryModel->getByParentId($parentId);

        foreach ($subdirs as $subdir) {

            $oldSubPath = $subdir['path'];
            $newSubPath = str_replace($oldParentPath, $newParentPath, $oldSubPath);
            $directoryModel->updatePath($subdir['id'], $newSubPath);


            $this->updateChildPaths($directoryModel, $subdir['id'], $oldSubPath, $newSubPath);
        }


        $fileModel = new FileModel();
        $files = $fileModel->getByDirectoryId($parentId);

        foreach ($files as $file) {
            $oldFilePath = $file['path'];
            $newFilePath = str_replace($oldParentPath, $newParentPath, $oldFilePath);
            $fileModel->updatePath($file['id'], $newFilePath);
        }
    }

    /**
     * Получить содержимое папки
     * 
     * @param int $id ID папки
     * @return Response JSON-ответ с содержимым папки
     */
    public function getDirectory(int $id): Response
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $directoryModel = new DirectoryModel();
        $directory = $directoryModel->getById($id);

        if (!$directory) {
            return $this->error('Directory not found', 404);
        }

        if ($directory['user_id'] !== $_SESSION['user_id']) {
            return $this->error('Forbidden', 403);
        }

        $contents = $directoryModel->getContents($id);

        return $this->json($contents);
    }

    /**
     * Удалить папку
     * 
     * @param int $id ID папки
     * @return Response JSON-ответ о результате удаления папки
     */
    public function deleteDirectory(int $id): Response
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $directoryModel = new DirectoryModel();
        $directory = $directoryModel->getById($id);

        if (!$directory) {
            return $this->error('Directory not found', 404);
        }

        if ($directory['user_id'] !== $_SESSION['user_id']) {
            return $this->error('Forbidden', 403);
        }

        $contents = $directoryModel->getContents($id);

        if (!empty($contents['directories']) || !empty($contents['files'])) {
            return $this->error('Cannot delete non-empty directory', 400);
        }

        $fullPath = __DIR__ . '/../../' . $directory['path'];
        if (is_dir($fullPath)) {
            rmdir($fullPath);
        }

        $result = $directoryModel->delete($id);

        if ($result) {
            return $this->json(['message' => 'Directory deleted successfully']);
        }

        return $this->error('Failed to delete directory', 500);
    }

    /**
     * Получить список пользователей, имеющих доступ к файлу
     * 
     * @param int $id ID файла
     * @return Response JSON-ответ со списком пользователей
     */
    public function shareList(int $id): Response
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $fileModel = new FileModel();
        $file = $fileModel->getById($id);

        if (!$file) {
            return $this->error('File not found', 404);
        }

        if ($file['user_id'] !== $_SESSION['user_id']) {
            return $this->error('Forbidden', 403);
        }

        $sharedModel = new ShareModel();

        $users = $sharedModel->getSharedUsers($id);

        return $this->json($users);
    }

    /**
     * Предоставить доступ к файлу другому пользователю
     * 
     * @param int $id ID файла
     * @param int $userId ID пользователя, которому даётся доступ
     * @return Response JSON-ответ о результате
     */
    public function shareAdd(int $id, int $userId): Response
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $fileModel = new FileModel();
        $file = $fileModel->getById($id);

        if (!$file) {
            return $this->error('File not found', 404);
        }

        if ($file['user_id'] !== $_SESSION['user_id']) {
            return $this->error('Forbidden', 403);
        }

        $userModel = new UserModel();

        $user = $userModel->getById($userId);

        if (!$user) {
            return $this->error('User not found', 404);
        }

        $sharedModel = new ShareModel();

        $result = $sharedModel->share($id, $userId, $_SESSION['user_id']);

        if ($result) {
            return $this->json(['message' => 'File shared successfully']);
        }

        return $this->error('Failed to file share');
    }

    /**
     * Отозвать доступ к файлу у пользователя
     * 
     * @param int $id ID файла
     * @param int $userId ID пользователя
     * @return Response JSON-ответ о результате
     */
    public function shareRemove(int $id, int $userId): Response
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $fileModel = new FileModel();
        $file = $fileModel->getById($id);

        if (!$file) {
            return $this->error('File not found', 404);
        }

        if ($file['user_id'] !== $_SESSION['user_id']) {
            return $this->error('Forbidden', 403);
        }

        $userModel = new UserModel();

        $user = $userModel->getById($userId);

        if (!$user) {
            return $this->error('User not found', 404);
        }

        $sharedModel = new ShareModel();

        $result = $sharedModel->unshare($id, $userId);

        if ($result) {
            return $this->json(['message' => 'File unshare successfully']);
        }
        return $this->error('Failed to file unshare');
    }
}
