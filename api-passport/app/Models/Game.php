<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'dado1', 'dado2', 'resultado'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


?>
