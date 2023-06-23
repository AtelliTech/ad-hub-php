<?php

namespace AtelliTech\AdHub\GoogleAds;

use AtelliTech\AdHub\AbstractService;
use AtelliTech\AdHub\CustomError;
use Exception;
use Google\ApiCore\ApiException;
use Google\Ads\GoogleAds\Lib\V14\GoogleAdsServerStreamDecorator;
use Google\Ads\GoogleAds\V14\Common\CrmBasedUserListInfo;
use Google\Ads\GoogleAds\V14\Common\CustomerMatchUserListMetadata;
use Google\Ads\GoogleAds\V14\Common\ExpressionRuleUserListInfo;
use Google\Ads\GoogleAds\V14\Common\RuleBasedUserListInfo;
use Google\Ads\GoogleAds\V14\Common\UserData;
use Google\Ads\GoogleAds\V14\Common\UserIdentifier;
use Google\Ads\GoogleAds\V14\Common\UserListRuleInfo;
use Google\Ads\GoogleAds\V14\Common\UserListRuleItemGroupInfo;
use Google\Ads\GoogleAds\V14\Common\UserListRuleItemInfo;
use Google\Ads\GoogleAds\V14\Common\UserListStringRuleItemInfo;
use Google\Ads\GoogleAds\V14\Enums\CustomerMatchUploadKeyTypeEnum\CustomerMatchUploadKeyType;
use Google\Ads\GoogleAds\V14\Enums\OfflineUserDataJobStatusEnum\OfflineUserDataJobStatus;
use Google\Ads\GoogleAds\V14\Enums\OfflineUserDataJobTypeEnum\OfflineUserDataJobType;
use Google\Ads\GoogleAds\V14\Enums\UserIdentifierSourceEnum\UserIdentifierSource;
use Google\Ads\GoogleAds\V14\Enums\UserListMembershipStatusEnum\UserListMembershipStatus;
use Google\Ads\GoogleAds\V14\Enums\UserListPrepopulationStatusEnum\UserListPrepopulationStatus;
use Google\Ads\GoogleAds\V14\Enums\UserListRuleTypeEnum\UserListRuleType;
use Google\Ads\GoogleAds\V14\Enums\UserListStringRuleItemOperatorEnum\UserListStringRuleItemOperator;
use Google\Ads\GoogleAds\V14\Enums\SharedSetTypeEnum\SharedSetType;
use Google\Ads\GoogleAds\V14\Resources\CampaignSharedSet;
use Google\Ads\GoogleAds\V14\Resources\Customer;
use Google\Ads\GoogleAds\V14\Resources\CustomerClient;
use Google\Ads\GoogleAds\V14\Resources\UserList;
use Google\Ads\GoogleAds\V14\Resources\OfflineUserDataJob;
use Google\Ads\GoogleAds\V14\Resources\SharedCriterion;
use Google\Ads\GoogleAds\V14\Resources\SharedSet;
use Google\Ads\GoogleAds\V14\Services\GoogleAdsRow;
use Google\Ads\GoogleAds\V14\Services\ListAccessibleCustomersResponse;
use Google\Ads\GoogleAds\V14\Services\MutateUserListResult;
use Google\Ads\GoogleAds\V14\Services\OfflineUserDataJobOperation;
use Google\Ads\GoogleAds\V14\Services\SharedSetOperation;
use Google\Ads\GoogleAds\V14\Services\UserDataOperation;
use Google\Ads\GoogleAds\V14\Services\UserListOperation;
use Google\Ads\GoogleAds\Util\V14\ResourceNames;
use Google\Protobuf\FieldMask;
use Google\Ads\GoogleAds\Util\FieldMasks;
use Traversable;

/**
 * This service is used to access GoogleAds Resources. Almost returned data are refering to Resource class of GoogleAds API.
 *
 * @see https://developers.google.com/google-ads/api/reference
 * @author Eric Huang <eric.huang@atelli.ai>
 */
class GoogleAdsService extends AbstractService
{
    /**
     * Get service name
     *
     * @return string
     */
    public function getName(): string
    {
        return 'GoogleAds';
    }

    /**
     * listAccessibleCustomers
     *
     * @return array<int, mixed>|bool
     */
    public function listAccessibleCustomers(): array|bool
    {
        try {
            $res = $this->client->getCustomerServiceClient()->listAccessibleCustomers();
            if ($res instanceof ListAccessibleCustomersResponse) {
                $data = [];
                foreach($res->getResourceNames() as $resource) {
                    list($category, $id) = explode('/', $resource);
                    $data[] = ['id'=>intval($id), 'resource_name'=>$resource];
                }

                return $data;
            }

            throw new Exception("Invalid response of listAccessibleCustomers of GoogleAdsService", 500);
        } catch (Exception $e) {
            $err = new CustomError($e->getMessage(), $e->getCode());
            $this->setCustomError($err);
            return false;
        }
    }

