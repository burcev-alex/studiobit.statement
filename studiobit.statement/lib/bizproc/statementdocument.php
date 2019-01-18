<?php

namespace Studiobit\Statement\BizProc;


use Studiobit\Statement\Entity\StatementTable;
use Bitrix\Bizproc\FieldType;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\GroupTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserGroupTable;
use Bitrix\Main\UserTable;

if (!Loader::includeModule('bizproc')) {
    return;
}

/**
 * Описывает типы документов и документы для модуля studiobit.statement.
 *
 * Определен один тип документа - "Торговая точка" с идентификатором "statement".
 *
 * @package Studiobit\Statement\BizProc
 */
class StatementDocument implements \IBPWorkflowDocument
{
    /**
     * @return array Кортеж из трех элементов:
     *      код модуля, полное квалифицированное имя класса документа, код типа документа.
     */
    static public function getComplexDocumentType()
    {
        return array('studiobit.statement', self::class, 'statement');
    }

    /**
     * @param int $statementId Идентификатор документа - торговой точки.
     * @return array Кортеж из трех элементов:
     *      код модуля, полное квалифицированное имя класса документа, идентификатор документа.
     */
    static public function getComplexDocumentId($statementId)
    {
        return array('studiobit.statement', self::class, $statementId);
    }

    /**
     * Определяет тип переданного документа.
     *
     * @return string Код типа документа.
     */
    static public function GetDocumentType()
    {
        return 'statement';
    }

