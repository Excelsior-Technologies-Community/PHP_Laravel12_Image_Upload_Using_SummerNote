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
        
        // Load HTML with specific flags:
        // LIBXML_HTML_NOIMPLIED - Don't add implied HTML elements
        // LIBXML_HTML_NODEFDTD - Don't add DOCTYPE
        $dom->loadHtml($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        // Get all <img> elements from the HTML
        $imageFile = $dom->getElementsByTagName('img');

        // ✅ Path where images will be uploaded (public/uploads/)
        $uploadPath = public_path('uploads');

        // ✅ Create uploads directory if it doesn't exist
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true); // Recursive directory creation
        }

        // Loop through each image tag
        foreach ($imageFile as $item => $image) {
            $src = $image->getAttribute('src');

            // Skip images that are already uploaded (not base64)
            // Only process data URI images (base64 encoded)
            if (!str_starts_with($src, 'data:image')) {
                continue;
            }

            // Parse base64 data URI: data:image/png;base64,iVBORw0KGgo...
            list($type, $data) = explode(';', $src);
            list(, $data) = explode(',', $data); // Extract base64 data part

            // Decode base64 image data to binary
            $imageData = base64_decode($data);

            // Generate unique filename using timestamp + index
            $imageName = time() . '_' . $item . '.png';
            $path = $uploadPath . '/' . $imageName;

            // Save image file to server
            file_put_contents($path, $imageData);

            // Update img src from base64 to uploaded file path
            $image->removeAttribute('src');
            $image->setAttribute('src', '/uploads/' . $imageName);
        }

        // Return modified HTML with all images processed
        return $dom->saveHTML();
    }
}
