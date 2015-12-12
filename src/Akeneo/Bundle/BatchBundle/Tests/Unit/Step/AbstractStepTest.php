<?php

namespace Akeneo\Bundle\BatchBundle\Tests\Unit\Job;

use Akeneo\Component\Batch\Job\BatchStatus;
use Akeneo\Component\Batch\Job\ExitStatus;
use Akeneo\Component\Batch\Job\Job;
use Akeneo\Component\Batch\Job\JobInterruptedException;

/**
 * Tests related to the AbstractStep class
 *
 */
class AbstractStepTest extends \PHPUnit_Framework_TestCase
{
    protected $step            = null;
    protected $eventDispatcher = null;
    protected $jobRepository   = null;

    const STEP_NAME = 'test_step_name';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->eventDispatcher = $this->getMock('Symfony\\Component\\EventDispatcher\\EventDispatcherInterface');
        $this->jobRepository   = $this->getMock('Akeneo\\Component\\Batch\\Job\\JobRepositoryInterface');

        $this->step = $this->getMockForAbstractClass(
            'Akeneo\\Component\\Batch\\Step\\AbstractStep',
            array(self::STEP_NAME)
        );

        $this->step->setEventDispatcher($this->eventDispatcher);
        $this->step->setJobRepository($this->jobRepository);
    }

    public function testGetSetJobRepository()
    {
        $this->step = $this->getMockForAbstractClass(
            'Akeneo\\Component\\Batch\\Step\\AbstractStep',
            array(self::STEP_NAME)
        );

        $this->assertNull($this->step->getJobRepository());
        $this->assertEntity($this->step->setJobRepository($this->jobRepository));
        $this->assertSame($this->jobRepository, $this->step->getJobRepository());
    }

    public function testGetSetName()
    {
        $this->assertEquals(self::STEP_NAME, $this->step->getName());
        $this->assertEntity($this->step->setName('other_name'));
        $this->assertEquals('other_name', $this->step->getName());
    }

    public function testExecute()
    {
        $stepExecution = $this->getMockBuilder('Akeneo\\Bundle\\BatchBundle\\Entity\\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $stepExecution->expects($this->once())
            ->method('getExitStatus')
            ->will($this->returnValue(new ExitStatus(ExitStatus::COMPLETED)));

        $stepExecution->expects($this->once())
            ->method('setEndTime')
            ->with($this->isInstanceOf('DateTime'));

        $stepExecution->expects($this->once())
            ->method('setExitStatus')
            ->with($this->equalTo(new ExitStatus(ExitStatus::COMPLETED)));

        $this->step->execute($stepExecution);
    }

    public function testExecuteWithTerminate()
    {
        $stepExecution = $this->getMockBuilder('Akeneo\\Bundle\\BatchBundle\\Entity\\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $stepExecution->expects($this->once())
            ->method('getExitStatus')
            ->will($this->returnValue(new ExitStatus(ExitStatus::COMPLETED)));

        $stepExecution->expects($this->any())
            ->method('getStatus')
            ->will($this->returnValue(new BatchStatus(BatchStatus::STOPPED)));

        $stepExecution->expects($this->any())
            ->method('isTerminateOnly')
            ->will($this->returnValue(true));

        $stepExecution->expects($this->once())
            ->method('upgradeStatus')
            ->with($this->equalTo(BatchStatus::STOPPED));

        $stepExecution->expects($this->once())
            ->method('setExitStatus')
            ->with(
                $this->equalTo(
                    new ExitStatus(
                        ExitStatus::STOPPED,
                        'Akeneo\\Component\\Batch\\Job\\JobInterruptedException'
                    )
                )
            );

        $this->step->execute($stepExecution);
    }

    public function testExecuteWithError()
    {
        $exception = new \Exception('My exception');

        $this->step->expects($this->once())
            ->method('doExecute')
            ->will($this->throwException($exception));

        $stepExecution = $this->getMockBuilder('Akeneo\\Bundle\\BatchBundle\\Entity\\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $stepExecution->expects($this->any())
            ->method('getStatus')
            ->will($this->returnValue(new BatchStatus(BatchStatus::FAILED)));

        $stepExecution->expects($this->once())
            ->method('upgradeStatus')
            ->with($this->equalTo(BatchStatus::FAILED));

        $stepExecution->expects($this->once())
            ->method('setExitStatus')
            ->with($this->equalTo(new ExitStatus(ExitStatus::FAILED, $exception->getTraceAsString())));

        $this->step->execute($stepExecution);
    }

    /**
     * Assert the entity tested
     *
     * @param object $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Akeneo\Component\Batch\Step\AbstractStep', $entity);
    }
}
