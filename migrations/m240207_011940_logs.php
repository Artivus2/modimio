<?php

use yii\db\Migration;

/**
 * Class m240207_011940_logs
 */
class m240207_011940_logs extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('logs', [
            'id' => $this->primaryKey(),
            'ip' => $this->string()->notNull()->defaultValue("0.0.0.0"),
            'date' => $this->dateTime()->notNull(),
            'url' => $this->string()->notNull(),
            'useragent' => $this->string()->notNull(),
            'os' => $this->string(),
            'archi' => $this->string(),
            'browser' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240207_011940_logs cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240207_011940_logs cannot be reverted.\n";

        return false;
    }
    */
}
