<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260916_000000_add_order_foreign_keys extends Migration
{
    private const TABLE_ORDERS = '{{%orders}}';
    private const TABLE_SERVICES = '{{%services}}';
    private const TABLE_USERS = '{{%users}}';

    public function safeUp(): void
    {
        $this->addForeignKey(
            'fk-orders-user_id',
            self::TABLE_ORDERS,
            'user_id',
            self::TABLE_USERS,
            'id',
            'RESTRICT',
            'CASCADE',
        );

        $this->addForeignKey(
            'fk-orders-service_id',
            self::TABLE_ORDERS,
            'service_id',
            self::TABLE_SERVICES,
            'id',
            'RESTRICT',
            'CASCADE',
        );
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk-orders-service_id', self::TABLE_ORDERS);
        $this->dropForeignKey('fk-orders-user_id', self::TABLE_ORDERS);
    }
}
