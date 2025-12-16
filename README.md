# PHP_Laravel12_Image_Upload_Using_SummerNote



---

## Step 1: Install Laravel 12

This step is optional.  
If you have not created a Laravel application, run:

```
composer create-project laravel/laravel example-app
```

Explanation:  
Creates a fresh Laravel 12 project with default configuration.

# Now Setup Database structure for .env file
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your databse name
DB_USERNAME=root
DB_PASSWORD=
```

---

## Step 2: Create Posts Table and Model

Create migration:

```
php artisan make:migration create_posts_table
```

### Migration File  
`database/migrations/2024_02_17_133331_create_posts_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

Run migration:

```
php artisan migrate
```

---

### Create Post Model
```
php artisan make:Model Post
```
`app/Models/Post.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Post extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'body'
    ];

    /**
     * Automatically process body content when saving (mutator)
     * Converts base64 images in HTML content to physical files
     */
    protected function body(): Attribute
    {
        return Attribute::make(
            // Only runs when setting the body attribute (saving/updating)
            set: fn (string $value) => $this->makeBodyContent($value),
        );
    }

    /**
     * Process HTML body content to handle embedded base64 images
     * 
     * @param string $content HTML content containing images
     * @return string Processed HTML with uploaded images
     */
    public function makeBodyContent($content)
    {
        // Create DOMDocument to parse HTML content safely
        $dom = new \DomDocument();

        // Suppress HTML parsing warnings/errors
        libxml_use_internal_errors(true);

        // Load HTML without adding extra tags
        $dom->loadHtml($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        // Get all <img> elements from the HTML
        $imageFile = $dom->getElementsByTagName('img');

        // Path where images will be uploaded (public/uploads/)
        $uploadPath = public_path('uploads');

        // Create uploads directory if it doesn't exist
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // Loop through each image tag
        foreach ($imageFile as $item => $image) {

            $src = $image->getAttribute('src');

            // Only process base64 images
            if (!str_starts_with($src, 'data:image')) {
                continue;
            }

            // Extract base64 data
            list($type, $data) = explode(';', $src);
            list(, $data) = explode(',', $data);

            // Decode base64 image
            $imageData = base64_decode($data);

            // Generate unique image name
            $imageName = time() . '_' . $item . '.png';
            $path = $uploadPath . '/' . $imageName;

            // Save image
            file_put_contents($path, $imageData);

            // Replace base64 src with uploaded file path
            $image->removeAttribute('src');
            $image->setAttribute('src', '/uploads/' . $imageName);
        }

        // Return modified HTML
        return $dom->saveHTML();
    }
}
```

Explanation:  
This model automatically converts base64 images pasted in Summernote into real image files.

---

## Step 3: Create Routes

`routes/web.php`

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

Route::get('posts/create',[PostController::class,'create']);//create now
Route::post('posts/store',[PostController::class,'store'])->name('posts.store');//all data store 

Route::get('/', function () {
    return view('welcome');
});
```

---

## Step 4: Create Controller
```
php artisan make:controller PostController
```

`app/Http/Controllers/PostController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PostController extends Controller
{
    /**
     * Display the form to create a new post
     * 
     * @return View
     */
    public function create(): View
    {
        // Render the create post form view
        return view('postsCreate');
    }

    /**
     * Store a newly created post in the database
     * 
     * @param Request $request Incoming form data
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        // Laravel 12 validation rules
        $request->validate([
            'title' => 'required|string|max:255',
            'body'  => 'required'
        ]);

        // Create post (body mutator handles image upload)
        Post::create([
            'title' => $request->title,
            'body'  => $request->body
        ]);

        return back()->with('success', 'Post created successfully.');
    }
}
```

---

## Step 5: Create Blade File

`resources/views/postsCreate.blade.php`

```html
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel 12 Summernote Editor Image Upload Example</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.css" />
</head>

<body>
<div class="container">
    <div class="card mt-5">
        <h3 class="card-header p-3">Laravel 12 Summernote Editor Image Upload Example</h3>

        <div class="card-body">
            <form method="post" action="{{ route('posts.store') }}">
                @csrf

                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control" />
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea id="summernote" name="body"></textarea>
                </div>

                <div class="form-group mt-2">
                    <button type="submit" class="btn btn-success">Publish</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.js"></script>

<script>
    $(document).ready(function () {
        $('#summernote').summernote({
            height: 300,
        });
    });
</script>
</body>
</html>
```

---

## Step 6: Run Laravel App

```
php artisan serve
```

Open browser:

```
http://localhost:8000/posts/create
```

---

<img width="1570" height="948" alt="image" src="https://github.com/user-attachments/assets/637f9b56-352e-49ec-ab95-0eab886dc6e2" />
<img width="1014" height="96" alt="image" src="https://github.com/user-attachments/assets/e7be84a2-6c1f-491f-96ab-015c44177aa4" />

