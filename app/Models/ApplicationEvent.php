<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ApplicationEvent extends Model {
 protected $fillable=['actor_id','status'];
 public function application(){return $this->belongsTo(Application::class);}
 public function actor(){return $this->belongsTo(User::class,'actor_id');}
}