    /**
     * Get customer
     *
     * @param int $customerId
     * @param string[] $fields default: []
     * @return Customer|bool
     */
    public function getCustomer(int $customerId, array $fields = []): Customer|bool
    {
        try {
            if (empty($fields))
                $fields = [
                    'customer.resource_name',
                    'customer.pay_per_conversion_eligibility_failure_reasons',
                    'customer.optimization_score_weight',
                    'customer.status',
                    'customer.id',
                    'customer.descriptive_name',
                    'customer.currency_code',
                    'customer.time_zone',
                    'customer.tracking_url_template',
                    'customer.final_url_suffix',
                    'customer.auto_tagging_enabled',
                    'customer.has_partners_badge',
                    'customer.manager',
                    'customer.test_account',
                    'customer.optimization_score'
                ];

            $query = sprintf('select %s from customer limit 1', implode(',', $fields));
            return $this->queryOne($customerId, $query)->getCustomer();
        } catch (Exception $e) {
            $err = new CustomError($e->getMessage(), $e->getCode());
            $this->setCustomError($err);
            return false;
        }
    }

    /**
     * Get customer client
     *
     * @param int $customerClientId
     * @param string[] $fields default: []
     * @return CustomerClient|bool
     */
    public function getCustomerClient(int $customerClientId, array $fields = []): CustomerClient|bool
    {
        try {
            if (empty($fields))
                $fields = [
                    'customer_client.client_customer',
                    'customer_client.currency_code',
                    'customer_client.id',
                    'customer_client.level',
                    'customer_client.manager',
                    'customer_client.resource_name',
                    'customer_client.time_zone',
                    'customer_client.descriptive_name',
                ];

            $query = sprintf('select %s from customer_client limit 1', implode(',', $fields));
            return $this->queryOne($customerClientId, $query)->getCustomerClient();
        } catch (Exception $e) {
            $err = new CustomError($e->getMessage(), $e->getCode());
            $this->setCustomError($err);
            return false;
        }
    }

    /**
     * list customer client by root customer id
     *
     * @param int $customerId
     * @param string[] $fields default: []
     * @return Traversable<int, mixed>|bool
     */
    public function listCustomerClients(int $customerId, array $fields = []): Traversable|bool
    {
        try {
            if (empty($fields))
                $fields = [
                    'customer_client.client_customer',
                    'customer_client.currency_code',
                    'customer_client.id',
                    'customer_client.level',
                    'customer_client.manager',
                    'customer_client.resource_name',
                    'customer_client.time_zone',
                    'customer_client.descriptive_name',
                ];

            $query = sprintf('select %s from customer_client', implode(',', $fields));
            $stream = $this->queryAll($customerId, $query);
            return $stream->iterateAllElements();
        } catch (Exception $e) {
            $err = new CustomError($e->getMessage(), $e->getCode());
            $this->setCustomError($err);
            return false;
        }
    }

    /**
     * list all user lists
     *
     * @param int $customerClientId
     * @param string[] $fields default: []
     * @return Traversable<int, mixed>|bool
     */
    public function listUserLists(int $customerClientId, array $fields = []): Traversable|bool
    {
        try {
            if (empty($fields))
                $fields = [
                    'user_list.resource_name',
                    'user_list.id',
                    'user_list.read_only',
                    'user_list.name',
                    'user_list.description',
                    'user_list.integration_code',
                    'user_list.membership_life_span',
                    'user_list.size_for_display',
                    'user_list.size_for_search',
                    'user_list.eligible_for_search',
                    'user_list.eligible_for_display',
                    'user_list.match_rate_percentage'
                ];

            $query = sprintf('SELECT %s FROM user_list', implode(',', $fields));
            $stream = $this->queryAll($customerClientId, $query);
            return $stream->iterateAllElements();
        } catch (Exception $e) {
            $err = new CustomError($e->getMessage(), $e->getCode());
            $this->setCustomError($err);
            return false;
        }
    }

    /**
     * list campaign
     *
     * @param int $customerClientId
     * @param string[] $fields default: []
     * @return Traversable<int, mixed>|bool
     */
    public function listCampaigns(int $customerClientId, array $fields = []): Traversable|bool
    {
        try {
            if (empty($fields))
                $fields = [
                    'campaign.id',
                    'campaign.name'
                ];

            $query = sprintf('SELECT %s FROM campaign', implode(',', $fields));
            $stream = $this->queryAll($customerClientId, $query);
            return $stream->iterateAllElements();
        } catch (Exception $e) {
            $err = new CustomError($e->getMessage(), $e->getCode());
            $this->setCustomError($err);
            return false;
        }
    }

