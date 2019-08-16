<?php

use yii\db\Migration;

/**
 * Class m190722_102019_create_balance
 */
class m190722_102019_create_balance extends Migration
{
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
//        echo "m190722_102019_create_balance cannot be reverted.\n";
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

        // Create user table
        $this->createTable('balance', [
            'id' => $this->primaryKey()->unsigned(),
            'project_id' => $this->integer(11)->unsigned(),
            'location' => $this->string(10)->notNull()->defaultValue(''),
            'vip'=> $this->string(20)->notNull()->defaultValue(''),
            'weight' => $this->integer(11)->unsigned()->notNull()->defaultValue(0),
            'create_time' => $this->dateTime()->notNull()->defaultExpression("CURRENT_TIMESTAMP"),
            'update_time' => $this->dateTime()->append('ON UPDATE CURRENT_TIMESTAMP'),
            'qfe_idc' => $this->string(10)->notNull()->defaultValue(''),
        ], $tableOptions);
    }

    public function down()
    {
        echo "m190722_102019_create_balance cannot be reverted.\n";

        return false;
    }
}
