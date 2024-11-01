<?php

namespace AtelliTech\Ads\GoogleAds;

use AtelliTech\Ads\AbstractService;
use Exception;
use Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder;
use Google\Ads\GoogleAds\Lib\V17\GoogleAdsClientBuilder;
use Google\Ads\GoogleAds\Util\FieldMasks;
use Google\Ads\GoogleAds\Util\V17\ResourceNames;
use Google\Ads\GoogleAds\V17\Common\KeywordInfo;
use Google\Ads\GoogleAds\V17\Common\PlacementInfo;
use Google\Ads\GoogleAds\V17\Enums\KeywordMatchTypeEnum\KeywordMatchType;
use Google\Ads\GoogleAds\V17\Enums\SharedSetTypeEnum\SharedSetType;
use Google\Ads\GoogleAds\V17\Resources\CampaignSharedSet;
use Google\Ads\GoogleAds\V17\Resources\Customer;
use Google\Ads\GoogleAds\V17\Resources\CustomerClient;
use Google\Ads\GoogleAds\V17\Resources\SharedCriterion;
use Google\Ads\GoogleAds\V17\Resources\SharedSet;
use Google\Ads\GoogleAds\V17\Services\CampaignSharedSetOperation;
use Google\Ads\GoogleAds\V17\Services\ListAccessibleCustomersRequest;
use Google\Ads\GoogleAds\V17\Services\MutateCampaignSharedSetsResponse;
use Google\Ads\GoogleAds\V17\Services\MutateSharedSetResult;
use Google\Ads\GoogleAds\V17\Services\SearchGoogleAdsRequest;
use Google\Ads\GoogleAds\V17\Services\SearchGoogleAdsStreamRequest;
use Google\Ads\GoogleAds\V17\Services\SharedCriterionOperation;
use Google\Ads\GoogleAds\V17\Services\SharedSetOperation;
use Google\ApiCore\PagedListResponse;
use Google\ApiCore\ServerStream;
use Google\Protobuf\Internal\RepeatedField;
use Throwable;

/**
 * This service is used to access GoogleAds Resources. Almost returned data are refering to Resource class of GoogleAds API.
 *
 * @see https://developers.google.com/google-ads/api/reference
 *
 * @author Eric Huang <eric.huang@atelli.ai>
 */
class GoogleAdsService extends AbstractService
{
    /**
     * create service.
     *
     * @param array<string, mixed> $config
     * @return static
     */
    public static function create(array $config): static
    {
        $names = ['customerId', 'refreshToken', 'clientId', 'clientSecret', 'developToken'];
        foreach ($names as $name) {
            if (!isset($config[$name])) {
                throw new Exception("Undefined configuration {$name}", 404);
            }
        }

        extract($config);

        $oAuth2Credential = (new OAuth2TokenBuilder())->withClientId($config['clientId'])
            ->withClientSecret($config['clientSecret'])
            ->withRefreshToken($config['refreshToken'])
            ->build();

        $googleAdsClient = (new GoogleAdsClientBuilder())->withOAuth2Credential($oAuth2Credential)
            ->withDeveloperToken($config['developToken'])
            ->withLoginCustomerId($config['customerId'])
            ->build();

        return new static($googleAdsClient); // @phpstan-ignore-line
    }

    /**
     * Get service name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'GoogleAds';
    }

    /**
     * listAccessibleCustomers.
     *
     * @return array<int, array{id:int, resource_name:string}>
     */
    public function listAccessibleCustomers(): array
    {
        try {
            $res = $this->client->getCustomerServiceClient()->listAccessibleCustomers(new ListAccessibleCustomersRequest());

            $data = [];
            foreach ($res->getResourceNames() as $resource) {
                list($category, $id) = explode('/', $resource);
                $data[] = ['id' => intval($id), 'resource_name' => $resource];
            }

            return $data;
        } catch (Throwable $e) {
            throw new Exception('Invalid response of listAccessibleCustomers of GoogleAdsService', 500, $e);
        }
    }

