<?php

use yii\db\Migration;

/**
 * Handles the creation of table `mail`.
 */
class m180905_182036_create_mail_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('mailss', [
            'id' => $this->primaryKey(5),
            'mailto' => $this->string(255)->null(),
            'mailfrom' => $this->string(255)->null(),
            'date' => $this->integer(11)->notNull(),
            'subject' => $this->string(998)->null(),
            'body' => $this->text()->null(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('mail');
    }
}
