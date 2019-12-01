<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class CreateUsersTable extends AbstractMigration
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
        $table = $this->table('users', ['id' => false, 'primary_key' => 'uuid']);
        $table->addColumn('uuid', 'uuid', [
            'default' => Literal::from('uuid_generate_v4()')
        ])
            ->addColumn('name', 'string', ['limit' => 128, 'null' => false])
            ->addColumn('email', 'string', ['limit' => 128, 'null' => false])
            ->addColumn('password', 'string', ['limit' => 128, 'null' => false])
            ->addColumn('email_verified', 'timestamp', ['null' => true, 'default' => null, 'timezone' => true])
            ->addColumn('remember_me_token', 'string', ['null' => true, 'default' => null, 'limit' => 100])
            ->addIndex(['email'], [
                'unique' => true,
                'name' => 'idx_users_email'])
            ->addTimestampsWithTimezone()
            ->create();
    }
}
