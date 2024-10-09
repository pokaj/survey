<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    protected $table = 'surveys';

    protected $fillable = ['id', 'title', 'owner_id', 'description'];

    protected $hidden = ['created_at', 'updated_at'];


    public function questions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Question::class);
    }


    public function delete()
    {
        $this->questions()->with('responses')->each(function ($question) {
            $question->responses()->delete();
            $question->delete();
        });

        return parent::delete();
    }

}
