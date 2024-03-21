<?php

declare(strict_types=1);

namespace HDNET\Importr\Service;

use HDNET\Importr\Domain\Model\Import;
use HDNET\Importr\Domain\Model\Strategy;
use HDNET\Importr\Domain\Repository\ImportRepository;
use HDNET\Importr\Exception\ReinitializeException;
use HDNET\Importr\Processor\Configuration;
use HDNET\Importr\Processor\Resource;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * Service Manager
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @author  Tim Lochmüller <tim.lochmueller@hdnet.de>
 */
class Manager implements ManagerInterface
{

    /**
     * @var \HDNET\Importr\Domain\Repository\ImportRepository
     */
    protected $importRepository;


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @var \HDNET\Importr\Processor\Configuration
     */
    protected $configuration;

    /**
     * @var \HDNET\Importr\Processor\Resource
     */
    protected $resource;

    public function __construct(
        ImportRepository $importRepository,
        protected EventDispatcherInterface $eventDispatcher,
        PersistenceManager $persistenceManager,
        Configuration $configuration,
        Resource $resource
    ) {
        $this->importRepository = $importRepository;
        $this->persistenceManager = $persistenceManager;
        $this->configuration = $configuration;
        $this->resource = $resource;
    }

    /**
     * Update Interval
     *
     * @var int
     */
    protected $updateInterval = 1;

    /**
     * run the Importr
     */
    public function runImports()
    {
        try {
            $imports = $this->importRepository->findWorkQueue();
            foreach ($imports as $import) {
                $this->runImport($import);
            }
        } catch (ReinitializeException $exc) {
            $this->runImports();
        }
    }

    /**
     * Get the preview
     *
     * @param string                               $filepath
     * @param \HDNET\Importr\Domain\Model\Strategy $strategy
     *
     * @return array
     */
    public function getPreview(Strategy $strategy, $filepath)
    {
        $data = [];
        $resources = $this->initializeResources($strategy, $filepath);
        foreach ($resources as $resource) {
            /** @var \HDNET\Importr\Service\Resources\ResourceInterface $resource */
            // Resourcen Object anhand der Datei auswählen
            if (\preg_match($resource->getFilepathExpression(), $filepath)) {
                // Resource "benutzen"
                $resource->parseResource();
                // Durchlauf starten
                for ($pointer = 0; $pointer <= 20; $pointer++) {
                    if ($resource->getEntry($pointer)) {
                        $data[] = $resource->getEntry($pointer);
                    }
                }
                break;
            }
        }
        return $data;
    }

    /**
     * Magic Runner
     *
     * @param \HDNET\Importr\Domain\Model\Import $import
     */
    protected function runImport(Import $import)
    {
        $this->emitSignal('preImport', $import);

        $resources = $this->initializeResourcesByImport($import);
        $targets = $this->initializeTargets($import);
        $strategyConfiguration = $import->getStrategy()
            ->getConfiguration();

        foreach ($resources as $resource) {
            if ($this->resource->process($import, $targets, $strategyConfiguration, $resource, $this)) {
                break;
            }
        }

        $this->teardownTargets($import);

        $this->emitSignal('postImport', $import);
    }

    /**
     * @param \HDNET\Importr\Domain\Model\Import $import
     *
     * @return array
     */
    protected function initializeResourcesByImport(Import $import)
    {
        return $this->initializeResources($import->getStrategy(), $import->getFilepath());
    }

    /**
     * @param \HDNET\Importr\Domain\Model\Strategy $strategy
     * @param string                               $filepath
     *
     * @return array
     */
    protected function initializeResources(Strategy $strategy, $filepath)
    {
        $resources = [];
        $resourceConfiguration = $strategy->getResources();
        foreach ($resourceConfiguration as $resource => $configuration) {
            $object = GeneralUtility::makeInstance($resource);
            $object->start($strategy, $filepath);
            $object->setConfiguration($configuration);
            $resources[$resource] = $object;
        }
        return $resources;
    }

    /**
     * @param \HDNET\Importr\Domain\Model\Import $import
     *
     * @return array
     */
    protected function initializeTargets(Import $import)
    {
        $targetConfiguration = $import->getStrategy()
            ->getTargets();
        return $this->addTargets($targetConfiguration, $import);
    }

    protected function addTargets(array $targetConfiguration, Import $import, array $targets = []): array
    {
        foreach ($targetConfiguration as $target => $configuration) {
            if (\is_numeric($target)) {
                $targets = $this->addTargets($configuration, $import, $targets);
                continue;
            }
            $object = GeneralUtility::makeInstance($target);
            $object->setConfiguration($configuration);
            $object->getConfiguration();
            $object->start($import->getStrategy());
            $targets[] = $object;
        }
        return $targets;
    }

    /**
     * @param \HDNET\Importr\Domain\Model\Import $import
     */
    protected function teardownTargets(Import $import)
    {
        $targetConfiguration = $import->getStrategy()
            ->getTargets();
        $this->endTargets($targetConfiguration, $import);
    }

    protected function endTargets(array $targetConfiguration, Import $import): void
    {
        foreach ($targetConfiguration as $target => $configuration) {
            if (\is_numeric($target)) {
                $this->endTargets($configuration, $import);
                continue;
            }
            $object = GeneralUtility::makeInstance($target);
            $object->setConfiguration($configuration);
            $object->getConfiguration();
            $object->end($import->getStrategy());
        }
    }

    /**
     * @param int $interval
     */
    public function setUpdateInterval($interval)
    {
        $this->updateInterval = $interval;
    }

    /**
     * @return int
     */
    public function getUpdateInterval()
    {
        return $this->updateInterval;
    }

    /**
     * @param string $name
     * @param Import $import
     */
    protected function emitSignal($name, Import $import)
    {

        // @todo migrate to events
        #$this->signalSlotDispatcher->dispatch(
        #    __CLASS__,
        #    $name,
        #    [$this, $import]
        #);
    }
}
