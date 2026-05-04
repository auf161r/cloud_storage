<?php

namespace app\Controllers;

use app\Models\UserModel;
use app\Controllers\BaseController;

class Admin extends BaseController
{
    /**
     * Получить список всех пользователей (только для администратора)
     * 
     * @return \Core\Response JSON-ответ со списком пользователей
     */
    public function list(): \Core\Response
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $userModel = new UserModel();
        $users = $userModel->getAll();
        return $this->json($users);
    }

    /**
     * Получить информацию о конкретном пользователе (только для администратора)
     * 
     * @param int $id ID пользователя
     * @return \Core\Response JSON-ответ с данными пользователя или ошибкой 404
     */
    public function get(int $id): \Core\Response
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $userModel = new UserModel();
        $user = $userModel->getById($id);

        if ($user) {
            return $this->json($user);
        } else {
            return $this->error('User not found', 404);
        }
    }

    /**
     * Удалить пользователя (только для администратора)
     * 
     * @param int $id ID пользователя
     * @return \Core\Response JSON-ответ о результате удаления
     */
    public function delete(int $id): \Core\Response
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        if ($id === $_SESSION['user_id']) {
            return $this->error('Cannot delete your own account', 400);
        }

        $userModel = new UserModel();
        $user = $userModel->getById($id);

        if (!$user) {
            return $this->error('User not found', 404);
        }

        $deleted = $userModel->delete($id);

        if ($deleted) {
            return $this->json(['message' => 'User deleted successfully']);
        } else {
            return $this->error('Failed to delete user', 500);
        }
    }

    /**
     * Обновить данные пользователя (только для администратора)
     * 
     * @param int $id ID пользователя
     * @return \Core\Response JSON-ответ о результате обновления
     */
    public function update(int $id): \Core\Response
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $data = [];
        $email = $this->request->post('email');
        $password = $this->request->post('password');
        $role = $this->request->post('role');

        if ($email) {
            $data['email'] = $email;
        }

        if ($password) {
            $data['password'] = $password;
        }

        if ($role) {
            $data['role'] = $role;
        }

        if (empty($data)) {
            return $this->error('No data to update', 400);
        }

        $userModel = new UserModel();

        if (isset($data['email'])) {
            $existingUser = $userModel->findByEmail($data['email']);
            if ($existingUser && $existingUser['id'] !== $id) {
                return $this->error('Email already in use', 409);
            }
        }
        
        $user = $userModel->getById($id);

        if (!$user) {
            return $this->error('User not found', 404);
        }

        $updated = $userModel->update($id, $data);

        if ($updated) {
            return $this->json(['message' => 'User updated successfully']);
        } else {
            return $this->error('Failed to update user', 500);
        }
    }
}
