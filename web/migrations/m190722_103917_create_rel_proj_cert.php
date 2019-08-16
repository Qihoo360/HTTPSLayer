<?php

use yii\db\Migration;

/**
 * Class m190722_103917_create_rel_proj_cert
 */
class m190722_103917_create_rel_proj_cert extends Migration
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
//        echo "m190722_103917_create_rel_proj_cert cannot be reverted.\n";
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
        $this->createTable('rel_proj_cert', [
            'id' => $this->primaryKey()->unsigned(),
            'project_id' => $this->integer(11)->unsigned()->notNull()->defaultValue(0),
            'certificate_id' => $this->integer(11)->unsigned()->notNull()->defaultValue(0),
            'status' => $this->tinyInteger(4)->unsigned()->notNull()->defaultValue(1),
        ], $tableOptions);

        $this->createIndex(
            "unq_proj_cert",
            'rel_proj_cert',
            ['project_id', 'certificate_id'],
            true
        );
    }

    public function down()
    {
        echo "m190722_103917_create_rel_proj_cert cannot be reverted.\n";

        return false;
    }
}
