<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = [
        'code',
        'name',
        'native',
        'rtl',
        'status',
        'json_exist',
        'is_default',
    ];

    public function getIsRtlAttribute()
    {
        if ($this->rtl == 1) {
            return '<i class="las la-check text-success"></i>';
        } else {
            return '<i class="las la-times text-danger"></i>';
        }
    }

    public function getIsActiveAttribute()
    {
        if ($this->status == 'active') {
            return '<small class="tyne-badge" variant="success">'._trans('common.Active').'</small>';
        } else {
            return '<small class="badge badge-danger">'._trans('common.Inactive').'</small>';
        }
    }
}
