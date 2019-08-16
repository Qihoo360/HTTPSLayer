<?php

use yii\db\Migration;

/**
 * Class m190722_103753_create_certificate
 */
class m190722_103753_create_certificate extends Migration
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
//        echo "m190722_103753_create_certificate cannot be reverted.\n";
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
        $this->createTable('certificate', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(60)->notNull()->defaultValue(""),
            'priv_key' => $this->string(50)->notNull()->defaultValue(''),
            'pub_key' => $this->string(50)->notNull()->defaultValue(''),
            'status' => $this->tinyInteger(4)->unsigned()->notNull()->defaultValue(0),
            'serial_no' => $this->string(300)->notNull()->defaultValue(''),
            'subject' => $this->string(300)->notNull()->defaultValue(''),
            'priority' => $this->integer(11)->unsigned()->notNull()->defaultValue(0),
            'algorithm' => $this->string(50)->notNull()->defaultValue(''),
            'issuer' => $this->string(200)->notNull()->defaultValue(''),
            'valid_start_time' => $this->dateTime(),
            'valid_end_time' => $this->dateTime(),
            'contact_email' => $this->string(60)->notNull()->defaultValue(''),
            'priv_content' => $this->text(),
            'pub_content' => $this->text(),
            'create_time' => $this->dateTime()->notNull()->defaultExpression("CURRENT_TIMESTAMP"),
            'update_time' => $this->dateTime()->append('ON UPDATE CURRENT_TIMESTAMP'),
        ], $tableOptions);
    }

    public function down()
    {
        echo "m190722_103753_create_certificate cannot be reverted.\n";

        return false;
    }
}
