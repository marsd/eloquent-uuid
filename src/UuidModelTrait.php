<?php

namespace Alsofronie\Uuid;

use Ramsey\Uuid\Uuid as RamseyUuid;
use MysqlUuid\Formats\ReorderedString;
use MysqlUuid\Uuid as MysqlUuid;

/*
 * This trait is to be used with the default $table->uuid('id') schema definition
 * @package Alsofronie\Uuid
 * @author Alex Sofronie <alsofronie@gmail.com>
 * @license MIT
 */
trait UuidModelTrait
{
    /*
     * This function is used internally by Eloquent models to test if the model has auto increment value
     * @returns bool Always false
     */
    public function getIncrementing()
    {
        return false;
    }

    /**
     * This function overwrites the default boot static method of Eloquent models. It will hook
     * the creation event with a simple closure to insert the UUID
     */
    public static function bootUuidModelTrait()
    {
        static::creating(function ($model) {
            // Only generate UUID if it wasn't set by already.
            if (!isset($model->attributes[$model->getKeyName()])) {
                // This is necessary because on \Illuminate\Database\Eloquent\Model::performInsert
                // will not check for $this->getIncrementing() but directly for $this->incrementing
                $model->incrementing = false;
                $uuid = RamseyUuid::uuid1()->toString();
                $reordered = new MysqlUuid($uuid);            
                
                $model->attributes[$model->getKeyName()] = str_replace('-', '', $reordered->toFormat(new ReorderedString()));;
            }
        }, 0);
    }
}
