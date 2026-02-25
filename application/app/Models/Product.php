<?php

namespace App\Models;

//use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
//use Spatie\MediaLibrary\HasMedia;
//use Spatie\MediaLibrary\InteractsWithMedia;
//use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model
{
//    /** @use HasFactory<ProductFactory> */
//    use HasFactory;

//    use InteractsWithMedia;

    /**
     * @var string
     */
    protected $table = 'products';

    protected $fillable = [
        'name',
        'slug',
        'price',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_visible' => 'boolean',
        'published_at' => 'date',
    ];

    /** @return BelongsToMany<Category, $this> */
//    public function categories(): BelongsToMany
//    {
//        return $this->belongsToMany(Category::class, 'category_product', 'product_id', 'category_id')->withTimestamps();
//    }
}
