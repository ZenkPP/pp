<?php

declare(strict_types=1);

use yii\db\Migration;

class m260914_122834_create_order_user_service_table extends Migration
{
    private const TABLE_ORDERS = '{{%orders}}';
    private const TABLE_SERVICES = '{{%services}}';
    private const TABLE_USERS = '{{%users}}';

    public function safeUp(): void
    {
        $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ENGINE=InnoDB';

        $this->createTable(self::TABLE_USERS, [
            'id' => $this->primaryKey(),
            'first_name' => $this->string(300)->notNull(),
            'last_name' => $this->string(300)->notNull(),
        ], $tableOptions);

        $this->createTable(self::TABLE_SERVICES, [
            'id' => $this->primaryKey(),
            'name' => $this->string(300)->notNull(),
        ], $tableOptions);

        $this->createTable(self::TABLE_ORDERS, [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'link' => $this->string(300)->notNull(),
            'quantity' => $this->integer()->notNull(),
            'service_id' => $this->integer()->notNull(),

            'status' => $this->tinyInteger()
                ->notNull()
                ->comment('0 - Pending, 1 - In progress, 2 - Completed, 3 - Canceled, 4 - Error'),

            'created_at' => $this->integer()->notNull(),

            'mode' => $this->tinyInteger()
                ->notNull()
                ->comment('0 - Manual, 1 - Auto'),
        ], $tableOptions);
    }

    public function safeDown(): void
    {
        $this->dropTable(self::TABLE_ORDERS);
        $this->dropTable(self::TABLE_SERVICES);
        $this->dropTable(self::TABLE_USERS);
    }
}
