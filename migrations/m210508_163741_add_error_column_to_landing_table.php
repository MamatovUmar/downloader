<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%landing}}`.
 */
class m210508_163741_add_error_column_to_landing_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%landings}}', 'error', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%landings}}', 'error');
    }
}
