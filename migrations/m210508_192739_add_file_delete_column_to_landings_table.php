<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%landings}}`.
 */
class m210508_192739_add_file_delete_column_to_landings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%landings}}', 'file_delete', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