	/**
	 * Возвращает документ по идентификатору.
	 *
	 * @param string $documentId
	 *
	 * @return array
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
    static public function GetDocument($documentId)
    {
        if (intval($documentId) <= 0) {
            throw new ArgumentException('Invalid statement ID.', 'documentId');
        }

        $dbStatement = StatementTable::getById($documentId);
        $statement = $dbStatement->fetch();

        return self::convertStatementToBp($statement);
    }

    /**
     * @param string $documentType
     * @return array Описание полей документа для БП.
     */
    static public function GetDocumentFields($documentType)
    {
        return array(
            'ID' => array(
                'Name' => Loc::getMessage('STB_STATEMENT_FIELD_ID'),
                'Type' => FieldType::INT,
                'Filterable' => true,
                'Editable' => false,
                'Required' => false,
            ),
            'UF_CLIENT' => array(
                'Name' => Loc::getMessage('STB_STATEMENT_FIELD_CLIENT'),
                'Type' => FieldType::STRING,
                'Filterable' => true,
                'Editable' => true,
                'Required' => true,
            ),
            'UF_RESPONSIBLE' => array(
                'Name' => Loc::getMessage('STB_STATEMENT_FIELD_ASSIGNED_BY_ID'),
                'Type' => FieldType::USER,
                'Filterable' => true,
                'Editable' => true,
                'Required' => false,
            ),
	        'UF_STATUS' => array(
		        'Name' => Loc::getMessage('STB_STATEMENT_FIELD_STATUS'),
		        'Type' => FieldType::INT,
		        'Filterable' => true,
		        'Editable' => true,
		        'Required' => false,
	        ),
	        'UF_STAGE_ID' => array(
		        'Name' => Loc::getMessage('STB_STATEMENT_FIELD_STAGE_ID'),
		        'Type' => FieldType::INT,
		        'Filterable' => true,
		        'Editable' => true,
		        'Required' => false,
	        ),
	        'UF_VIEW' => array(
		        'Name' => Loc::getMessage('STB_STATEMENT_FIELD_VIEW'),
		        'Type' => FieldType::SELECT,
		        'Filterable' => true,
		        'Editable' => false,
		        'Required' => false,
	        ),
	        'UF_NUMBER' => array(
		        'Name' => Loc::getMessage('STB_STATEMENT_FIELD_NUMBER'),
		        'Type' => FieldType::STRING,
		        'Filterable' => true,
		        'Editable' => true,
		        'Required' => false,
	        ),
	        'UF_IS_PAY' => array(
		        'Name' => Loc::getMessage('STB_STATEMENT_FIELD_IS_PAY'),
		        'Type' => FieldType::BOOL,
		        'Filterable' => true,
		        'Editable' => true,
		        'Required' => false,
	        ),
	        'UF_IS_DUPLICATE' => array(
		        'Name' => Loc::getMessage('STB_STATEMENT_FIELD_IS_DUPLICATE'),
		        'Type' => FieldType::BOOL,
		        'Filterable' => true,
		        'Editable' => true,
		        'Required' => false,
	        ),
	        'UF_IS_TERMINATION' => array(
		        'Name' => Loc::getMessage('STB_STATEMENT_FIELD_IS_TERMINATION'),
		        'Type' => FieldType::BOOL,
		        'Filterable' => true,
		        'Editable' => true,
		        'Required' => false,
	        ),
	        'UF_IS_CLIENT_DETAIL' => array(
		        'Name' => Loc::getMessage('STB_STATEMENT_FIELD_IS_CLIENT_DETAIL'),
		        'Type' => FieldType::BOOL,
		        'Filterable' => true,
		        'Editable' => true,
		        'Required' => false,
	        ),
	        'UF_IS_RETURN_PAID' => array(
		        'Name' => Loc::getMessage('STB_STATEMENT_FIELD_IS_RETURN_PAID'),
		        'Type' => FieldType::BOOL,
		        'Filterable' => true,
		        'Editable' => true,
		        'Required' => false,
	        ),
	        'UF_IS_OVERPAYMENT' => array(
		        'Name' => Loc::getMessage('STB_STATEMENT_FIELD_IS_OVERPAYMENT'),
		        'Type' => FieldType::BOOL,
		        'Filterable' => true,
		        'Editable' => true,
		        'Required' => false,
	        ),
	        'UF_IS_REPAYMENT' => array(
		        'Name' => Loc::getMessage('STB_STATEMENT_FIELD_IS_REPAYMENT'),
		        'Type' => FieldType::BOOL,
		        'Filterable' => true,
		        'Editable' => true,
		        'Required' => false,
	        ),
	        'UF_IS_REMODELING' => array(
		        'Name' => Loc::getMessage('STB_STATEMENT_FIELD_IS_REMODELING'),
		        'Type' => FieldType::BOOL,
		        'Filterable' => true,
		        'Editable' => true,
		        'Required' => false,
	        ),
	        'UF_IS_PARKING' => array(
		        'Name' => Loc::getMessage('STB_STATEMENT_FIELD_IS_PARKING'),
		        'Type' => FieldType::BOOL,
		        'Filterable' => true,
		        'Editable' => true,
		        'Required' => false,
	        ),
	        'UF_FARMER_ID' => array(
		        'Name' => Loc::getMessage('STB_STATEMENT_FIELD_FARMER_ID'),
		        'Type' => FieldType::USER,
		        'Filterable' => true,
		        'Editable' => true,
		        'Required' => false,
	        ),
	        'UF_OBJECT_ID' => array(
		        'Name' => Loc::getMessage('STB_STATEMENT_FIELD_OBJECT_ID'),
		        'Type' => FieldType::INT,
		        'Filterable' => true,
		        'Editable' => true,
		        'Required' => false,
	        ),
        );
    }

    /**
     * Создает документ - заявление.
     *
     * @param $parentDocumentId
     * @param array $arFields Значения полей согласно описанию из GetDocumentFields.
     * @return int ID созданного документа.
     * @throws \Exception
     */
    static public function CreateDocument($parentDocumentId, $arFields)
    {
        $result = StatementTable::add(self::convertStatementFromBp($arFields));

        if ($result->isSuccess()) {
            \CBPDocument::AutoStartWorkflows(
                self::getComplexDocumentType(),
                \CBPDocumentEventType::Create,
                self::getComplexDocumentId($result->getId()),
                array(),
                $errors
            );
        }

        return $result->getId();
    }

