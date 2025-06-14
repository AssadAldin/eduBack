<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'user_id', 'visible'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lessons()
    {
        return $this->hasMany(\App\Models\Lesson::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot(['is_accepted', 'accepted_at'])->withTimestamps();
    }

}
