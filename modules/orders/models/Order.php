<?php

declare(strict_types=1);

namespace modules\orders\models;

use modules\orders\providers\OrderProvider;
use modules\users\models\User;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $user_id
 * @property string $link
 * @property int $quantity
 * @property int $service_id
 * @property int $status
 * @property int $created_at
 * @property int $mode
 *
 * @property-read User $user
 * @property-read Service $service
 */
final class Order extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%orders}}';
    }

    public function rules(): array
    {
        return [
            [
                [
                    'user_id',
                    'quantity',
                    'service_id',
                    'status',
                    'created_at',
                    'mode',
                ],
                'integer',
            ],

            [
                [
                    'user_id',
                    'link',
                    'quantity',
                    'service_id',
                    'status',
                    'created_at',
                    'mode',
                ],
                'required',
            ],

            ['link', 'string', 'max' => 300],

            [
                'status',
                'in',
                'range' => array_column(OrderStatus::cases(), 'value'),
            ],

            [
                'mode',
                'in',
                'range' => array_column(OrderMode::cases(), 'value'),
            ],
        ];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(
            User::class,
            ['id' => 'user_id'],
        );
    }

    public function getService(): ActiveQuery
    {
        return $this->hasOne(
            Service::class,
            ['id' => 'service_id'],
        );
    }

    public function afterSave($insert, $changedAttributes): void
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            Yii::$app->cache->delete(OrderProvider::CACHE_ORDERS_COUNT);
        }
    }

    public function afterDelete(): void
    {
        parent::afterDelete();

        Yii::$app->cache->delete(OrderProvider::CACHE_ORDERS_COUNT);
    }
}
