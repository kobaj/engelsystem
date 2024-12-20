<?php

declare(strict_types=1);

namespace Engelsystem\Models\Shifts;

use Engelsystem\Models\AngelType;
use Engelsystem\Models\BaseModel;
use Engelsystem\Models\Location;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property int             $id
 * @property int|null        $location_id
 * @property int|null        $shift_id
 * @property int             $angel_type_id
 * @property int             $count
 *
 * @property-read Location|null  $location
 * @property-read Shift|null $shift
 * @property-read AngelType  $angelType
 *
 * @method static QueryBuilder|NeededAngelType[] whereId($value)
 * @method static QueryBuilder|NeededAngelType[] whereLocationId($value)
 * @method static QueryBuilder|NeededAngelType[] whereShiftId($value)
 * @method static QueryBuilder|NeededAngelType[] whereAngelTypeId($value)
 * @method static QueryBuilder|NeededAngelType[] whereCount($value)
 */
class NeededAngelType extends BaseModel
{
    use HasFactory;

    /** @var array<string, null> default attributes */
    protected $attributes = [ // phpcs:ignore
        'location_id'  => null,
        'shift_id' => null,
    ];

    /** @var array<string> */
    protected $fillable = [ // phpcs:ignore
        'location_id',
        'shift_id',
        'angel_type_id',
        'count',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function angelType(): BelongsTo
    {
        return $this->belongsTo(AngelType::class);
    }
}
