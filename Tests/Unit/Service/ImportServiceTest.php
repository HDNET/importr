<?php

declare(strict_types=1);
namespace HDNET\Importr\Tests\Unit\Service;

use HDNET\Importr\Domain\Model\Import;
use HDNET\Importr\Domain\Model\Strategy;
use HDNET\Importr\Domain\Repository\ImportRepository;
use HDNET\Importr\Service\ImportService;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ImportServiceTest extends UnitTestCase
{

    /**
     * @var ImportService
     */
    protected $fixture;

    /**
     * @var ImportRepository
     */
    protected $repository;


    public function setUp(): void
    {
        $persistenceManager = $this->getMockBuilder(PersistenceManagerInterface::class)->getMock();
        $repository = $this->getMockBuilder(ImportRepository::class)->disableOriginalConstructor()->getMock();
        $this->repository = $repository;

        $this->fixture = new ImportService($persistenceManager, $repository);
    }

    /**
     * @test
     */
    public function is_pointer_increased_on_update()
    {
        $import = $this->getAccessibleMock(Import::class);
        $pointer = 5;

        $this->repository->expects(self::once())->method('update')->with($import);
        $import->expects(self::once())->method('setPointer')->with($pointer);

        $this->fixture->updateImport($import, $pointer);
    }

    /**
     * @test
     */
    public function is_import_added_to_queue()
    {
        $import = $this->getAccessibleMock(Import::class);
        $strategy = $this->getAccessibleMock(Strategy::class);
        $path = './import.csv';
        $time = '2016-06-19T13:49:39+00:00';

        $import->expects(self::once())->method('setStrategy')->with($strategy);
        $import->expects(self::once())->method('setFilepath')->with($path);
        $import->expects(self::once())->method('setStarttime')->with(self::callback(function ($date) use ($time) {
            return $date->format(\DateTime::ATOM) === $time;
        }));


        $this->repository->expects(self::once())->method('add')->with(self::isInstanceOf(Import::class));

        $this->fixture->addToQueue($path, $strategy, ['start' => $time]);
    }

    /**
     * @test
     */
    public function is_datetime_created_with_invalid_text()
    {
        $import = $this->getAccessibleMock(Import::class);
        $strategy = $this->getAccessibleMock(Strategy::class);
        $path = './import.csv';

        $import->expects(self::once())->method('setStarttime')->with(self::isInstanceOf(\DateTime::class));

        $this->fixture->addToQueue($path, $strategy, ['start' => 'Lorem ipsum']);
    }
}