    /**
     * Get customer.
     *
     * @param string $customerId
     * @param string[] $fields default: []
     * @return Customer
     */
    public function getCustomer(string $customerId, array $fields = []): Customer
    {
        try {
            if (empty($fields)) {
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
                    'customer.optimization_score',
                ];
            }

            $query = sprintf('select %s from customer limit 1', implode(',', $fields));

            return $this->query($customerId, $query)->getIterator()
                ->current()
                ->getCustomer();
        } catch (Throwable $e) {
            throw new Exception($e->getMessage(), 500, $e);
        }
    }

    /**
     * Get customer client.
     *
     * @param string $customerClientId
     * @param string[] $fields default: []
     * @return CustomerClient
     */
    public function getCustomerClient(string $customerClientId, array $fields = []): CustomerClient
    {
        try {
            if (empty($fields)) {
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
            }

            $query = sprintf('select %s from customer_client limit 1', implode(',', $fields));

            return $this->query($customerClientId, $query)->getIterator()
                ->current()
                ->getCustomerClient();
        } catch (Throwable $e) {
            throw new Exception($e->getMessage(), 500, $e);
        }
    }

    /**
     * list customer client by root customer id.
     *
     * @param string $customerId
     * @param string[] $fields default: []
     * @param array<string, mixed> $options default: []
     * @return ServerStream
     */
    public function listCustomerClients(string $customerId, array $fields = [], array $options = []): ServerStream
    {
        try {
            if (empty($fields)) {
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
            }

            $query = sprintf('select %s from customer_client', implode(',', $fields));

            return $this->queryStream($customerId, $query, $options);
        } catch (Throwable $e) {
            throw new Exception($e->getMessage(), 500, $e);
        }
    }

    /**
     * list all user lists.
     *
     * @param string $customerClientId
     * @param string[] $fields default: []
     * @param array<string, mixed> $options default: []
     * @return ServerStream
     */
    public function listUserLists(string $customerClientId, array $fields = [], array $options = []): ServerStream
    {
        try {
            if (empty($fields)) {
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
                    'user_list.match_rate_percentage',
                ];
            }

            $query = sprintf('SELECT %s FROM user_list', implode(',', $fields));

            return $this->queryStream($customerClientId, $query, $options);
        } catch (Throwable $e) {
            throw new Exception($e->getMessage(), 500, $e);
        }
    }

    /**
     * list campaign.
     *
     * @param string $customerClientId
     * @param string[] $fields default: []
     * @param array<string, mixed> $options default: []
     * @return ServerStream
     */
    public function listCampaigns(string $customerClientId, array $fields = [], array $options = []): ServerStream
    {
        try {
            if (empty($fields)) {
                $fields = [
                    'campaign.id',
                    'campaign.name',
                ];
            }

            $query = sprintf('SELECT %s FROM campaign', implode(',', $fields));

            return $this->queryStream($customerClientId, $query, $options);
        } catch (Throwable $e) {
            throw new Exception($e->getMessage(), 500, $e);
        }
    }

    /**
     * list submit form data.
     *
     * @param string $customerClientId
     * @param string[] $fields default: []
     * @param array<string, mixed> $options default: []
     * @return ServerStream
     */
    public function listFormData(string $customerClientId, array $fields = [], array $options = []): ServerStream
    {
        try {
            if (empty($fields)) {
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
                    'ad_group_ad.ad.id',
                ];
            }

            $query = sprintf('SELECT %s FROM lead_form_submission_data', implode(',', $fields));

            return $this->queryStream($customerClientId, $query, $options);
        } catch (Throwable $e) {
            throw new Exception($e->getMessage(), 500, $e);
        }
    }

    /**
     * list forms.
     *
     * @param string $customerClientId
     * @param string[] $fields default: []
     * @param array<string, mixed> $options default: []
     * @return ServerStream
     */
    public function listForms(string $customerClientId, array $fields = [], array $options = []): ServerStream
    {
        try {
            if (empty($fields)) {
                $fields = [
                    'asset.id',
                    'asset.name',
                    'asset.lead_form_asset.headline',
                ];
            }

            $query = sprintf('SELECT %s FROM asset WHERE asset.type="LEAD_FORM"', implode(',', $fields));

            return $this->queryStream($customerClientId, $query, $options);
        } catch (Throwable $e) {
            throw new Exception($e->getMessage(), 500, $e);
        }
    }

    /**
     * list all shared set.
     *
     * @param string $customerClientId
     * @param string[] $fields default: []
     * @param array<string, mixed> $options default: []
     * @return ServerStream
     */
    public function listSharedSets(string $customerClientId, array $fields = [], array $options = []): ServerStream
    {
        try {
            if (empty($fields)) {
                $fields = [
                    'shared_set.id',
                    'shared_set.name',
                    'shared_set.resource_name',
                    'shared_set.status',
                    'shared_set.type',
                    'shared_set.member_count',
                    'shared_set.reference_count',
                ];
            }

            $query = sprintf('SELECT %s FROM shared_set', implode(',', $fields));

            return $this->queryStream($customerClientId, $query, $options);
        } catch (Throwable $e) {
            throw new Exception($e->getMessage(), 500, $e);
        }
    }

    /**
     * create shared set.
     *
     * @param string $customerClientId
     * @param array<string, mixed> $data
     * @return MutateSharedSetResult
     */
    public function createSharedSet(string $customerClientId, array $data): MutateSharedSetResult
    {
        try {
            $sharedSet = new SharedSet([
                'name' => $data['name'],
                'type' => SharedSetType::value($data['type']),
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
     * update shared set.
     *
     * @param string $customerClientId
     * @param int $sharedSetId
     * @param array<string, mixed> $data
     * @return bool|MutateSharedSetResult
     */
    public function updateSharedSet(string $customerClientId, int $sharedSetId, array $data): bool|MutateSharedSetResult
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
     * create shared set criterion.
     *
     * @param string $customerClientId
     * @param int $sharedSetId
     * @param string $type default: [keyword, negativeKeyword, placement]
     * @param array<int, mixed> $data
     * @return bool|RepeatedField
     */
    public function createSharedSetCriterion(string $customerClientId, int $sharedSetId, string $type, array $data): bool|RepeatedField
    {
        try {
            $sharedSetResourceName = ResourceNames::forSharedSet(strval($customerClientId), strval($sharedSetId)); // 'customers/{customer_id}/sharedSets/{shared_set_id}
            $operations = [];
            foreach ($data as $item) {
                if ('keyword' === $type) {
                    $matchType = $item['match_type'] ?? 'broad'; // default: 'broad
                    $sharedCriterion = new SharedCriterion([
                        'keyword' => new KeywordInfo([
                            'text' => $item['text'],
                            'match_type' => KeywordMatchType::value($item['match_type']),
                        ]),
                        'shared_set' => $sharedSetResourceName,
                    ]);
                } elseif ('placement' === $type) {
                    $sharedCriterion = new SharedCriterion([
                        'placement' => new PlacementInfo([
                            'url' => $item['url'],
                        ]),
                        'shared_set' => $sharedSetResourceName,
                    ]);
                } else {
                    throw new Exception("Invalid type({$type})");
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
     * create campaign shared set.
     *
     * @param string $customerClientId
     * @param int $campaignId
     * @param int $sharedSetId
     * @return MutateCampaignSharedSetsResponse
     */
    public function createCampaignSharedSet(string $customerClientId, int $campaignId, int $sharedSetId): MutateCampaignSharedSetsResponse
    {
        try {
            $campaignResourceName = ResourceNames::forCampaign(strval($customerClientId), strval($campaignId)); // 'customers/{customer_id}/campaigns/{campaign_id}
            $sharedSetResourceName = ResourceNames::forSharedSet(strval($customerClientId), strval($sharedSetId)); // 'customers/{customer_id}/sharedSets/{shared_set_id}
            $campaignSharedSet = new CampaignSharedSet([
                'campaign' => $campaignResourceName,
                'shared_set' => $sharedSetResourceName,
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
     * query result.
     *
     * @param string $customerId
     * @param string $query
     * @param array<string, mixed> $options
     * @return PagedListResponse
     */
    public function query(string $customerId, string $query, array $options = []): PagedListResponse
    {
        $req = SearchGoogleAdsRequest::build($customerId, $query);

        return $this->client->getGoogleAdsServiceClient()->search($req);
    }

    /**
     * query result by stream.
     *
     * @param string $customerId
     * @param string $query
     * @param array<string, mixed> $options
     * @return ServerStream
     */
    public function queryStream(string $customerId, string $query, array $options = []): ServerStream
    {
        $req = SearchGoogleAdsStreamRequest::build($customerId, $query);

        return $this->client->getGoogleAdsServiceClient()->searchStream($req);
    }
}
