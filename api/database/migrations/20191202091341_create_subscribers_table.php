<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class CreateSubscribersTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('subscribers', ['id' => false, 'primary_key' => 'uuid']);
        $table->addColumn('uuid', 'uuid', [
            'default' => Literal::from('uuid_generate_v4()')
        ])
            ->addColumn('user_uuid', 'uuid', ['null' => false])
            ->addColumn('subscription_uuid', 'uuid', ['null' => false])
            ->addColumn('is_confirmed', 'boolean', ['default' => false])
            ->addColumn('is_active', 'boolean', ['default' => true])
            ->addTimestampsWithTimezone()
            ->create();
    }
}

