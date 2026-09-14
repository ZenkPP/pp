<?php

use yii\db\Migration;

class m260914_123513_add_order_user_service_indexes_and_fk extends Migration
{
    private const TABLE_ORDERS = '{{%orders}}';
    private const TABLE_USERS = '{{%users}}';

    public function safeUp(): void
    {
        $this->createIndex(
            'idx-orders-user_id',
            self::TABLE_ORDERS,
            'user_id',
        );

        $this->createIndex(
            'idx-orders-service_id',
            self::TABLE_ORDERS,
            'service_id',
        );

        $this->createIndex(
            'idx-orders-status-id',
            self::TABLE_ORDERS,
            ['status', 'id'],
        );

        $this->createIndex(
            'idx-orders-status-mode-service_id',
            self::TABLE_ORDERS,
            ['status', 'mode', 'service_id'],
        );

        $this->createIndex(
            'idx-users-first_name',
            self::TABLE_USERS,
            'first_name',
        );

        $this->createIndex(
            'idx-users-last_name',
            self::TABLE_USERS,
            'last_name',
        );
    }

    public function safeDown(): void
    {
        $this->dropIndex(
            'idx-users-last_name',
            self::TABLE_USERS,
        );

        $this->dropIndex(
            'idx-users-first_name',
            self::TABLE_USERS,
        );

        $this->dropIndex(
            'idx-orders-status-mode-service_id',
            self::TABLE_ORDERS,
        );

        $this->dropIndex(
            'idx-orders-status-id',
            self::TABLE_ORDERS,
        );

        $this->dropIndex(
            'idx-orders-service_id',
            self::TABLE_ORDERS,
        );

        $this->dropIndex(
            'idx-orders-user_id',
            self::TABLE_ORDERS,
        );
    }
}