    /**
     * list submit form data
     *
     * @param int $customerClientId
     * @param string[] $fields default: []
     * @return Traversable<int, mixed>|bool
     */
    public function listFormData(int $customerClientId, array $fields = []): Traversable|bool
    {
        try {
            if (empty($fields))
                $fields = [
                    'lead_form_submission_data.ad_group',
                    'lead_form_submission_data.ad_group_ad',
                    'lead_form_submission_data.asset',
                    'lead_form_submission_data.campaign',
                    'lead_form_submission_data.custom_lead_form_submission_fields',
                    'lead_form_submission_data.gclid',
                    'lead_form_submission_data.id',
                    'lead_form_submission_data.lead_form_submission_fields',
                    'lead_form_submission_data.resource_name',
                    'lead_form_submission_data.submission_date_time',
                    'campaign.id',
                    'campaign.name',
                    'ad_group.id',
                    'ad_group.name',
                    'ad_group_ad.ad.final_urls',
                    'ad_group_ad.ad.id'
                ];

            $query = sprintf('SELECT %s FROM lead_form_submission_data', implode(',', $fields));
            $stream = $this->queryAll($customerClientId, $query);
            return $stream->iterateAllElements();
        } catch (Exception $e) {
            $err = new CustomError($e->getMessage(), $e->getCode());
            $this->setCustomError($err);
            return false;
        }
    }

    /**
     * list all shared set
     *
     * @param int $customerClientId
     * @param string[] $fields default: []
     * @return Traversable<int, mixed>|bool
     */
    public function listSharedSets(int $customerClientId, array $fields = []): Traversable|bool
    {
        try {
            if (empty($fields))
                $fields = [
                    'shared_set.id',
                    'shared_set.name',
                    'shared_set.resource_name',
                    'shared_set.status',
                    'shared_set.type',
                    'shared_set.member_count',
                    'shared_set.reference_count'
                ];

            $query = sprintf('SELECT %s FROM shared_set', implode(',', $fields));
            $stream = $this->queryAll($customerClientId, $query);
            return $stream->iterateAllElements();
        } catch (Exception $e) {
            $err = new CustomError($e->getMessage(), $e->getCode());
            $this->setCustomError($err);
            return false;
        }
    }

    /**
     * create shared set
     *
     * @param int $customerClientId
     * @param array<string, mixed> $data
     * @return \Google\Ads\GoogleAds\V14\Services\MutateSharedSetResult|bool
     */
    public function createSharedSet(int $customerClientId, array $data): \Google\Ads\GoogleAds\V14\Services\MutateSharedSetResult|bool
    {
        try {
            $sharedSet = new SharedSet([
                'name' => $data['name'],
                'type' => SharedSetType::value($data['type'])
            ]);
            $operation = new SharedSetOperation();
            $operation->setCreate($sharedSet);
            $response = $this->client->getSharedSetServiceClient()->mutateSharedSets($customerClientId, [$operation]);
            return $response->getResults()[0];
        } catch (Exception $e) {
            $err = new CustomError($e->getMessage(), $e->getCode());
            $this->setCustomError($err);
            return false;
        }
    }

    /**
     * update shared set
     *
     * @param int $customerClientId
     * @param int $sharedSetId
     * @param array<string, mixed> $data
     * @return \Google\Ads\GoogleAds\V14\Services\MutateSharedSetResult|bool
     */
    public function updateSharedSet(int $customerClientId, int $sharedSetId, array $data): \Google\Ads\GoogleAds\V14\Services\MutateSharedSetResult|bool
    {
        try {
            $data = array_merge($data, [
                'resource_name' => ResourceNames::forSharedSet($customerClientId, $sharedSetId), // 'customers/{customer_id}/sharedSets/{shared_set_id}
            ]);
            $sharedSet = new SharedSet($data);
            $operation = new SharedSetOperation();
            $operation->setUpdate($sharedSet);
            $operation->setUpdateMask(FieldMasks::allSetFieldsOf($sharedSet));
            $response = $this->client->getSharedSetServiceClient()->mutateSharedSets($customerClientId, [$operation]);
            return $response->getResults()[0];
        } catch (Exception $e) {
            $err = new CustomError($e->getMessage(), $e->getCode());
            $this->setCustomError($err);
            return false;
        }
    }

    /**
     * find one
     *
     * @param int $customerId
     * @return GoogleAdsRow
     */
    private function queryOne(int $customerId, string $query): GoogleAdsRow|null
    {
        return $this->client->getGoogleAdsServiceClient()->search($customerId, $query)
                                                         ->getIterator()
                                                         ->current();
    }

    /**
     * find all
     *
     * @param int $customerId
     * @return GoogleAdsServerStreamDecorator
     */
    private function queryAll(int $customerId, string $query): GoogleAdsServerStreamDecorator
    {
        return $this->client->getGoogleAdsServiceClient()->searchStream($customerId, $query);
    }
}
