<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%landings}}`.
 */
class m210508_162545_create_landings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%landings}}', [
            'id' => $this->primaryKey(),
            'status' => $this->boolean()->defaultValue(0),
            'date' => $this->integer(),
            'is_paid' => $this->boolean()->defaultValue(0),
            'file_name' => $this->string(),
            'url' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%landings}}');
    }
}
