<?php

namespace modules\orders\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 *
 * @property-read Order[] $orders
 */
final class Service extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%services}}';
    }

    public function rules(): array
    {
        return [
            ['name', 'required'],
            ['name', 'string', 'max' => 300],
        ];
    }

    public function getOrders(): ActiveQuery
    {
        return $this->hasMany(
            Order::class,
            ['service_id' => 'id'],
        );
    }
}
