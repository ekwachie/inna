<?php
namespace app\Controllers;

use app\Core\ApiException;
use app\Core\DApiController;
use app\Core\Request;
use app\Core\Utils\DUtil;
use app\Models\User;

/** Starter endpoints for the bundled users migration; add application rate limiting before exposing login. */
class ApiController extends DApiController
{
    public function login(Request $request)
    {
        $body = $request->getJsonBody();
        if (!is_string($body['username'] ?? null) || !is_string($body['password'] ?? null)
            || $body['username'] === '' || $body['password'] === '') throw new ApiException('VALIDATION_ERROR', 'Username and password are required.', 422);
        $rows = (new User())->select('SELECT id, username, email, firstname, lastname, role, password, is_active, is_suspended FROM users WHERE username = :username', ['username' => $body['username']]);
        $user = $rows[0] ?? null;
        if (!$user || empty($user['is_active']) || !empty($user['is_suspended']) || !DUtil::passVerify($body['password'], $user['password'])) {
            throw new ApiException('INVALID_CREDENTIALS', 'Invalid credentials.', 401);
        }
        $roles = array_values(array_filter(array_map('trim', explode(',', $user['role'] ?? 'user'))));
        $this->success(['token' => $this->issueToken(['sub' => (string) $user['id'], 'username' => $user['username'], 'roles' => $roles]),
            'user' => ['id' => $user['id'], 'username' => $user['username'], 'email' => $user['email'], 'roles' => $roles]]);
    }

    public function profile(Request $request)
    {
        $claims = $this->requireJwt();
        $rows = (new User())->select('SELECT id, username, email, firstname, lastname, role, is_active, is_suspended FROM users WHERE id = :id', ['id' => $claims['sub'] ?? '']);
        $user = $rows[0] ?? null;
        if (!$user || empty($user['is_active']) || !empty($user['is_suspended'])) throw new ApiException('UNAUTHENTICATED', 'Account is unavailable.', 401);
        $this->success(['user' => $user]);
    }

    public function adminUsers(Request $request)
    {
        $claims = $this->requireJwt(['admin']);
        $actor = (new User())->select('SELECT role, is_active, is_suspended FROM users WHERE id = :id', ['id' => $claims['sub'] ?? ''])[0] ?? [];
        if (empty($actor['is_active']) || !empty($actor['is_suspended']) || !in_array('admin', array_map('trim', explode(',', $actor['role'] ?? '')), true)) throw new ApiException('FORBIDDEN', 'Administrator access is required.', 403);
        $users = (new User())->select('SELECT id, username, email, firstname, lastname, role FROM users LIMIT 100');
        $this->success(['users' => $users, 'count' => count($users)]);
    }

    public function refresh(Request $request)
    {
        $claims = $this->requireJwt();
        $actor = (new User())->select('SELECT id, username, role, is_active, is_suspended FROM users WHERE id = :id', ['id' => $claims['sub'] ?? ''])[0] ?? [];
        if (empty($actor['is_active']) || !empty($actor['is_suspended'])) throw new ApiException('UNAUTHENTICATED', 'Account is unavailable.', 401);
        $this->success(['token' => $this->issueToken(['sub' => (string) $actor['id'], 'username' => $actor['username'], 'roles' => array_map('trim', explode(',', $actor['role'] ?? 'user'))])]);
    }

    public function public(Request $request)
    {
        $this->success(['message' => 'This is a public endpoint', 'timestamp' => time()]);
    }
}

