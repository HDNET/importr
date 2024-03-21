<?php

declare(strict_types=1);

namespace HDNET\Importr\Controller;

use HDNET\Importr\Domain\Model\Import;
use HDNET\Importr\Domain\Model\Strategy;
use HDNET\Importr\Domain\Repository\ImportRepository;
use HDNET\Importr\Domain\Repository\StrategyRepository;
use HDNET\Importr\Service\ImportServiceInterface;
use HDNET\Importr\Service\Manager;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Description of ImportrController
 *
 * @author timlochmueller
 */
class ImportrController extends ActionController
{
    /**
     * @var \TYPO3\CMS\Core\Resource\ResourceFactory
     */
    protected $resourceFactory;

    /**
     * @var \HDNET\Importr\Domain\Repository\StrategyRepository
     */
    protected $strategyRepository;

    /**
     * @var \HDNET\Importr\Domain\Repository\ImportRepository
     */
    protected $importRepository;

    /**
     * @var \HDNET\Importr\Service\Manager
     */
    protected $importManager;

    /**
     * @var \HDNET\Importr\Service\ImportServiceInterface
     */
    protected $importService;

    public function __construct(
        ResourceFactory $resourceFactory,
        StrategyRepository $strategyRepository,
        ImportRepository $importRepository,
        Manager $importManager,
        ImportServiceInterface $importService,
        protected ModuleTemplateFactory $moduleTemplateFactory
    ) {
        $this->resourceFactory = $resourceFactory;
        $this->strategyRepository = $strategyRepository;
        $this->importRepository = $importRepository;
        $this->importManager = $importManager;
        $this->importService = $importService;
    }

    public function indexAction():ResponseInterface
    {
        $viewVariables = [];
        $combinedIdentifier = GeneralUtility::_GP('id');
        if (isset($combinedIdentifier) && \is_string($combinedIdentifier) && !MathUtility::canBeInterpretedAsInteger($combinedIdentifier)) {
            $folder = $this->resourceFactory->getFolderObjectFromCombinedIdentifier($combinedIdentifier);
            $files = [];
            foreach ($folder->getFiles() as $file) {
                $files[$file->getStorage()
                    ->getUid() . ':' . $file->getIdentifier()] = $file->getName();
            }
            $viewVariables['folder'] = $files;
        }
        $viewVariables['imports'] = $this->importRepository->findUserQueue();

        return $this->createModuleTemplate()
            ->assignMultiple($viewVariables)
            ->renderResponse('Index');
    }

    /**
     * @param string $identifier
     */
    public function importAction($identifier):ResponseInterface
    {
        $file = $this->resourceFactory->getObjectFromCombinedIdentifier($identifier);
        $viewVariables = [
            'file' => $file,
            'strategies' => $this->strategyRepository->findAllUser(),
        ];
        return $this->createModuleTemplate()
            ->assignMultiple($viewVariables)
            ->renderResponse('Import');
    }

    /**
     * @param string $identifier
     * @param \HDNET\Importr\Domain\Model\Strategy $strategy
     */
    public function previewAction($identifier, Strategy $strategy):ResponseInterface
    {
        $file = $this->resourceFactory->getObjectFromCombinedIdentifier($identifier);

        $filePath = $file->getForLocalProcessing();
        // @todo check path (absolute)
        $previewData = $this->importManager->getPreview($strategy, $filePath);

        return $this->createModuleTemplate()
            ->assignMultiple([
                // @todo better the file ID
                'filepath' => $filePath,
                'strategy' => $strategy,
                'preview' => $previewData,
            ])
            ->renderResponse('Preview');
    }

    /**
     * @param string $filepath
     * @param \HDNET\Importr\Domain\Model\Strategy $strategy
     */
    public function createAction($filepath, Strategy $strategy):ResponseInterface
    {
        $this->importService->addToQueue($filepath, $strategy);
        $text = 'The Import file %s width the strategy %s was successfully added to the queue';
        $message = GeneralUtility::makeInstance(
            FlashMessage::class,
            \sprintf($text, $filepath, $strategy->getTitle()),
            'Import is in Queue',
            FlashMessage::INFO,
            true
        );

        $flashMessageService = GeneralUtility::makeInstance(
            FlashMessageService::class
        );
        $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $messageQueue->addMessage($message);

        return $this->redirect('index');
    }

    /**
     * @param Import $import
     */
    public function resetAction(Import $import):ResponseInterface
    {
        $import->reset();
        $this->importRepository->update($import);
        return $this->redirect('index');
    }

    protected function createModuleTemplate(): ModuleTemplate
    {
        return $this->moduleTemplateFactory->create($this->request)
            ->setFlashMessageQueue($this->getFlashMessageQueue())
            ->setModuleClass('tx-impotr');
    }
}
