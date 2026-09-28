<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobListing extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['deadline' => 'date', 'featured' => 'boolean'];
    }

    public function employer()
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function scopeOpen($q)
    {
        return $q->where('status', 'published')->whereDate('deadline', '>=', today());
    }
}
