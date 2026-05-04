<?php

namespace app\Controllers;

use Core\Response;
use app\Models\UserModel;
use app\Services\MailService;

class User extends BaseController
{
    /**
     * Регистрация нового пользователя
     * 
     * @return Response JSON-ответ с результатом регистрации
     */
    public function register(): Response
    {
        $email = $this->request->post('email');
        $password = $this->request->post('password');

        if (!$email || !$password) {
            return $this->error('Email and password required', 400);
        }

        $userModel = new UserModel();

        if ($userModel->findByEmail($email)) {
            return $this->error('Email already exists', 409);
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $userId = $userModel->create([
            'email' => $email,
            'password' => $hashedPassword,
            'role' => 'user'
        ]);

        if ($userId) {
            return $this->json(['message' => 'User registered successfully', 'user_id' => $userId]);
        }
        return $this->error('Registration failed', 500);
    }

    /**
     * Получить список всех пользователей
     * 
     * @return Response JSON-ответ со списком пользователей
     */
    public function list(): Response
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $userModel = new UserModel();
        $users = $userModel->getAllPublic();
        return $this->json($users);
    }

    /**
     * Получить информацию о конкретном пользователе
     * 
     * @param int $id ID пользователя
     * @return Response JSON-ответ с данными пользователя или ошибкой 404
     */
    public function get($id): Response
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $userModel = new UserModel();
        $user = $userModel->getById($id);

        if (!$user) {
            return $this->error('User not found', 404);
        }

        return $this->json([
            'email' => $user['email'],
            'role' => $user['role']
        ]);
    }

    /**
     * Обновить профиль текущего пользователя
     * 
     * @return Response JSON-ответ с результатом обновления
     */
    public function update(): Response
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            return $this->error('Unauthorized', 401);
        }

        $userId = $_SESSION['user_id'];
        $email = $this->request->post('email');
        $password = $this->request->post('password');
        $data = [];

        if ($email) {
            $data['email'] = $email;
        }
        if ($password) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if (empty($data)) {
            return $this->error('No data to update', 400);
        }

        $userModel = new UserModel();

        if (isset($data['email'])) {
            $existingUser = $userModel->findByEmail($data['email']);
            if ($existingUser && $existingUser['id'] !== $userId) {
                return $this->error('Email already in use', 409);
            }
        }

        $updated = $userModel->update($userId, $data);

        if ($updated) {
            return $this->json(['message' => 'Profile updated successfully']);
        }
        return $this->error('Update failed', 500);
    }

    /**
     * Авторизация пользователя
     * 
     * @return Response JSON-ответ с данными пользователя или ошибкой
     */
    public function login(): Response
    {
        $email = $this->request->post('email');
        $password = $this->request->post('password');

        if (!$email || !$password) {
            return $this->error('Email and password required', 400);
        }

        $userModel = new UserModel();
        $user = $userModel->findByEmail($email);

        if (!$user) {
            return $this->error('User not found', 401);
        }

        if (!password_verify($password, $user['password'])) {
            return $this->error('Invalid password', 401);
        }

        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];

        return $this->json(['message' => 'Login successful', 'user' => $user]);
    }

    /**
     * Выход из системы (завершение сессии)
     * 
     * @return Response JSON-ответ об успешном выходе
     */
    public function logout(): Response
    {
        session_start();
        session_destroy();
        return $this->json(['message' => 'Logout successful']);
    }

    /**
     * Запрос на сброс пароля
     * 
     * @return Response JSON-ответ об отправке ссылки для сброса
     */
    public function resetPassword(): Response
    {
        $email = $this->request->post('email');

        if (!$email) {
            return $this->error('Email required', 400);
        }

        $userModel = new UserModel();
        $user = $userModel->findByEmail($email);

        if (!$user) {
            return $this->error('User not found', 404);
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $userModel->saveResetToken($email, $token, $expires);

        $mailService = new MailService();
        $sent = $mailService->sendResetLink($email, $token);

        if ($sent) {
            return $this->json(['message' => 'Reset link sent to email']);
        }
        return $this->error('Failed to send email', 500);
    }

    /**
     * Установка нового пароля по токену
     * 
     * @param string $token Токен сброса пароля
     * @return Response JSON-ответ о результате смены пароля
     */
    public function setNewPassword(string $token): Response
    {
        $newPassword = $this->request->post('password');

        if (!$newPassword) {
            return $this->error('Password required', 400);
        }

        $userModel = new UserModel();
        $user = $userModel->findByResetToken($token);

        if (!$user) {
            return $this->error('Invalid or expired token', 400);
        }

        $updated = $userModel->update($user['id'], [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
            'reset_token' => null,
            'reset_expires' => null
        ]);

        if ($updated) {
            return $this->json(['message' => 'Password updated successfully']);
        }
        return $this->error('Update failed', 500);
    }
    /**
     * Поиск пользователя по email
     * 
     * @param string $email
     * @return Response
     */
    public function search(string $email): Response
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $userModel = new UserModel();
        $user = $userModel->findByEmail($email);

        if (!$user) {
            return $this->error('User not found', 404);
        }

        return $this->json(['email' => $user['email'], 'role' => $user['role']]);
    }
}
