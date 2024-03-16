<?php
namespace App\Models;
use App\Constants\Status;
use App\Traits\GlobalStatus;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use Illuminate\Database\Eloquent\Casts\Attribute;
class Zone extends Model
{
    use Searchable, GlobalStatus, HasSpatial;

    protected $fillable = [
        'coordinates',
    ];

    protected $casts = [
        'coordinates' => Polygon::class,
    ];

    public function getCoordinates() : Attribute{
        return new Attribute(function(){
            return json_decode($this->coordinates->toJson())->coordinates[0];
        });
    }
}
