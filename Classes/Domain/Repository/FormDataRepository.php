<?php

namespace Othman\SaveFormToDb\Domain\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Repository;

class FormDataRepository extends Repository
{
    protected string $table = 'tx_saveformtodb_domain_model_formdata';

    /**
     * Get all Emails from Database
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findAllFormsIdentifier()
    {
        $queryBuilder = $this->getConnectionForTable($this->table);

        return $queryBuilder->select('formIdentifier', 'formIdentifierPath')
            ->from($this->table)
            ->addSelectLiteral(
                $queryBuilder->expr()->count('uid', 'numberOfEmails')
            )
            ->groupBy('formIdentifier')
            ->execute()
            ->fetchAll();
    }

    /**
     * Get all Emails from Database
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function findAll()
    {
        $queryBuilder = $this->getConnectionForTable($this->table);
        return $queryBuilder->select('*')
            ->from($this->table)
            ->groupBy('formIdentifier')
            ->execute()
            ->fetchAll();
    }

    /**
     * Get all Emails of formIdentifier from Database
     * @param string $formIdentifier
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByFormIdentifier(string $formIdentifier)
    {
        $queryBuilder = $this->getConnectionForTable($this->table);
        return $queryBuilder->select('*')
            ->from($this->table)
            ->where(
                $queryBuilder->expr()->eq('formIdentifier', $queryBuilder->createNamedParameter($formIdentifier))
            )
            ->orderBy('uid', 'DESC')
            ->execute()
            ->fetchAll();
    }

    /**
     * add Email data to Database
     *
     * @param array $formValues
     * @param array $options
     * @return \Doctrine\DBAL\Driver\Statement|int
     */
    public function addData(array $formValues, array $options)
    {
        $queryBuilder = $this->getConnectionForTable($this->table);
        return $queryBuilder
            ->insert($this->table)
            ->values([
                'pid' => $GLOBALS['TSFE']->id,
                'senderEmail' => $formValues['email'],
                'values' => json_encode($formValues),
                'formIdentifier' => $options['formIdentifier'],
                'formIdentifierPath' => $options['formIdentifierPath'],
                'pluginUid' => $options['pluginUid'],
                'tstamp' => $GLOBALS['EXEC_TIME'],
                'crdate' => $GLOBALS['EXEC_TIME'],
            ])
            ->execute();
    }

    /**
     * Delete Email from DB
     * @param int $uid
     * @return \Doctrine\DBAL\Driver\ResultStatement|\Doctrine\DBAL\Driver\Statement|\Doctrine\DBAL\ForwardCompatibility\Result|int
     */
    public function delete(int $uid)
    {
        $queryBuilder = $this->getConnectionForTable($this->table);
        return $queryBuilder
            ->delete($this->table)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT))
            )
            ->execute();
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
