<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\mutex;

use yii\base\InvalidConfigException;
use yii\mutex\Mutex;

/**
 * Class MutexTestTrait.
 */
trait MutexTestTrait
{
    /**
     * @return Mutex
     * @throws InvalidConfigException
     */
    abstract protected function createMutex();

    /**
     * @dataProvider mutexDataProvider()
     *
     * @param string $mutexName
     */
    public function testMutexAcquire($mutexName)
    {
        $mutex = $this->createMutex();

        $this->assertTrue($mutex->acquire($mutexName));
        $this->assertTrue($mutex->release($mutexName));
    }

    /**
     * @dataProvider mutexDataProvider()
     *
     * @param string $mutexName
     */
    public function testThatMutexLockIsWorking($mutexName)
    {
        $mutexOne = $this->createMutex();
        $mutexTwo = $this->createMutex();

        $this->assertTrue($mutexOne->acquire($mutexName));
        $this->assertFalse($mutexTwo->acquire($mutexName));
        $this->assertTrue($mutexOne->release($mutexName));
        $this->assertFalse($mutexTwo->release($mutexName));

        $this->assertTrue($mutexTwo->acquire($mutexName));
        $this->assertTrue($mutexTwo->release($mutexName));
    }

    /**
     * @dataProvider mutexDataProvider()
     *
     * @param string $mutexName
     */
    public function testThatMutexLockIsWorkingOnTheSameComponent($mutexName)
    {
        $mutex = $this->createMutex();

        $this->assertTrue($mutex->acquire($mutexName));
        $this->assertFalse($mutex->acquire($mutexName));

        $this->assertTrue($mutex->release($mutexName));
        $this->assertFalse($mutex->release($mutexName));
    }

    public function testTimeout()
    {
        $mutexName = __FUNCTION__;
        $mutexOne = $this->createMutex();
        $mutexTwo = $this->createMutex();

        $this->assertTrue($mutexOne->acquire($mutexName));
        $microtime = microtime(true);
        $this->assertFalse($mutexTwo->acquire($mutexName, 1));
        $diff = microtime(true) - $microtime;
        $this->assertTrue($diff >= 1 && $diff < 2);
        $this->assertTrue($mutexOne->release($mutexName));
        $this->assertFalse($mutexTwo->release($mutexName));
    }

    /**
     * @dataProvider mutexDataProvider()
     *
     * @param string $mutexName
     */
    public function testMutexIsAcquired($mutexName)
    {
        $mutexOne = $this->createMutex();
        $mutexTwo = $this->createMutex();

        $this->assertFalse($mutexOne->isAcquired($mutexName));
        $this->assertTrue($mutexOne->acquire($mutexName));
        $this->assertTrue($mutexOne->isAcquired($mutexName));

        $this->assertFalse($mutexTwo->isAcquired($mutexName));

        $this->assertTrue($mutexOne->release($mutexName));
        $this->assertFalse($mutexOne->isAcquired($mutexName));

        $this->assertFalse($mutexOne->isAcquired('non existing'));
    }

    public static function mutexDataProvider()
    {
        $utf = <<<'UTF'
?????????? ?????? ???? ????????????????, ??????????????????????, ???????????? ??????????????????????????????? ??????????? ?????????????????????????????.
????l???????????l?? ???????????? ?????????????????????????????.
??????????????????? ?????????????????? ?????????? ??????? ?????????? ????????????.
???????????? ??????????????????????????????l???? ???????????????l???????.
????
UTF;

        return [
            'simple name' => ['testname'],
            'long name' => ['Y' . str_repeat('iiiiiiiiii', 1000)],
            'UTF-8 garbage' => [$utf],
        ];
    }
}
