<?php

    namespace Jubilant\commands;

    use Jubilant\Database;
    use Jubilant\Migration\MigrationManager;

    class Migrate {
        
        private $db;

        public function __construct(Database $db) {
            $this->db = $db;
        }

        public function run($filename) {
            $manager = new MigrationManager($this->db, __DIR__ . '/../../migrations');
            switch ($filename) {
                case 'migrate':
                    $manager->migrate();
                    echo "Migrations applied.\n";
                    break;
                case 'rollback':
                    $manager->rollback();
                    echo "Last migration rolled back.\n";
                    break;
                default:
                    echo "Invalid migration command.\n";
                    break;
            }
        }

    }
