<?php

namespace tests;

use Yii;
use yii\db\Query;

/**
 * Class CacheSettingsTest
 * @package tests
 */
class CacheSettingsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \lav45\settings\Settings|\lav45\settings\behaviors\CacheBehavior
     */
    protected function getSettings()
    {
        return Yii::$app->get('settings');
    }

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        Yii::$app->set('settings', [
            'class' => 'lav45\settings\Settings',
            'as cache' => [
                'class' => 'lav45\settings\behaviors\CacheBehavior',
            ]
        ]);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->clearStorage();
        $this->getSettings()->cache->flush();
    }

    protected function clearStorage()
    {
        /** @var \lav45\settings\storage\DbStorage $storage */
        $storage = $this->getSettings()->storage;
        return (new Query())
            ->createCommand()
            ->delete($storage->tableName)
            ->execute();
    }

    public function testGetDataInCache()
    {
        $items = [
            'object' => new \stdClass(),
            'array' => ['data'],
            'integer' => 123,
            'float' => 123.5,
            'string' => 'string',
            'bool true' => true,
            'bool false' => false,
            'null' => null,
            'empty string' => '',
            'zero' => 0
        ];

        $settings = $this->getSettings();

        foreach ($items as $key => $data) {
            static::assertTrue($settings->set($key, $data));
        }

        $rows = $this->clearStorage();
        static::assertEquals($rows, count($items));

        foreach ($items as $key => $data) {
            static::assertEquals($settings->get($key), $data);
        }

        static::assertFalse($settings->delete('object'));
        static::assertNull($settings->get('object'));
    }

    public function testGetDataWithoutCache()
    {
        $settings = $this->getSettings();
        $key = 'key';
        $data = ['data'];

        static::assertTrue($settings->set($key, $data));
        static::assertTrue($settings->cache->flush());
        static::assertEquals($settings->get($key), $data);
    }
}