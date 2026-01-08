<?php
/**
 * @author      Payperlez Team <inna@payperlez.org>
 * @copyright   Copyright (C), 2019. Payperlez Inc.
 * @license     MIT LICENSE (https://opensource.org/licenses/MIT)
 *              Refer to the LICENSE file distributed within the package.
 *
 * @todo PDO exception and error handling
 * @category    Database
 * @example
 * $this->query('INSERT INTO tb (col1, col2, col3) VALUES(?,?,?)', $var1, $var2, $var3);
 *
 *
 */
namespace app\Core;

use app\Core\Middlewares\BaseMiddleware;

class Controller
{
    /**
     * @var \app\Core\Middlewares\BaseMiddleware[];
     */
    protected array $middlewares = [];
    public string $action = '';

    /** 
     * render page view
     * */
    public function render($view, $params = [])
    {
        return Application::$app->router->render($view, $params);
    }
    /**
     * redirect to url
     */
    public function redirect()
    {
        return Application::$app->response->redirect('/');
    }

    public function setFlash($type, $msg)
    {
        // Map type to Tailwind classes
        $typeClasses = [
            'error' => 'bg-red-50 text-red-800 border-red-200',
            'danger' => 'bg-red-50 text-red-800 border-red-200',
            'success' => 'bg-green-50 text-green-800 border-green-200',
            'warning' => 'bg-yellow-50 text-yellow-800 border-yellow-200',
            'info' => 'bg-blue-50 text-blue-800 border-blue-200',
        ];
        
        $classes = $typeClasses[$type] ?? $typeClasses['info'];
        $iconColor = in_array($type, ['error', 'danger']) ? 'text-red-400' : 
                     ($type === 'success' ? 'text-green-400' : 
                     ($type === 'warning' ? 'text-yellow-400' : 'text-blue-400'));
        
        return '
        <div class="fixed top-4 right-4 z-50 max-w-md w-full" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-full" x-transition:enter-end="opacity-100 transform translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-x-0" x-transition:leave-end="opacity-0 transform translate-x-full">
            <div class="' . $classes . ' border rounded-lg shadow-lg p-4 flex items-start gap-3">
                <div class="flex-shrink-0">
                    ' . ($type === 'error' || $type === 'danger' ? '
                    <svg class="w-5 h-5 ' . $iconColor . '" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    ' : ($type === 'success' ? '
                    <svg class="w-5 h-5 ' . $iconColor . '" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    ' : ($type === 'warning' ? '
                    <svg class="w-5 h-5 ' . $iconColor . '" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    ' : '
                    <svg class="w-5 h-5 ' . $iconColor . '" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    '))) . '
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium">' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</p>
                </div>
                <button @click="show = false" class="flex-shrink-0 text-gray-400 hover:text-gray-600 focus:outline-none">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        </div>
        ';
    }


    public function middleware(BaseMiddleware $middleware)
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * 
     * @return array
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * 
     * @param array $middlewares 
     * @return Controller
     */
    public function setMiddlewares(array $middlewares): self
    {
        $this->middlewares = $middlewares;
        return $this;
    }

    /**
     * for APi resources
     */
    public function apiMessage($status, $message, $errorCode = null)
    {
        return Application::$app->api->message($status, $message, $errorCode);
    }
}
