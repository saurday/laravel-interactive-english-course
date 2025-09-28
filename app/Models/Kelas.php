<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';
    protected $fillable = ['nama_kelas', 'kode_kelas', 'dosen_id'];

    // Relasi ke dosen (user yang membuat kelas)
    public function dosen()
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    // Relasi ke mahasiswa melalui pivot
    public function mahasiswa()
    {
        return $this->belongsToMany(User::class, 'kelas_mahasiswa', 'kelas_id', 'mahasiswa_id')
                    ->withPivot('joined_at'); // kolom di tabel pivot milikmu
    }

    // Alias biar aman kalau ada kode yang memanggil ->students
    public function students()
    {
        return $this->mahasiswa();
    }


}
