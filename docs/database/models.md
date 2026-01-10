# Models

Models in Inna provide an object-oriented way to work with your database tables. While the framework doesn't include a full ORM, you can create model classes to encapsulate database logic.

## Creating Models

### Basic Model

Create a model in `app/Models/`:

```php
<?php

namespace app\Models;

use app\Core\Application;

class User
{
    public static function find($id)
    {
        $db = Application::$app->db;
        $result = $db->select(
            "SELECT * FROM users WHERE id = :id",
            ['id' => $id]
        );
        
        return !empty($result) ? $result[0] : null;
    }
    
    public static function all()
    {
        $db = Application::$app->db;
        return $db->select("SELECT * FROM users");
    }
    
    public static function create($data)
    {
        $db = Application::$app->db;
        return $db->insert('users', $data);
    }
    
    public static function update($id, $data)
    {
        $db = Application::$app->db;
        return $db->update(
            'users',
            $data,
            'id = :id',
            ['id' => $id]
        );
    }
    
    public static function delete($id)
    {
        $db = Application::$app->db;
        return $db->delete('users', 'id = :id', ['id' => $id]);
    }
}
```

## Using Models

### In Controllers

```php
use app\Models\User;

class UserController extends Controller
{
    public function show(Request $request, Response $response)
    {
        $id = $request->getRouteParam('id');
        $user = User::find($id);
        
        if (!$user) {
            return $this->render('404');
        }
        
        return $this->render('users/show', ['user' => $user]);
    }
    
    public function create(Request $request, Response $response)
    {
        if ($request->isPost()) {
            $body = $request->getBody();
            
            $id = User::create([
                'name' => $body['name'],
                'email' => $body['email'],
                'password' => password_hash($body['password'], PASSWORD_DEFAULT)
            ]);
            
            return $response->redirect('/user/' . $id);
        }
        
        return $this->render('users/create');
    }
}
```

## Advanced Model Patterns

### Model with Instance Methods

```php
<?php

namespace app\Models;

use app\Core\Application;

class Post
{
    public $id;
    public $title;
    public $content;
    public $user_id;
    public $created_at;
    
    public static function find($id)
    {
        $db = Application::$app->db;
        $result = $db->select(
            "SELECT * FROM posts WHERE id = :id",
            ['id' => $id]
        );
        
        if (empty($result)) {
            return null;
        }
        
        $post = new self();
        foreach ($result[0] as $key => $value) {
            $post->$key = $value;
        }
        
        return $post;
    }
    
    public function save()
    {
        $db = Application::$app->db;
        
        if ($this->id) {
            // Update
            return $db->update(
                'posts',
                [
                    'title' => $this->title,
                    'content' => $this->content
                ],
                'id = :id',
                ['id' => $this->id]
            );
        } else {
            // Insert
            $this->id = $db->insert('posts', [
                'title' => $this->title,
                'content' => $this->content,
                'user_id' => $this->user_id
            ]);
            return $this->id;
        }
    }
    
    public function delete()
    {
        $db = Application::$app->db;
        return $db->delete('posts', 'id = :id', ['id' => $this->id]);
    }
    
    public function user()
    {
        return User::find($this->user_id);
    }
}
```

### Using Instance Models

```php
// Create
$post = new Post();
$post->title = 'My Post';
$post->content = 'Content here';
$post->user_id = 1;
$post->save();

// Update
$post = Post::find(1);
$post->title = 'Updated Title';
$post->save();

// Delete
$post = Post::find(1);
$post->delete();

// Relationships
$post = Post::find(1);
$user = $post->user();
```

## Query Scopes

Add reusable query methods:

```php
class Post extends Model
{
    public static function published()
    {
        $db = Application::$app->db;
        return $db->select(
            "SELECT * FROM posts WHERE status = :status",
            ['status' => 'published']
        );
    }
    
    public static function byUser($userId)
    {
        $db = Application::$app->db;
        return $db->select(
            "SELECT * FROM posts WHERE user_id = :user_id",
            ['user_id' => $userId]
        );
    }
    
    public static function recent($limit = 10)
    {
        $db = Application::$app->db;
        return $db->select(
            "SELECT * FROM posts ORDER BY created_at DESC LIMIT :limit",
            ['limit' => $limit]
        );
    }
}
```

## Relationships

### One-to-Many

```php
class User
{
    public function posts()
    {
        $db = Application::$app->db;
        return $db->select(
            "SELECT * FROM posts WHERE user_id = :user_id",
            ['user_id' => $this->id]
        );
    }
}

// Usage
$user = User::find(1);
$posts = $user->posts();
```

### Many-to-One

```php
class Post
{
    public function user()
    {
        return User::find($this->user_id);
    }
}

// Usage
$post = Post::find(1);
$user = $post->user();
```

## Validation with Models

Combine models with the validation system:

```php
use app\Core\Model;

class User extends Model
{
    public static function validate($data)
    {
        $validation = new Model();
        
        $validation->name('name')->value($data['name'] ?? '')
            ->required()
            ->min(3)
            ->max(100);
        
        $validation->name('email')->value($data['email'] ?? '')
            ->required()
            ->pattern('email');
        
        $validation->name('password')->value($data['password'] ?? '')
            ->required()
            ->min(8);
        
        return $validation;
    }
}

// Usage
$validation = User::validate($request->getBody());
if (!$validation->isSuccess()) {
    $errors = $validation->getErrors();
    // Handle errors
}
```

## Best Practices

1. **Keep models focused**: One model per table
2. **Use static methods**: For simple CRUD operations
3. **Use instance methods**: When you need object state
4. **Encapsulate logic**: Put database logic in models, not controllers
5. **Reuse code**: Create base model classes for common functionality

## Example: Complete Model

```php
<?php

namespace app\Models;

use app\Core\Application;

class Product
{
    public static function find($id)
    {
        $db = Application::$app->db;
        $result = $db->select(
            "SELECT * FROM products WHERE id = :id",
            ['id' => $id]
        );
        return !empty($result) ? $result[0] : null;
    }
    
    public static function all()
    {
        $db = Application::$app->db;
        return $db->select("SELECT * FROM products ORDER BY name");
    }
    
    public static function active()
    {
        $db = Application::$app->db;
        return $db->select(
            "SELECT * FROM products WHERE status = :status",
            ['status' => 'active']
        );
    }
    
    public static function create($data)
    {
        $db = Application::$app->db;
        return $db->insert('products', $data);
    }
    
    public static function update($id, $data)
    {
        $db = Application::$app->db;
        return $db->update(
            'products',
            $data,
            'id = :id',
            ['id' => $id]
        );
    }
    
    public static function delete($id)
    {
        $db = Application::$app->db;
        return $db->delete('products', 'id = :id', ['id' => $id]);
    }
    
    public static function search($query)
    {
        $db = Application::$app->db;
        return $db->select(
            "SELECT * FROM products 
             WHERE name LIKE :query OR description LIKE :query",
            ['query' => "%$query%"]
        );
    }
}
```

## Next Steps

- [Database Basics](basics.md) - Learn about database operations
- [Validation](/utilities/validation.md) - Learn about validation

