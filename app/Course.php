<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Course extends Model
{
    use HasRelationships;

    public function users() {
        return $this->belongsToMany(User::class);
    }

    public function students() {
        return $this->belongsToMany(User::class)->where('role','student');
    }

    public function department() {
        return $this->belongsTo(Department::class);
    }

    public function blocks() {
        return $this->hasMany(Block::class);
    }

    public function schedule_requests() {
        return $this->belongsToMany(ScheduleRequest::class);
    }

    public function grades() {
        return $this->hasManyDeep(Grade::class, [Block::class, Assignment::class]);
    }

    public function assignments() {
        return $this->hasManyThrough(Assignment::class, Block::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function session(){
        return $this->hasMany(Session::class);
    }


    protected $with = ['department'];

}
