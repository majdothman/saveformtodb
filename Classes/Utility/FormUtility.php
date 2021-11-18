<?php

namespace Othman\SaveFormToDb\Utility;

use Symfony\Component\Yaml\Yaml;

class FormUtility
{
    /**
     * @param string $formYamlPath path of FormSetup .yaml ex: EXT:Ext_KEY/Resources/Private/Form/contact.form.yaml
     * @return array
     */
    public function getRenderAblesFieldsOfYamlForm(string $formYamlPath): array
    {
        $fields = [];
        if (!file_exists(\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($formYamlPath))) {
            $resourceFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\ResourceFactory::class);
            $file = $resourceFactory->getFileObjectFromCombinedIdentifier($formYamlPath);
            $formYamlPath = $file->getPublicUrl();
        }
        try {
            $formYamlContentArray = Yaml::parseFile(\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($formYamlPath));
        } catch (\Exception $exception) {
            return $fields;
        }
        if (!empty($formYamlContentArray['renderables'])) {
            foreach ($formYamlContentArray['renderables'] as $key => $renderables) {
                foreach ($renderables['renderables'] as $fieldKey => $field) {
                    $fields[$field['identifier']]['identifier'] = $field['identifier'];
                    $fields[$field['identifier']]['label'] = $field['label'];
                    $fields[$field['identifier']]['type'] = $field['type'];
                }
            }
        }
        return $fields;
    }
}
