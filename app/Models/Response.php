<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    protected $table = 'responses';

    protected $fillable = ['user_id', 'question_id', 'answer'];

    protected $casts = ['answer' => 'array'];

    protected $hidden = ['created_at', 'updated_at'];


    public function question(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Question::class);
    }


}
