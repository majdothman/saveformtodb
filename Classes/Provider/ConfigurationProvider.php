<?php

namespace Othman\SaveFormToDb\Provider;

use Doctrine\DBAL\Driver\ResultStatement;
use Othman\SaveFormToDb\Utility\FormUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ConfigurationProvider
 * To get and update Configuration of current BE-User
 */
class ConfigurationProvider implements SingletonInterface
{
    protected string $table = 'tx_saveformtodb_configuration';
    protected int $userId;

    public function __construct()
    {
        $this->userId = (int)$GLOBALS['BE_USER']->user['uid'];
    }

    /**
     * get configuration of User for selected form identifier
     * @param string $formIdentifier
     * @param string $formIdentifierPath
     * @return mixed
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getConfiguration(string $formIdentifier, string $formIdentifierPath)
    {
        $configuration = $this->getConfigurationOfUser($formIdentifier);
        if (empty($configuration)) {
            $this->addConfiguration($formIdentifier, $formIdentifierPath);
            $configuration = $this->getConfigurationOfUser($formIdentifier);
        }
        if (!is_array(json_decode($configuration, true))) {
            $configuration = json_decode($configuration, true);
        }
        return json_decode($configuration, true);
    }

    /**
     * @param string $formIdentifier
     * @param string $configuration Json_encode
     * @return ResultStatement|\Doctrine\DBAL\Driver\Statement|\Doctrine\DBAL\ForwardCompatibility\Result|int
     */
    public function updateConfiguration(string $formIdentifier, string $configuration)
    {
        $queryBuilder = $this->getConnectionForTable($this->table);
        return $queryBuilder
            ->update($this->table)
            ->where(
                $queryBuilder->expr()->eq('userId', $queryBuilder->createNamedParameter($this->userId, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('formIdentifier', $queryBuilder->createNamedParameter($formIdentifier))
            )
            ->set('configuration', json_encode($configuration))
            ->set('crdate', $GLOBALS['EXEC_TIME'])
            ->execute();
    }

    /**
     * add configuration for current User with selected form
     * @param string $formIdentifier
     * @param string $formIdentifierPath
     * @return ResultStatement|\Doctrine\DBAL\Driver\Statement|\Doctrine\DBAL\ForwardCompatibility\Result|int
     */
    public function addConfiguration(string $formIdentifier, string $formIdentifierPath)
    {
        $formFields = $this->getFieldsOfFormIdentifierPath($formIdentifierPath);
        foreach ($formFields as $key => $formField) {
            $formFields[$key]['enabled'] = true;
        }
        $queryBuilder = $this->getConnectionForTable($this->table);
        return $queryBuilder
            ->insert($this->table)
            ->values([
                'userId' => (int)$this->userId,
                'configuration' => json_encode($formFields),
                'formIdentifier' => $formIdentifier,
                'tstamp' => $GLOBALS['EXEC_TIME'],
                'crdate' => $GLOBALS['EXEC_TIME'],
            ])
            ->execute();
    }

    public function deleteConfiguration(string $formIdentifier)
    {
        $queryBuilder = $this->getConnectionForTable($this->table);
        return $queryBuilder
            ->delete($this->table)
            ->where(
                $queryBuilder->expr()->eq('userId', $queryBuilder->createNamedParameter($this->userId, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('formIdentifier', $queryBuilder->createNamedParameter($formIdentifier)),
            )
            ->execute();
    }

    /**
     * get configuration of User for selected form identifier
     * @param string $formIdentifier
     * @return string|null
     */
    protected function getConfigurationOfUser(string $formIdentifier): ?string
    {
        $queryBuilder = $this->getConnectionForTable($this->table);
        $result = $queryBuilder->select('configuration')
            ->from($this->table)
            ->where(
                $queryBuilder->expr()->eq('userId', $queryBuilder->createNamedParameter($this->userId, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('formIdentifier', $queryBuilder->createNamedParameter($formIdentifier)),
            )
            ->setMaxResults(1)
            ->orderBy('uid', 'DESC')
            ->execute()
            ->fetchAll();
        if (empty($result['0'])) {
            return null;
        }
        return $result['0']['configuration'];
    }

    /**
     * @param string $formIdentifierPath
     * @return array
     */
    public function getFieldsOfFormIdentifierPath(string $formIdentifierPath): array
    {
        $fields = [];
        if (!empty($formIdentifierPath)) {
            $fields = $this->getRenderAblesFieldsOfYamlForm($formIdentifierPath);
        }
        return $fields;
    }

    /**
     * Get all renderables fields from Form
     * @param string $formYamlPath path of FormSetup .yaml ex: EXT:EX_KEY/Resources/Private/Form/contact.form.yaml
     * @return array
     */
    public function getRenderAblesFieldsOfYamlForm(string $formYamlPath): array
    {
        $formUtility = GeneralUtility::makeInstance(FormUtility::class);
        return $formUtility->getRenderAblesFieldsOfYamlForm($formYamlPath);
    }

    /**
     * Get QueryBuilder for Connection
     * @param string $table
     * @return QueryBuilder
     */
    protected function getConnectionForTable(string $table): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
    }
}
