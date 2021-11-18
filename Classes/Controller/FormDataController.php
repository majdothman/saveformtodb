<?php

namespace Othman\SaveFormToDb\Controller;

use Othman\SaveFormToDb\Domain\Repository\FormDataRepository;
use Othman\SaveFormToDb\Provider\ConfigurationProvider;
use Othman\SaveFormToDb\Utility\CsvExportUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class FormDataController extends ActionController
{
    protected FormDataRepository $formDataRepository;
    protected array $beUser;
    protected array $extensionConfiguration;

    /**
     * @param FormDataRepository $formDataRepository
     */
    public function injectFormDataRepository(FormDataRepository $formDataRepository): void
    {
        $this->formDataRepository = $formDataRepository;
    }

    protected ConfigurationProvider $configurationProvider;

    /**
     * @param ConfigurationProvider $configurationProvider
     */
    public function injectConfigurationProvider(ConfigurationProvider $configurationProvider): void
    {
        $this->configurationProvider = $configurationProvider;
    }

    protected CsvExportUtility $csvExportUtility;

    /**
     * @param CsvExportUtility $csvExportUtility
     */
    public function injectCsvExportUtility(CsvExportUtility $csvExportUtility): void
    {
        $this->csvExportUtility = $csvExportUtility;
    }

    protected function initializeAction()
    {
        $this->extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('othman_saveformtodb');
        $this->extensionConfiguration['defaultCsvFields'] = GeneralUtility::trimExplode(',', $this->extensionConfiguration['defaultCsvFields']);
        $this->beUser = $GLOBALS['BE_USER']->user;
    }

    /**
     * Get all forms
     */
    public function indexAction()
    {
        $formsIdentifier = $this->formDataRepository->findAllFormsIdentifier();
        $this->view->assign('formsIdentifier', $formsIdentifier);
    }

    /**
     * Get all Emails from form(Identifier)
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    public function listByFormIdentifierAction()
    {
        $formData = [];
        if ($this->request->hasArgument('formIdentifier')) {
            $formIdentifier = $this->request->getArgument('formIdentifier');
            $formIdentifierPath = $this->request->getArgument('formIdentifierPath');
            $formData = $this->formDataRepository->findByFormIdentifier($formIdentifier);
            foreach ($formData as &$data) {
                $data['values'] = json_decode($data['values'], true);
            }
            $this->view->assign('formIdentifier', $formIdentifier);
            $this->view->assign('formIdentifierPath', $formIdentifierPath);
            $this->view->assign('formFields', $this->configurationProvider->getFieldsOfFormIdentifierPath($formIdentifierPath));
            $this->view->assign('configuration', $this->configurationProvider->getConfiguration($formIdentifier, $formIdentifierPath));
            $this->view->assign('extConf', $this->extensionConfiguration);
            $this->view->assign('beUser', $this->beUser);
        }
        $this->view->assign('formDatas', $formData);
    }

    /**
     * Update Configuration for current BE-User
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function updateConfigurationAction()
    {
        if ($this->request->hasArgument('formIdentifier') && $this->request->hasArgument('fields')) {
            $saveData = [];
            $formIdentifier = $this->request->getArgument('formIdentifier');
            $formIdentifierPath = $this->request->getArgument('formIdentifierPath');
            foreach ($this->request->getArgument('fields') as $key => $field) {
                $saveData[$key] = [
                    'identifier' => $key,
                    'label' => $field['label'],
                    'type' => $field['type'],
                    'enabled' => $field['enabled'],
                ];
            }
            $this->configurationProvider->updateConfiguration($this->request->getArgument('formIdentifier'), json_encode($saveData));
            $this->redirect(
                'listByFormIdentifier',
                'FormData',
                null,
                [
                    'formIdentifier' => $formIdentifier,
                    'formIdentifierPath' => $formIdentifierPath
                ]
            );
        } else {
            $this->redirect('index', 'FormData');
        }
    }
    /**
     * Reset Configuration for current BE-User
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function resetConfigurationAction()
    {
        if ($this->request->hasArgument('formIdentifier')) {
            $formIdentifier = $this->request->getArgument('formIdentifier');
            $formIdentifierPath = $this->request->getArgument('formIdentifierPath');

            $this->configurationProvider->deleteConfiguration($formIdentifier);

            $this->redirect(
                'listByFormIdentifier',
                'FormData',
                null,
                [
                    'formIdentifier' => $formIdentifier,
                    'formIdentifierPath' => $formIdentifierPath
                ]
            );
        } else {
            $this->redirect('index', 'FormData');
        }
    }

    /**
     * Delete an Email from form
     */
    public function deleteAction()
    {
        try {
            if ($this->extensionConfiguration['onlyAdminCanDelete'] && !$GLOBALS['BE_USER']->isAdmin()) {
                die('Access denied');
            }
            if (!empty(GeneralUtility::_POST('uid'))) {
                if ($this->formDataRepository->delete((int)GeneralUtility::_POST('uid'))) {
                    echo 'email-' . (int)GeneralUtility::_POST('uid');
                    die();
                }
            }

            echo 'not-deleted';
        } catch (\RuntimeException $exception) {
            throw new \RuntimeException('This email not deleted.', 1636164135);
        }
    }

    /**
     * Convert Emails form as .csv file
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    public function csvExportAction()
    {
        if ($this->request->hasArgument('formIdentifier')) {
            $formIdentifier = $this->request->getArgument('formIdentifier');
            $formIdentifierPath = $this->request->getArgument('formIdentifierPath');
            $formData = $this->formDataRepository->findByFormIdentifier($formIdentifier);
            $this->csvExportUtility->prepareEmailsAndDownloadAsCsv($formData, $formIdentifierPath);
        }
        die();
    }
}
