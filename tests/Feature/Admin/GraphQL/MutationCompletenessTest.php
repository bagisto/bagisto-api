<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * Mutation-response field-completeness guard.
 *
 * The read guard (FieldCompletenessTest) proves QUERY responses resolve. Mutations
 * go through a different serializer path, so this runs a representative CREATE for
 * each major resource with a valid input and an EXPLICIT full field selection on
 * the result, asserting no `errors` and that every selected field is non-null.
 * That catches the mutation-response field-resolution class (camelCase-null, the
 * delete-snapshot 500s) for the create path.
 *
 * Why explicit selections (not introspected): in the test environment the GraphQL
 * INTROSPECTION schema is shop-scoped (admin mutations execute fine, but
 * `__schema.mutationType` / `__type(Admin*)` return the shop schema), so field
 * discovery via introspection is unreliable here. Hard-coding the field list per
 * resource is reliable and makes the guard's coverage explicit.
 *
 * Transactional — every create rolls back. Serial — skips under --parallel.
 */
class MutationCompletenessTest extends AdminApiTestCase
{
    public function test_create_mutations_resolve_all_result_fields(): void
    {
        if (getenv('LARAVEL_PARALLEL_TESTING')) {
            $this->markTestSkipped('Mutation-completeness sweep runs serially.');
        }

        $admin = $this->createAdmin();
        $this->seedRequiredData();

        $channelId = (int) \DB::table('channels')->value('id');
        $groupId = (int) \DB::table('customer_groups')->value('id');
        $rnd = (string) rand(10000, 99999);
        $cur = chr(rand(65, 90)).chr(rand(65, 90)).chr(rand(65, 90)); // 3 letters for currency code

        // wrapper => [mutation, inputType, input, resultSelection]
        $cases = [
            'adminSettingsCurrency' => ['createAdminSettingsCurrency', 'createAdminSettingsCurrencyInput',
                ['code' => $cur, 'name' => 'Cur '.$rnd],
                'code name createdAt updatedAt'],

            'adminSettingsLocale' => ['createAdminSettingsLocale', 'createAdminSettingsLocaleInput',
                ['code' => 'lc'.$rnd, 'name' => 'Locale '.$rnd, 'direction' => 'ltr'],
                'code name direction createdAt updatedAt'],

            'adminCustomerGroup' => ['createAdminCustomerGroup', 'createAdminCustomerGroupInput',
                ['code' => 'grp'.$rnd, 'name' => 'Group '.$rnd],
                'code name isUserDefined createdAt updatedAt'],

            'adminSettingsTaxRate' => ['createAdminSettingsTaxRate', 'createAdminSettingsTaxRateInput',
                ['identifier' => 'tr'.$rnd, 'country' => 'US', 'taxRate' => 5.5, 'isZip' => false, 'zipCode' => '94043'],
                'identifier country taxRate isZip zipCode createdAt updatedAt'],

            'adminMarketingEvent' => ['createAdminMarketingEvent', 'createAdminMarketingEventInput',
                ['name' => 'Event '.$rnd, 'description' => 'desc', 'date' => '2027-01-01'],
                'name description date createdAt updatedAt'],

            'adminMarketingSearchSynonym' => ['createAdminMarketingSearchSynonym', 'createAdminMarketingSearchSynonymInput',
                ['name' => 'Syn '.$rnd, 'terms' => 'a,b,c'],
                'name terms createdAt updatedAt'],

            'adminMarketingTemplate' => ['createAdminMarketingTemplate', 'createAdminMarketingTemplateInput',
                ['name' => 'Tpl '.$rnd, 'status' => 'active', 'content' => '<p>hi</p>'],
                'name status content createdAt updatedAt'],

            'adminSettingsRole' => ['createAdminSettingsRole', 'createAdminSettingsRoleInput',
                ['name' => 'Role '.$rnd, 'description' => 'd', 'permissionType' => 'all'],
                'name description permissionType createdAt updatedAt'],

            'adminMarketingCartRule' => ['createAdminMarketingCartRule', 'createAdminMarketingCartRuleInput',
                ['name'          => 'CR '.$rnd, 'channels' => [$channelId], 'customerGroups' => [$groupId],
                    'couponType' => 0, 'actionType' => 'by_percent', 'discountAmount' => 10],
                'name couponType actionType discountAmount sortOrder conditionType createdAt updatedAt'],

            'adminCustomer' => ['createAdminCustomer', 'createAdminCustomerInput',
                ['firstName'          => 'Jane', 'lastName' => 'Doe', 'email' => 'qa'.$rnd.'@example.com',
                    'customerGroupId' => $groupId, 'sendPassword' => false, 'password' => 'secret123'],
                'firstName lastName name email status createdAt updatedAt'],

            'adminCmsPage' => ['createAdminCmsPage', 'createAdminCmsPageInput',
                ['urlKey'      => 'qa-page-'.$rnd, 'pageTitle' => 'QA '.$rnd, 'htmlContent' => '<p>x</p>',
                    'channels' => [$channelId]],
                'urlKey pageTitle createdAt updatedAt'],
        ];

        // Fields that are legitimately null on a fresh create (no value yet).
        $nullableOnCreate = ['updatedAt' => true];

        $failures = [];
        foreach ($cases as $wrapper => [$mutation, $inputType, $input, $select]) {
            $query = "mutation(\$input: {$inputType}!) { {$mutation}(input: \$input) { {$wrapper} { id _id $select } } }";
            $resp = $this->adminGraphQL($query, ['input' => $input], $admin);
            $errors = $resp->json('errors');
            $node = $resp->json("data.{$mutation}.{$wrapper}");

            if ($errors) {
                $failures[] = "$mutation: ".($errors[0]['message'] ?? '?').' @ '.json_encode($errors[0]['path'] ?? []);

                continue;
            }
            if ($node === null) {
                $failures[] = "$mutation: result payload `$wrapper` was null";

                continue;
            }
            // _id must resolve (the delete/serializer-path bugs nulled it).
            if (($node['_id'] ?? null) === null) {
                $failures[] = "$mutation: `_id` resolved null on the result";
            }
            // Every explicitly-selected field must resolve non-null (the camelCase-null class).
            foreach (explode(' ', trim($select)) as $field) {
                if (isset($nullableOnCreate[$field])) {
                    continue;
                }
                if (! array_key_exists($field, $node)) {
                    $failures[] = "$mutation: field `$field` missing from result";
                } elseif ($node[$field] === null) {
                    $failures[] = "$mutation: field `$field` resolved null on the result";
                }
            }
        }

        $this->assertSame([], $failures, "Admin create-mutation field-resolution failures:\n".implode("\n", $failures));
    }
}