    /**
     * Изменяет значения полей документа.
     *
     * @param string $documentId
     * @param array $arFields Новые значения полей согласно описанию из GetDocumentFields.
     * @throws \Exception
     */
    static public function UpdateDocument($documentId, $arFields)
    {
        $result = StatementTable::update($documentId, self::convertStatementFromBp($arFields));

        if ($result->isSuccess()) {
            \CBPDocument::AutoStartWorkflows(
                self::getComplexDocumentType(),
                \CBPDocumentEventType::Edit,
                self::getComplexDocumentId($documentId),
                array(),
                $errors
            );
        }
    }

    /**
     * Удаляет документ.
     *
     * @param string $documentId
     * @throws \Exception
     */
    static public function DeleteDocument($documentId)
    {
        StatementTable::delete($documentId);
    }

    /**
     * Проверяет права пользователя на указанный документ.
     *
     * @param int $operation См. константы CBPCanUserOperateOperation::*, кроме CreateWorkflow.
     * @param int $userId
     * @param int $documentId
     * @param array $arParameters Вспомогателные параметры, например:
     *     DocumentStates - массив состояний БП данного документа;
     *     WorkflowId - код бизнес-процесса.
     * @return bool true, если операция разрешена.
     */
    static public function CanUserOperateDocument($operation, $userId, $documentId, $arParameters = array())
    {
        return true;
    }

    /**
     * Проверяет права пользователя на указанный тип документа.
     *
     * @param int $operation CBPCanUserOperateOperation: WriteDocument и CreateWorkflow.
     * @param int $userId
     * @param string $documentType
     * @param array $arParameters
     * @return bool true, если операция разрешена.
     */
    static public function CanUserOperateDocumentType($operation, $userId, $documentType, $arParameters = array())
    {
        return true;
    }

    /**
     * @param int|string $documentId
     * @return string Путь к карточке документа (в административной панели - если предусмотрено,
     *     иначе - какой есть).
     */
    static public function GetDocumentAdminPage($documentId)
    {
        return \CComponentEngine::makePathFromTemplate(
            Option::get('studiobit.statement', 'STORE_DETAIL_TEMPLATE'),
            array('STORE_ID' => $documentId)
        );
    }

    /**
     * Возвращает логические группы пользователей, имеющие смысл в рамках документа.
     *
     * Например, группа "Ответственный" включает одного пользователя - ответственного за заявление.
     *
     * @param string $documentType
     * @return string[] Ключ - идентификатор группы, значение - название на текущем языке.
     *     Правила формирования идентификаторов выбирает разработчик документа.
     */
    static public function GetAllowableUserGroups($documentType)
    {
        $dbAdminGroup = GroupTable::getById(1);
        $adminGroup = $dbAdminGroup->fetch();

        return array(
            'Author' => Loc::getMessage('STB_STATEMENT_GROUP_AUTHOR'),
            'group_1' => $adminGroup['NAME']
        );
    }

    /**
     * Возвращает пользователей, входящих в группу.
     *
     * @param string $group Один из идентификаторов групп, полученный от GetAllowableUserGroups.
     * @param int $documentId
     * @return int[] Идентификаторы пользователей, входящих в группу.
     */
    static public function GetUsersFromUserGroup($group, $documentId)
    {
        $group = strtolower($group);

        if ($group == 'author') {
            if (intval($documentId) > 0) {
                $dbStatement = StatementTable::getById($documentId);
                $statement = $dbStatement->fetch();
                return array($statement['ASSIGNED_BY_ID']);
            } else {
                return array();
            }
        }

        $groupId = intval(str_replace('group_', '', $group));
        if ($groupId <= 0) {
            return array();
        }

        return \CGroup::GetGroupUser($groupId);
    }

