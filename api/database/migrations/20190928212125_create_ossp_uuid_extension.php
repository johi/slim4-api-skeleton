<?php

use Phinx\Migration\AbstractMigration;

class CreateOsspUuidExtension extends AbstractMigration
{
    public function up() {
        $this->execute('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');
    }

    public function down() {
        $this->execute('DROP EXTENSION IF EXISTS "uuid-ossp"');
    }
}
