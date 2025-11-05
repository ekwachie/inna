<?php

/**
 * Example API Controller demonstrating JWT authentication usage
 * 
 * @author     Desmond Evans <desmond.evans@ucc.edu.gh>
 * @copyright   Copyright (C), 2025 Desmond Evans
 * @license     MIT LICENSE
 *              Refer to the LICENSE file distributed within the package.
 *
 * @todo PDO exception and error handling
 * @category    Database
 * @example
 * $this->query('INSERT INTO tb (col1, col2, col3) VALUES(?,?,?)', $var1, $var2, $var3);
 *
 *
 */

namespace app\Controllers;

use app\Core\DApiController;
use app\Core\Request;
use app\Models\User;
use app\Core\Utils\DUtil;

class ApiController extends DApiController
{
    /**
     * POST /api/login
     * Authenticates user and returns JWT token
     * 
     * Request Body (JSON):
     * {
     *   "username": "user@example.com",
     *   "password": "password123"
     * }
     * 
     * Response:
     * {
     *   "success": true,
     *   "data": {
     *     "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
     *     "user": {
     *       "id": 1,
     *       "username": "user@example.com",
     *       "name": "John Doe"
     *     }
     *   }
     * }
     */
    public function login(Request $request)
    {
        if (!$request->isPost()) {
            return $this->message(false, 'Method not allowed', 'ERR_METHOD');
        }

        // Get JSON body for API requests
        $jsonInput = file_get_contents('php://input');
        $body = json_decode($jsonInput, true) ?? $request->getBody();
        
        $username = $body['username'] ?? '';
        $password = $body['password'] ?? '';

        if (empty($username) || empty($password)) {
            return $this->message(false, 'Username and password are required', 'ERR_VALIDATION');
        }

        // Authenticate user (using your existing User model)
        $user = new User();
        try {
            $userData = $user->select(
                "SELECT id, username, email, concat(fname, ' ', lname) AS name, role FROM users WHERE username = :username",
                ['username' => $username]
            );

            if (empty($userData)) {
                return $this->message(false, 'Invalid credentials', 'ERR_AUTH');
            }

            $userRecord = $userData[0];
            
            // Verify password
            $stmt = $user->select(
                "SELECT password FROM users WHERE username = :username",
                ['username' => $username]
            );
            
            if (!DUtil::passVerify($password, $stmt[0]['password'])) {
                return $this->message(false, 'Invalid credentials', 'ERR_AUTH');
            }

            // Generate JWT token with user claims
            $roles = !empty($userRecord['role']) ? explode(',', $userRecord['role']) : ['user'];
            
            $token = $this->issueToken([
                'sub' => $userRecord['id'],           // Subject (user ID)
                'username' => $userRecord['username'],
                'email' => $userRecord['email'] ?? '',
                'roles' => $roles,                     // User roles for authorization
            ], 3600); // Token expires in 1 hour

            // Return token and user info
            return $this->message(true, [
                'token' => $token,
                'user' => [
                    'id' => $userRecord['id'],
                    'username' => $userRecord['username'],
                    'email' => $userRecord['email'] ?? '',
                    'name' => $userRecord['name'] ?? '',
                    'roles' => $roles
                ]
            ]);

        } catch (\Exception $e) {
            return $this->message(false, 'Authentication failed', 'ERR_AUTH');
        }
    }

    /**
     * GET /api/profile
     * Protected endpoint - requires valid JWT token
     * 
     * Headers:
     *   Authorization: Bearer {token}
     * 
     * Response:
     * {
     *   "success": true,
     *   "data": {
     *     "user_id": 1,
     *     "username": "user@example.com",
     *     "token_data": {...}
     *   }
     * }
     */
    public function profile(Request $request)
    {
        // This will automatically validate the JWT token
        // If invalid, it will return 401 and exit
        $payload = $this->requireJwt();

        // Access user data from token payload
        $userId = $payload['sub'] ?? null;
        $username = $payload['username'] ?? null;

        // Fetch user details from database
        $user = new User();
        try {
            $userData = $user->select(
                "SELECT id, username, email, concat(fname, ' ', lname) AS name, role FROM users WHERE id = :id",
                ['id' => $userId]
            );

            if (empty($userData)) {
                return $this->message(false, 'User not found', 'ERR_NOT_FOUND');
            }

            return $this->message(true, [
                'user' => $userData[0],
                'token_issued_at' => $payload['iat'] ?? null,
                'token_expires_at' => $payload['exp'] ?? null
            ]);

        } catch (\Exception $e) {
            return $this->message(false, 'Failed to fetch profile', 'ERR_SERVER');
        }
    }

    /**
     * GET /api/admin/users
     * Protected endpoint - requires JWT token AND admin role
     * 
     * Headers:
     *   Authorization: Bearer {token}
     * 
     * The token must contain 'admin' in the roles array
     */
    public function adminUsers(Request $request)
    {
        // Require JWT token AND admin role
        $payload = $this->requireJwt(['admin']);

        // Only admins can reach here
        $user = new User();
        try {
            $users = $user->select("SELECT id, username, email, concat(fname, ' ', lname) AS name, role FROM users LIMIT 100");
            
            return $this->message(true, [
                'users' => $users,
                'count' => count($users)
            ]);

        } catch (\Exception $e) {
            return $this->message(false, 'Failed to fetch users', 'ERR_SERVER');
        }
    }

    /**
     * POST /api/refresh
     * Refreshes JWT token - requires valid token
     * 
     * Headers:
     *   Authorization: Bearer {token}
     */
    public function refresh(Request $request)
    {
        // Validate existing token
        $payload = $this->requireJwt();

        // Issue new token with same claims
        $newToken = $this->issueToken([
            'sub' => $payload['sub'] ?? null,
            'username' => $payload['username'] ?? null,
            'email' => $payload['email'] ?? '',
            'roles' => $payload['roles'] ?? ['user'],
        ], 3600);

        return $this->message(true, [
            'token' => $newToken
        ]);
    }

    /**
     * GET /api/public
     * Public endpoint - no authentication required
     */
    public function public(Request $request)
    {
        return $this->message(true, [
            'message' => 'This is a public endpoint',
            'timestamp' => time()
        ]);
    }
}

