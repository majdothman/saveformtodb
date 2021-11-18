<?php

if (defined('TYPO3_MODE') && TYPO3_MODE === 'BE') {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'othman_saveformtodb',
        'web',
        'tx_saveformtodb_domain_model_formdata',
        'after:FormFormbuilder',
        [
            \Othman\SaveFormToDb\Controller\FormDataController::class => 'index,listByFormIdentifier,delete,search,updateConfiguration,resetConfiguration,csvExport'
        ],
        [
            'labels' => 'LLL:EXT:othman_saveformtodb/Resources/Private/Language/locallang_mod.xlf',
            'access' => 'user,group',
            'iconIdentifier' => 'actions-database-export',
            'navigationComponentId' => 'TYPO3/CMS/Backend/PageTree/PageTreeElement',
            'inheritNavigationComponentFromMainModule' => false,
        ]
    );
}
