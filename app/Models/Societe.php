<?php
namespace App\Models;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Societe extends Basemodel
{
    use HasFactory;

    protected $appends = [
        'image_url',
        'thumbnail_url',
    ];

    public function scopeFilter($query, $search = null)
    {
        if (!is_null($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('societes.name', 'LIKE', "%{$search}%")
                    ->orWhere('societes.nif', 'LIKE', "%{$search}%")
                    ->orWhere('societes.stat', 'LIKE', "%{$search}%")
                    ->orWhere('societes.status', 'LIKE', "%{$search}%")
                    ;
            });
        }
        return $query;
    }
    public function scopeFilterStatus($query, $status = null)
    {
        if(!is_null($status)) $query->where('status',$status);
        return $query;
    }

    public function getImageUrlAttribute()
    {
        if (empty($this->img)) {
            return null;
        }
        return asset('storage/' . $this->img);
    }
    public function getThumbnailUrlAttribute()
    {
        if (empty($this->thumb_img)) {
            return null;
        }
        return asset('storage/' . $this->thumb_img);
    }

}
