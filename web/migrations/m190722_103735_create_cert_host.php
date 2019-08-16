<?php

use yii\db\Migration;

/**
 * Class m190722_103735_create_cert_host
 */
class m190722_103735_create_cert_host extends Migration
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
//        echo "m190722_103735_create_cert_host cannot be reverted.\n";
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
        $this->createTable('cert_host', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(64)->notNull()->defaultValue(""),
            'certificate_id' => $this->integer(11)->unsigned()->notNull()->defaultValue(0),
        ], $tableOptions);

        $this->createIndex(
            'idx_certificate_id',
            'cert_host',
            'certificate_id'
        );
    }

    public function down()
    {
        echo "m190722_103735_create_cert_host cannot be reverted.\n";

        return false;
    }
}
