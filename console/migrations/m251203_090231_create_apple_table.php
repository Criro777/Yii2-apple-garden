<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%apple}}`.
 */
class m251203_090231_create_apple_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%apple}}', [
            'id' => $this->primaryKey(),
            'color' => $this->string(50)->notNull(),
            'date_appear' => $this->integer()->notNull(), //Unix timestamp
            'date_fall' => $this->integer(), //Unix timestamp
            'status' => $this->tinyInteger()->defaultValue(1)->comment('1-на дереве, 2-упало, 3-гнилое'),
            'eaten_percent' => $this->decimal(5,2)->defaultValue(0),
            'created_at' => $this->integer()->notNull(), //Unix timestamp
            'updated_at' => $this->integer()->notNull(), //Unix timestamp
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%apple}}');
    }
}
