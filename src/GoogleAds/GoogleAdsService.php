<?php

namespace AtelliTech\AdHub\GoogleAds;

use AtelliTech\AdHub\AbstractService;
use Exception;
use Google\Ads\GoogleAds\Lib\V15\GoogleAdsServerStreamDecorator;
use Google\Ads\GoogleAds\Util\FieldMasks;
use Google\Ads\GoogleAds\Util\V15\ResourceNames;
use Google\Ads\GoogleAds\V15\Common\CrmBasedUserListInfo;
use Google\Ads\GoogleAds\V15\Common\CustomerMatchUserListMetadata;
use Google\Ads\GoogleAds\V15\Common\ExpressionRuleUserListInfo;
use Google\Ads\GoogleAds\V15\Common\KeywordInfo;
use Google\Ads\GoogleAds\V15\Common\PlacementInfo;
use Google\Ads\GoogleAds\V15\Common\RuleBasedUserListInfo;
use Google\Ads\GoogleAds\V15\Common\UserData;
use Google\Ads\GoogleAds\V15\Common\UserIdentifier;
use Google\Ads\GoogleAds\V15\Common\UserListRuleInfo;
use Google\Ads\GoogleAds\V15\Common\UserListRuleItemGroupInfo;
use Google\Ads\GoogleAds\V15\Common\UserListRuleItemInfo;
use Google\Ads\GoogleAds\V15\Common\UserListStringRuleItemInfo;
use Google\Ads\GoogleAds\V15\Enums\CustomerMatchUploadKeyTypeEnum\CustomerMatchUploadKeyType;
use Google\Ads\GoogleAds\V15\Enums\KeywordMatchTypeEnum\KeywordMatchType;
use Google\Ads\GoogleAds\V15\Enums\OfflineUserDataJobStatusEnum\OfflineUserDataJobStatus;
use Google\Ads\GoogleAds\V15\Enums\OfflineUserDataJobTypeEnum\OfflineUserDataJobType;
use Google\Ads\GoogleAds\V15\Enums\SharedSetTypeEnum\SharedSetType;
use Google\Ads\GoogleAds\V15\Enums\UserIdentifierSourceEnum\UserIdentifierSource;
use Google\Ads\GoogleAds\V15\Enums\UserListMembershipStatusEnum\UserListMembershipStatus;
use Google\Ads\GoogleAds\V15\Enums\UserListPrepopulationStatusEnum\UserListPrepopulationStatus;
use Google\Ads\GoogleAds\V15\Enums\UserListRuleTypeEnum\UserListRuleType;
use Google\Ads\GoogleAds\V15\Enums\UserListStringRuleItemOperatorEnum\UserListStringRuleItemOperator;
use Google\Ads\GoogleAds\V15\Resources\CampaignSharedSet;
use Google\Ads\GoogleAds\V15\Resources\Customer;
use Google\Ads\GoogleAds\V15\Resources\CustomerClient;
use Google\Ads\GoogleAds\V15\Resources\OfflineUserDataJob;
use Google\Ads\GoogleAds\V15\Resources\SharedCriterion;
use Google\Ads\GoogleAds\V15\Resources\SharedSet;
use Google\Ads\GoogleAds\V15\Resources\UserList;
use Google\Ads\GoogleAds\V15\Services\CampaignSharedSetOperation;
use Google\Ads\GoogleAds\V15\Services\GoogleAdsRow;
use Google\Ads\GoogleAds\V15\Services\ListAccessibleCustomersRequest;
use Google\Ads\GoogleAds\V15\Services\ListAccessibleCustomersResponse;
use Google\Ads\GoogleAds\V15\Services\MutateCampaignSharedSetsResponse;
use Google\Ads\GoogleAds\V15\Services\MutateSharedSetResult;
use Google\Ads\GoogleAds\V15\Services\MutateUserListResult;
use Google\Ads\GoogleAds\V15\Services\OfflineUserDataJobOperation;
use Google\Ads\GoogleAds\V15\Services\SearchGoogleAdsRequest;
use Google\Ads\GoogleAds\V15\Services\SearchGoogleAdsStreamRequest;
use Google\Ads\GoogleAds\V15\Services\SharedCriterionOperation;
use Google\Ads\GoogleAds\V15\Services\SharedSetOperation;
use Google\Ads\GoogleAds\V15\Services\UserDataOperation;
use Google\Ads\GoogleAds\V15\Services\UserListOperation;
use Google\ApiCore\PagedListResponse;
use Google\ApiCore\ServerStream;
use Google\Protobuf\FieldMask;
use Google\Protobuf\Internal\RepeatedField;
use Throwable;
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
     * @return array<int, array{id:int, resource_name:string}>
     */
    public function listAccessibleCustomers(): array
    {
        try {
            $res = $this->client->getCustomerServiceClient()->listAccessibleCustomers();

            $data = [];
            foreach($res->getResourceNames() as $resource) {
                list($category, $id) = explode('/', $resource);
                $data[] = ['id'=>intval($id), 'resource_name'=>$resource];
            }

            return $data;
        } catch (Throwable $e) {
            throw new Exception("Invalid response of listAccessibleCustomers of GoogleAdsService", 500, $e);
        }
    }

    /**
     * Get customer
     *
     * @param int $customerId
     * @param string[] $fields default: []
     * @return Customer
     */
    public function getCustomer(int $customerId, array $fields = []): Customer
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
            return $this->query($customerId, $query)->getIterator()
                                                    ->current()
                                                    ->getCustomer();
        } catch (Throwable $e) {
            throw new Exception($e->getMessage(), 500, $e);
        }
    }

    /**
     * Get customer client
     *
     * @param int $customerClientId
     * @param string[] $fields default: []
     * @return CustomerClient
     */
    public function getCustomerClient(int $customerClientId, array $fields = []): CustomerClient
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
            return $this->query($customerClientId, $query)->getIterator()
                                                          ->current()
                                                          ->getCustomerClient();
        } catch (Throwable $e) {
            throw new Exception($e->getMessage(), 500, $e);
        }
    }

    /**
     * list customer client by root customer id
     *
     * @param int $customerId
     * @param string[] $fields default: []
     * @param array<string, mixed> $options default: []
     * @return ServerStream
     */
    public function listCustomerClients(int $customerId, array $fields = [], array $options = []): ServerStream
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
            return $this->queryStream($customerId, $query, $options);
        } catch (Throwable $e) {
            throw new Exception($e->getMessage(), 500, $e);
        }
    }

    /**
     * list all user lists
     *
     * @param int $customerClientId
     * @param string[] $fields default: []
     * @param array<string, mixed> $options default: []
     * @return ServerStream
     */
    public function listUserLists(int $customerClientId, array $fields = [], array $options = []): ServerStream
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
            return $this->queryStream($customerClientId, $query, $options);
        } catch (Throwable $e) {
            throw new Exception($e->getMessage(), 500, $e);
        }
    }

    /**
     * list campaign
     *
     * @param int $customerClientId
     * @param string[] $fields default: []
     * @param array<string, mixed> $options default: []
     * @return ServerStream
     */
    public function listCampaigns(int $customerClientId, array $fields = [], array $options = []): ServerStream
    {
        try {
            if (empty($fields))
                $fields = [
                    'campaign.id',
                    'campaign.name'
                ];

            $query = sprintf('SELECT %s FROM campaign', implode(',', $fields));
            return $this->queryStream($customerClientId, $query, $options);
        } catch (Throwable $e) {
            throw new Exception($e->getMessage(), 500, $e);
        }
    }

    /**
     * list submit form data
     *
     * @param int $customerClientId
     * @param string[] $fields default: []
     * @param array<string, mixed> $options default: []
     * @return ServerStream
     */
    public function listFormData(int $customerClientId, array $fields = [], array $options = []): ServerStream
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
            return $this->queryStream($customerClientId, $query, $options);
        } catch (Throwable $e) {
            throw new Exception($e->getMessage(), 500, $e);
        }
    }

    /**
     * list all shared set
     *
     * @param int $customerClientId
     * @param string[] $fields default: []
     * @param array<string, mixed> $options default: []
     * @return ServerStream
     */
    public function listSharedSets(int $customerClientId, array $fields = [], array $options = []): ServerStream
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
            return $this->queryStream($customerClientId, $query, $options);
        } catch (Throwable $e) {
            throw new Exception($e->getMessage(), 500, $e);
        }
    }

    /**
     * create shared set
     *
     * @param int $customerClientId
     * @param array<string, mixed> $data
     * @return MutateSharedSetResult
     */
    public function createSharedSet(int $customerClientId, array $data): MutateSharedSetResult
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
        } catch (Throwable $e) {
            throw new Exception($e->getMessage(), 500, $e);
        }
    }

    /**
     * update shared set
     *
     * @param int $customerClientId
     * @param int $sharedSetId
     * @param array<string, mixed> $data
     * @return MutateSharedSetResult|bool
     */
    public function updateSharedSet(int $customerClientId, int $sharedSetId, array $data): MutateSharedSetResult|bool
    {
        try {
            $data = array_merge($data, [
                'resource_name' => ResourceNames::forSharedSet(strval($customerClientId), strval($sharedSetId)), // 'customers/{customer_id}/sharedSets/{shared_set_id}
            ]);
            $sharedSet = new SharedSet($data);
            $operation = new SharedSetOperation();
            $operation->setUpdate($sharedSet);
            $operation->setUpdateMask(FieldMasks::allSetFieldsOf($sharedSet));
            $response = $this->client->getSharedSetServiceClient()->mutateSharedSets($customerClientId, [$operation]);
            return $response->getResults()[0];
        } catch (Throwable $e) {
            throw new Exception($e->getMessage(), 500, $e);
        }
    }

    /**
     * create shared set criterion
     *
     * @param int $customerClientId
     * @param int $sharedSetId
     * @param string $type default: [keyword, negativeKeyword, placement]
     * @param array<int, mixed> $data
     * @return RepeatedField|bool
     */
    public function createSharedSetCriterion(int $customerClientId, int $sharedSetId, string $type, array $data): RepeatedField|bool
    {
        try {
            $sharedSetResourceName = ResourceNames::forSharedSet(strval($customerClientId), strval($sharedSetId)); // 'customers/{customer_id}/sharedSets/{shared_set_id}
            $operations = [];
            foreach ($data as $item) {
                if ($type === 'keyword') {
                    $matchType = $item['match_type'] ?? 'broad'; // default: 'broad
                    $sharedCriterion = new SharedCriterion([
                        'keyword' => new KeywordInfo([
                            'text' => $item['text'],
                            'match_type' => KeywordMatchType::value($item['match_type'])
                        ]),
                        'shared_set' => $sharedSetResourceName
                    ]);
                } elseif ($type === 'placement') {
                    $sharedCriterion = new SharedCriterion([
                        'placement' => new PlacementInfo([
                            'url' => $item['url']
                        ]),
                        'shared_set' => $sharedSetResourceName
                    ]);
                } else {
                    throw new Exception("Invalid type($type)");
                }

                $operation = new SharedCriterionOperation();
                $operation->setCreate($sharedCriterion);
                $operations[] = $operation;
            }

            $response = $this->client->getSharedCriterionServiceClient()->mutateSharedCriteria($customerClientId, $operations);
            return $response->getResults();
        } catch (Throwable $e) {
            throw new Exception($e->getMessage(), 500, $e);
        }
    }

    /**
     * create campaign shared set
     *
     * @param int $customerClientId
     * @param int $campaignId
     * @param int $sharedSetId
     * @return MutateCampaignSharedSetsResponse
     */
    public function createCampaignSharedSet(int $customerClientId, int $campaignId, int $sharedSetId): MutateCampaignSharedSetsResponse
    {
        try {
            $campaignResourceName = ResourceNames::forCampaign(strval($customerClientId), strval($campaignId)); // 'customers/{customer_id}/campaigns/{campaign_id}
            $sharedSetResourceName = ResourceNames::forSharedSet(strval($customerClientId), strval($sharedSetId)); // 'customers/{customer_id}/sharedSets/{shared_set_id}
            $campaignSharedSet = new CampaignSharedSet([
                'campaign' => $campaignResourceName,
                'shared_set' => $sharedSetResourceName
            ]);
            $operation = new CampaignSharedSetOperation();
            $operation->setCreate($campaignSharedSet);
            $response = $this->client->getCampaignSharedSetServiceClient()->mutateCampaignSharedSets($customerClientId, [$operation]);
            return $response->getResults()[0];
        } catch (Throwable $e) {
            throw new Exception($e->getMessage(), 500, $e);
        }
    }

    /**
     * query result
     *
     * @param int $customerId
     * @param string $query
     * @param array<string, mixed> $options
     * @return PagedListResponse
     */
    public function query(int $customerId, string $query, array $options = []): PagedListResponse
    {
        return $this->client->getGoogleAdsServiceClient()->search($customerId, $query, $options);
    }

    /**
     * query result by stream
     *
     * @param int $customerId
     * @param string $query
     * @param array<string, mixed> $options
     * @return ServerStream
     */
    public function queryStream(int $customerId, string $query, array $options = []): ServerStream
    {
        return $this->client->getGoogleAdsServiceClient()->searchStream($customerId, $query, $options);
    }
}
