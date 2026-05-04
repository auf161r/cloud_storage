<?php

namespace app\Controllers;

use Core\Response;

abstract class BaseController
{
    protected ?\Core\Request $request = null;

    /**
     * Установить объект запроса
     * 
     * @param \Core\Request $request Объект запроса
     * @return self
     */
    public function setRequest(\Core\Request $request): self
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Отправить JSON-ответ
     * 
     * @param mixed $data Данные для отправки
     * @param int $statusCode HTTP-статус код
     * @return Response
     */
    protected function json($data, int $statusCode = 200): Response
    {
        $response = new Response();
        return $response->setStatusCode($statusCode)->json($data);
    }

    /**
     * Отправить JSON-ответ с ошибкой
     * 
     * @param string $message Текст ошибки
     * @param int $statusCode HTTP-статус код
     * @return Response
     */
    protected function error(string $message, int $statusCode = 400): Response
    {
        return $this->json(['error' => $message], $statusCode);
    }

    /**
     * Проверить, является ли текущий пользователь администратором
     * 
     * @return bool true если админ, иначе false
     */
    protected function checkAdmin(): bool
    {
        session_start();
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    /**
     * Проверить права администратора и вернуть ошибку при их отсутствии
     * 
     * @return Response|null null если админ, иначе Response с ошибкой 403
     */
    protected function requireAdmin(): ?Response
    {
        if (!$this->checkAdmin()) {
            return $this->error('Forbidden: admin only', 403);
        }
        return null;
    }

    /**
     * Проверить, авторизован ли пользователь
     * 
     * @return bool true если авторизован, иначе false
     */
    protected function isAuthenticated(): bool
    {
        session_start();
        return isset($_SESSION['user_id']);
    }

    /**
     * Проверить авторизацию и вернуть ошибку при её отсутствии
     * 
     * @return Response|null null если авторизован, иначе Response с ошибкой 401
     */
    protected function requireAuth(): ?Response
    {
        if (!$this->isAuthenticated()) {
            return $this->error('Unauthorized', 401);
        }
        return null;
    }
}
