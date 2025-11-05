<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FactLog extends Model
{
    use HasFactory;

    protected $table = 'fact_logs';
    protected $primaryKey = 'fact_id';
    public $timestamps = true;

    protected $fillable = [
        'fact_type_id',
        'admin_id',
        'import_id',
        'entity_type',
        'entity_id',
        'action',
        'details', // long text, nullable
        'logged_at',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
    ];

    /**
     * Relations
     */
    public function factType()
    {
        return $this->belongsTo(FactType::class, 'fact_type_id', 'fact_type_id');
    }

    public function admin()
    {
        return $this->belongsTo(AdminAccount::class, 'admin_id', 'admin_id');
    }

    public function importLog()
    {
        return $this->belongsTo(ImportLog::class, 'import_id', 'import_id');
    }

    /**
     * Helper method to safely log facts
     */
    public static function logFact(array $data)
    {
        // Ensure details is always a string or null
        if (!isset($data['details']) || $data['details'] === '') {
            $data['details'] = null;
        }

        return self::create($data);
    }
}