    /**
     * Конвертирует данные торговой точки, полученные из StatementTable в формат,
     * необходимый модулю БП.
     *
     * Например, для поля документа типа FieldType::USER значение должно быть не просто
     * идентификатором, а с префиксом "user_".
     *
     * Обратите внимание, что метод должен учитывать отсутствие некоторых полей в массиве.
     *
     * @param array $statement
     * @return array
     */
    static private function convertStatementToBp($statement)
    {
        if (isset($statement['ASSIGNED_BY_ID'])) {
            $statement['ASSIGNED_BY_ID'] = 'user_' . $statement['ASSIGNED_BY_ID'];
        }

        return $statement;
    }

    /**
     * Конвертирует данные торговой точки, полученные от БП в формат, необходимый StatementTable.
     *
     * Например, для поля документа типа FieldType::USER значение будет не просто
     * идентификатором, а с префиксом "user_". Префикс нужно удалить.
     *
     * Обратите внимание, что метод должен учитывать отсутствие некоторых полей в массиве.
     *
     * @param $statement
     * @return mixed
     */
    static private function convertStatementFromBp($statement)
    {
        if (isset($statement['ASSIGNED_BY_ID'])) {
            $statement['ASSIGNED_BY_ID'] = str_replace('user_', '', $statement['ASSIGNED_BY_ID']);
        }

        return $statement;
    }



    /**
     * Преобразует данные документа в массив для сохранения в истории.
     *
     * Используется службой истории документов.
     *
     * @param string $documentId
     * @param $historyIndex
     * @return array Массив, описывающий данные докумнта.
     */
    static public function GetDocumentForHistory($documentId, $historyIndex)
    {
        return self::GetDocument($documentId);
    }

    /**
     * Преобразует сохраненные ранее данные документа и сохраняет их в БД.
     *
     * Используется службой истории документов.
     *
     * @param string $documentId
     * @param array $arDocument Массив данных документа, полученный с помощью GetDocumentForHistory.
     * @throws \Exception
     */
    static public function RecoverDocumentFromHistory($documentId, $arDocument)
    {
        StatementTable::update($documentId, self::convertStatementFromBp($arDocument));
    }

    /**
     * Делает документ доступным в публичной части сайта.
     *
     * Для торговых точек не предусмотрено разделение на административный
     * и публичный интерфейс как в инфоблоках.
     *
     * @param string $documentId
     * @return bool
     */
    static public function PublishDocument($documentId)
    {
        return false;
    }

    /**
     * Делает документ недоступным в публичной части сайта.
     *
     * Для торговых точек не предусмотрено разделение на административный
     * и публичный интерфейс, как в инфоблоках.
     *
     * @param string $documentId
     * @return bool
     */
    static public function UnpublishDocument($documentId)
    {
        return false;
    }

    /**
     * Блокирует документ для данного БП. Заблокированный документ может
     * изменяться только указанным БП.
     *
     * Для торговых точек блокировка не поддерживается.
     *
     * @param string $documentId
     * @param string $workflowId
     * @return bool
     */
    static public function LockDocument($documentId, $workflowId)
    {
        return true;
    }

    /**
     * Разблокирует документ.
     *
     * Для торговых точек блокировка не поддерживается.
     *
     * @param string $documentId
     * @param string $workflowId
     * @return bool
     */
    static public function UnlockDocument($documentId, $workflowId)
    {
        return true;
    }

    /**
     * @param string $documentId
     * @param string $workflowId
     * @return bool true, если указанный БП обладает блокировкой на документ.
     */
    static public function IsDocumentLocked($documentId, $workflowId)
    {
        return false;
    }

    /**
     * Определяет состав операций над документом для последующего определения
     * прав в бизнес-процессах на статусах. Эти права отображаются на права
     * доступа к документу.
     *
     * @param string $documentType
     * @return array Ключ - идентификатор операции, значение - название операции на текущем языке.
     *     Например: array('read' => 'Чтение', 'update' => 'Изменение')
     */
    static public function GetAllowableOperations($documentType)
    {
        return array();
    }
}