<?php

declare(strict_types=1);
/**
 * ImportCommandController.php
 */
namespace HDNET\Importr\Command;

use HDNET\Importr\Service\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
/**
 * ImportCommandController
 *
 * For initializing the Manager
 */
class ImportCommandController extends Command
{

    /**
     * ImportCommandController constructor.
     * @param string|null $name
     */
    public function __construct(string $name = null)
    {
        parent::__construct($name);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initializeServiceManagerCommand();

        return 0;
    }

    /**
     * initializes the import service manager
     *
     * @param string $mail Set an email address for error reporting
     *
     * @return bool
     */
    public function initializeServiceManagerCommand($mail = null)
    {
        $message = GeneralUtility::makeInstance(
            FlashMessage::class,
            '',
            'Initializing ServiceManager',
            \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::INFO
        );
        $this->addFlashMessage($message);

        $manager = GeneralUtility::makeInstance(Manager::class);
        try {
            // let the manager run the imports now
            $manager->runImports();
        } catch (\Exception $e) {
            $message = GeneralUtility::makeInstance(
                FlashMessage::class,
                '',
                'An Error occured: ' . $e->getCode() . ': ' . $e->getMessage(),
                \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR
            );
            $this->addFlashMessage($message);

            // @TODO: Send email when the manager crashes.
            return false;
        }
        return true;
    }

    /**
     * @param FlashMessage $flashMessage
     */
    protected function addFlashMessage(FlashMessage $flashMessage)
    {
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $messageQueue->addMessage($flashMessage);
    }
}
