<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    protected $table = 'tahun_ajaran';

    protected $fillable = ['nama', 'tahun_mulai', 'is_aktif'];

    protected $casts = ['is_aktif' => 'boolean'];

    public static function aktif()
    {
        return static::where('is_aktif', true)->first();
    }

    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'tahun_ajaran_id');
    }
}