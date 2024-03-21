<?php

declare(strict_types=1);

namespace HDNET\Importr\Processor;

use HDNET\Importr\Domain\Model\Import;
use HDNET\Importr\Service\ImportServiceInterface;
use HDNET\Importr\Service\Targets\TargetInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Target
 */
class Target
{
    /**
     * @var ImportServiceInterface
     */
    protected $importService;

    /**
     * Target constructor.
     *
     * @param ImportServiceInterface $importService
     */
    public function __construct(ImportServiceInterface $importService, protected EventDispatcherInterface $dispatcher)
    {
        $this->importService = $importService;
    }

    /**
     * @param TargetInterface $target
     * @param mixed $entry
     * @param Import $import
     * @param int $pointer
     *
     * @throws \Exception
     */
    public function process(TargetInterface $target, $entry, Import $import, $pointer)
    {
        try {
            $entry = $this->emitEntrySignal('preProcess', $target->getConfiguration(), $entry);
            $result = $target->processEntry($entry);
            $import->increaseCount($result);
        } catch (\Exception $e) {
            $import->increaseCount(TargetInterface::RESULT_ERROR);
            $this->importService->updateImport($import, $pointer + 1);
            throw $e;
        }
    }

    /**
     * @param string $name
     * @param array $configuration
     * @param mixed $entry
     *
     * @return mixed
     */
    protected function emitEntrySignal($name, array $configuration, $entry)
    {
        // @todo migrate to events
        #$result = $this->dispatcher->dispatch(
        #    __CLASS__,
        #    $name,
        #    [$configuration, $entry]
        #);

        return $result[1];
    }
}
