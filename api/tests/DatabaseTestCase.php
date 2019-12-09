<?php
declare(strict_types=1);

namespace Tests;

use App\Infrastructure\Database\PdoDatabaseService;
use Phinx\Config\Config;
use Phinx\Migration\Manager;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Yaml\Yaml;

abstract class DatabaseTestCase extends TestCase
{

    protected static $databaseService;
    protected static $manager;
    protected static $seedSubPath;

    public static function setUpBeforeClass()
    {
        self::$seedSubPath = '';
        self::$databaseService = new PdoDatabaseService('test'); // needs environment
        $configArray = Yaml::parse(file_get_contents('phinx.yml'));
        $configArray['environments']['test'] = [
            'connection' => self::$databaseService->getConnection()
        ];
        //@todo solve the namespace issue with phinx seeds
        $configArray['paths']['seeds'] = [
            '/api/tests/Database/Seeds' . static::$seedSubPath
            // 'Tests\\Database\\Seeds\\User\\' => '/api/tests/Database/Seeds' . static::$seedSubPath
        ];
        $config = new Config($configArray);
        self::$manager = new Manager($config, new StringInput(' '), new NullOutput());
    }

    public static function tearDownAfterClass()
    {}

}