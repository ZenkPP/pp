<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260914_123000_load_order_data extends Migration
{
    public function safeUp(): void
    {
        $dumpPath = Yii::getAlias('@app/migrations/data/test_db_data.sql');
        $sql = file_get_contents($dumpPath);

        if ($sql === false) {
            throw new RuntimeException("Unable to read SQL dump: $dumpPath");
        }

        $this->db->createCommand($sql)->execute();
    }

    public function safeDown(): void
    {
        $this->delete('{{%orders}}');
        $this->delete('{{%services}}');
        $this->delete('{{%users}}');
    }
}
