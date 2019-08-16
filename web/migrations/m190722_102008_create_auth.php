<?php

use yii\db\Migration;

/**
 * Class m190722_102008_create_auth
 */
class m190722_102008_create_auth extends Migration
{
//
//    /**
//     * {@inheritdoc}
//     */
//    public function safeUp()
//    {
//
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function safeDown()
//    {
//        echo "m190722_102008_create_auth cannot be reverted.\n";
//
//        return false;
//    }

    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        // Create auth table
        $this->createTable('auth', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer(11)->unsigned()->notNull()->defaultValue(0),
            'source' => $this->string(64)->notNull()->defaultValue(""),
            "source_id" => $this->string(64)->notNull()->defaultValue(""),
        ], $tableOptions);
    }

    public function down()
    {
        echo "m190722_102008_create_auth cannot be reverted.\n";

        return false;
    }
}
