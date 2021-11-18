<?php

namespace Othman\SaveFormToDb\Domain\Finishers;

use Othman\SaveFormToDb\Domain\Repository\FormDataRepository;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;

class SaveFormToDbFinisher extends AbstractFinisher
{
    // send more Options to Repository
    protected array $extraOptions;

    /**
     * @var \Othman\SaveFormToDb\Domain\Repository\FormDataRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected FormDataRepository $formDataRepository;

    public function injectFormDataRepository(FormDataRepository $formDataRepository)
    {
        $this->formDataRepository = $formDataRepository;
    }

    /**
     * SaveFormToDbFinisher constructor.
     */
    public function __construct(string $finisherIdentifier = '')
    {
        parent::__construct($finisherIdentifier);
    }

    /**
     * @return string|void|null
     */
    protected function executeInternal()
    {
        $lastDelimiter = strrpos($this->finisherContext->getFormRuntime()->getIdentifier(), '-');
        $this->extraOptions['pluginUid'] = substr($this->finisherContext->getFormRuntime()->getIdentifier(), $lastDelimiter + 1);
        $this->extraOptions['formIdentifier'] = substr($this->finisherContext->getFormRuntime()->getIdentifier(), 0, $lastDelimiter);
        $this->extraOptions['formIdentifierPath'] = $this->finisherContext->getFormRuntime()->getFormDefinition()->getPersistenceIdentifier();
        $formValues = $this->getFormValues();
        $this->formDataRepository->addData($formValues, $this->extraOptions);
    }

    /**
     * get all form Values
     */
    protected function getFormValues(): array
    {
        $formValues = [];
        foreach ($this->finisherContext->getFormRuntime()->getPages() as $page) {
            foreach ($page->getElementsRecursively() as $element) {
                if ($element->getType() !== 'Honeypot') {
                    if (strtolower($element->getType()) !== 'email'
                        && strtolower($element->getType()) !== 'multicheckbox'
                        && strtolower($element->getType()) !== 'fileupload'
                        && strtolower($element->getType()) !== 'imageupload') {
                        // if normal type
                        $formValues[$element->getIdentifier()]['value'] = $this->finisherContext->getFormValues()[$element->getIdentifier()];
                    } elseif (strtolower($element->getType()) === 'email') {
                        $formValues['senderEmail'] = $this->finisherContext->getFormValues()[$element->getIdentifier()];
                        $formValues[$element->getIdentifier()]['value'] = $this->finisherContext->getFormValues()[$element->getIdentifier()];
                    } elseif (strtolower($element->getType()) === 'multicheckbox') {
                        $keyAndValue = $this->finisherContext->getFormValues()[$element->getIdentifier()];
                        if (is_array($keyAndValue)) {
                            foreach ($keyAndValue as $key => $value) {
                                if (isset($element->getProperties()['options'][$value])) {
                                    $keyAndValue[$key] = $element->getProperties()['options'][$value];
                                }
                            }
                        }
                        $formValues[$element->getIdentifier()]['value'] = $keyAndValue;
                    } elseif ($this->finisherContext->getFormValues()[$element->getIdentifier()]) {
                        $formValues[$element->getIdentifier()]['value'] = $this->finisherContext->getFormValues()[$element->getIdentifier()]->getOriginalResource()->getName();
                    }
                    $formValues[$element->getIdentifier()]['label'] = $element->getLabel();
                    $formValues[$element->getIdentifier()]['type'] = $element->getType();
                }
            }
        }
        return $formValues;
    }
}
