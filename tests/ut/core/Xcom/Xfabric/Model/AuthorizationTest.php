<?php
/**
 * Test class for Xcom_Xfabric_Model_Authorization
 */
class Xcom_Xfabric_Model_AuthorizationTest extends Xcom_TestCase
{
    /* @var Xcom_Xfabric_Model_Authorization */
    protected $_object;

    protected $_validFormattedAuthArray = array(
        'destination_id' => 'REkAAa7d+StVHscZ5YPIhguFVgUmNRqjzlsJHWDzE/pbFyCly5pj81zou2TeOeypkV/zCQ==',
        'capability_id' => '210',
        'capability_name' => 'Magento Store Front',
        'fabric_url' => 'https://127.0.0.1:8080',
        'authorizations' => array(
            'xfabric' => array(
                'tenant_id' => '',
                'bearer_token' => 'Bearer QkEAAdJI8BfunKPxt9gsT9ufHXuSx+O+aj7sTjmIAmvW9rPvK92vZ9hKIKHEhZhiTwIJsQ==',
                'tenant_pseudonym' => '',
                'status' => 'VALID',
                'tenant_name' => ''
            ),
            'tenant' => array(
                'tenant_id' => '1',
                'bearer_token' => 'Bearer qGyqrImfo5pGsxTfwGKbdxc58hjwEU1034lQg8g4Yli5R0TdJBdLhJ2ptr2jEPaRpgbODNHf',
                'tenant_pseudonym' => '47JhxfVpmMreNLWFg4dqadBBNcidF+1C8MU6mtmGgfdCnlwnYEmv9oGIWQrQe8thCLsEFB/9',
                'status' => 'VALID',
                'tenant_name' => 'Soccer Pro'
            ),
            'self' => array(
                'tenant_id' => '1000000017',
                'bearer_token' => 'Bearer QUkAAXN37at9lIngeEO9j4eo0Mf0An+OBScKhJ7pe1YaSFKwZnB2exOlgM3g29KrULKZVQ==',
                'tenant_pseudonym' => 'VEkAAQsENZQ/07WgQyNg8l7jjmy7uHozVkcncKLhdHKvjUDlnTI6rRAUdaJB7oMgNge/lQ==',
                'status' => 'VALID',
                'tenant_name' => 'SELF-Magento Store Front'
            ),
        )
    );

    protected $_importFileContent = <<<TEXT
{
    "authorizations": [
        {
            "tenantId": null,
            "bearerToken": "Bearer QkEAAdJI8BfunKPxt9gsT9ufHXuSx+O+aj7sTjmIAmvW9rPvK92vZ9hKIKHEhZhiTwIJsQ==",
            "tenantPseudonym": null,
            "status": "VALID",
            "type": "XFABRIC",
            "tenantName": null
        },
        {
            "tenantId": "1",
            "bearerToken": "Bearer qGyqrImfo5pGsxTfwGKbdxc58hjwEU1034lQg8g4Yli5R0TdJBdLhJ2ptr2jEPaRpgbODNHf",
            "tenantPseudonym": "47JhxfVpmMreNLWFg4dqadBBNcidF+1C8MU6mtmGgfdCnlwnYEmv9oGIWQrQe8thCLsEFB/9",
            "status": "VALID",
            {{TYPE_TENANT}}
            "tenantName": "Soccer Pro"
        },
        {
            "tenantId": "1000000017",
            "bearerToken": "Bearer QUkAAXN37at9lIngeEO9j4eo0Mf0An+OBScKhJ7pe1YaSFKwZnB2exOlgM3g29KrULKZVQ==",
            "tenantPseudonym": "VEkAAQsENZQ/07WgQyNg8l7jjmy7uHozVkcncKLhdHKvjUDlnTI6rRAUdaJB7oMgNge/lQ==",
            "status": "VALID",
            "type": "{{SELF_TYPE}}",
            "tenantName": "SELF-Magento Store Front"
        }
    ],
    "destinationId": "REkAAa7d+StVHscZ5YPIhguFVgUmNRqjzlsJHWDzE/pbFyCly5pj81zou2TeOeypkV/zCQ==",
    "capabilityId": "210",
    "capabilityName": "Magento Store Front",
    "fabricURL": "{{URL}}"{{COMMA}}
    {{UNKNOWN_FIELD}}
}
TEXT;

    public function setUp()
    {
        $this->_object = new Xcom_Xfabric_Model_Authorization();
    }

    public function testIsValid()
    {
        $this->_object->validate($this->_validFormattedAuthArray);

        for ($i = 0; $i < 10; $i++) {
            switch ($i) {
                case 0:
                    $testArray = 'not an array';
                    break;
                case 1:
                    $testArray = $this->_validFormattedAuthArray;
                    unset($testArray['capability_name']);
                    break;
                case 2:
                    $testArray = $this->_validFormattedAuthArray;
                    unset($testArray['authorizations']);
                    break;
            }

            try {
                $this->_object->validate($testArray);
                $this->fail('Exception has not been thrown');
            } catch (Xcom_Xfabric_Exception $e) {
            } catch (Exception $e) {
                $this->fail('Wrong exception type');
            }
        }


        try {
            $testArray = $this->_validFormattedAuthArray;
            unset($testArray['authorizations']['xfabric']);
            $this->_object->validate($testArray);
            $this->fail('Exception has not been thrown');
        } catch (Xcom_Xfabric_Exception $e) {
        } catch (Exception $e) {
            $this->fail('Wrong exception type');
        }

        try {
            $testArray = $this->_validFormattedAuthArray;
            unset($testArray['authorizations']['xfabric']);
            $testArray['authorizations']['unknown_type'] = $this->_validFormattedAuthArray['authorizations']['self'];
            $this->_object->validate($testArray);
            $this->fail('Exception has not been thrown');
        } catch (Xcom_Xfabric_Exception $e) {
        } catch (Exception $e) {
            $this->fail('Wrong exception type');
        }

        try {
            $testArray = $this->_validFormattedAuthArray;
            unset($testArray['authorizations']['xfabric']['bearer_token']);
            $this->_object->validate($testArray);
            $this->fail('Exception has not been thrown');
        } catch (Xcom_Xfabric_Exception $e) {
        } catch (Exception $e) {
            $this->fail('Wrong exception type');
        }

        try {
            $testArray = $this->_validFormattedAuthArray;
            $testArray['authorizations']['xfabric']['bearer_token'] = '';
            $this->_object->validate($testArray);
            $this->fail('Exception has not been thrown');
        } catch (Xcom_Xfabric_Exception $e) {
        } catch (Exception $e) {
            $this->fail('Wrong exception type');
        }

        try {
            $testArray = $this->_validFormattedAuthArray;
            $testArray['authorizations']['xfabric']['bearer_token'] = '';
            $this->_object->validate($testArray);
            $this->fail('Exception has not been thrown');
        } catch (Xcom_Xfabric_Exception $e) {
        } catch (Exception $e) {
            $this->fail('Wrong exception type');
        }
    }

    protected function _getImportFileContent($case = 'normal')
    {
        $content = $this->_importFileContent;

        switch ($case) {
            case 'normal':
                break;
            case 'wrong_url':
                $content = str_replace('{{URL}}', 'wrong_url', $content);
                break;
            case 'url_no_port':
                $content = str_replace('{{URL}}', 'https://www.domain.com/', $content);
                break;
            case 'unknown_field':
                $content = str_replace('{{COMMA}}', ',', $content);
                $content = str_replace('{{UNKNOWN_FIELD}}', '"unknownField" : "some value"', $content);
                break;
            case 'no_tenant_type':
                $content = str_replace('{{TYPE_TENANT}}', '', $content);
                break;
            case 'wrong_type':
                $content = str_replace('{{SELF_TYPE}}', 'WRONG', $content);
                break;
        }

        $content = str_replace('{{URL}}', 'https://www.mydomain.com:7890', $content);
        $content = str_replace('{{COMMA}}', '', $content);
        $content = str_replace('{{UNKNOWN_FIELD}}', '', $content);
        $content = str_replace('{{TYPE_TENANT}}', '"type": "TENANT",', $content);
        $content = str_replace('{{SELF_TYPE}}', 'SELF', $content);

        return $content;
    }

    public function testImportFile()
    {
        $this->markTestIncomplete('In Progress');
        $contentCases = array(
            1,
            '',
            '{"authorizations" : [}',
            '{"authorizations":[]}',
            $this->_getImportFileContent('wrong_url'),
            $this->_getImportFileContent('unknown_field'),
            $this->_getImportFileContent('wrong_type'),
        );

        foreach ($contentCases as $content) {
            try {
                $this->_object->importFile($content);
            } catch (Xcom_Xfabric_Exception $e) {
                $this->assertFalse($this->_object->hasAuthorizationData());
            } catch (Exception $e) {
                $this->fail('Wrong exception type, message: ' . $e->getMessage());
            }
        }

        $this->_object->importFile($this->_getImportFileContent());
        $this->assertTrue($this->_object->hasAuthorizationData());
        $this->assertTrue($this->_object->getFabricUrl() == 'https://www.mydomain.com:7890');

        $this->_object->importFile($this->_getImportFileContent('url_no_port'));
        $this->assertTrue($this->_object->hasAuthorizationData());
        $this->assertTrue($this->_object->getFabricUrl() == 'https://www.domain.com/');
    }

    public function testSetAuthorizationData()
    {

        try {
            $testArray = $this->_validFormattedAuthArray;
            unset($testArray['authorizations']['xfabric']['bearer_token']);
            $this->_object->setAuthorizationData($testArray);
        } catch (Xcom_Xfabric_Exception $e) {
            $this->assertFalse($this->_object->hasAuthorizationData());
        } catch (Exception $e) {
            $this->fail('Wrong exception type');
        }

        $this->_object->setAuthorizationData($this->_validFormattedAuthArray);
        $this->assertTrue($this->_object->hasAuthorizationData());

        $this->assertNotEmpty($this->_object->getFabricUrl());
        $this->assertNotEmpty($this->_object->getDestinationId());
        $this->assertNotEmpty($this->_object->getCapabilityId());
        $this->assertNotEmpty($this->_object->getCapabilityName());
        $this->assertNotEmpty($this->_object->getAuthorizations());
        $this->assertNotEmpty($this->_object->getAuthorizations()->getSelf());
        $this->assertNotEmpty($this->_object->getAuthorizations()->getXfabric());
        $this->assertNotEmpty($this->_object->getAuthorizations()->getTenant());
        $this->assertNotEmpty($this->_object->getAuthorizations()->getSelf()->getTenantId());
        $this->assertNotEmpty($this->_object->getAuthorizations()->getSelf()->getBearerToken());
        $this->assertNotEmpty($this->_object->getAuthorizations()->getSelf()->getTenantPseudonym());
        $this->assertNotEmpty($this->_object->getAuthorizations()->getSelf()->getStatus());
        $this->assertNotEmpty($this->_object->getAuthorizations()->getSelf()->getTenantName());
    }

    protected function _paramsForSaveConfig()
    {
        $data = array();
        $configPath = 'xfabric/connection_settings/';

        foreach (array('capability_name', 'capability_id', 'fabric_url', 'destination_id') as $stringField) {
            $data[] = array($configPath . $stringField, $this->_validFormattedAuthArray[$stringField]);
        }

        $configPath .= 'authorizations/';

        foreach (array('self', 'tenant', 'xfabric') as $type) {
            foreach (array('status', 'bearer_token', 'tenant_name', 'tenant_id', 'tenant_pseudonym') as $field) {
                $data[] = array(
                    $configPath . $type . '/' . $field, $this->_validFormattedAuthArray['authorizations'][$type][$field]
                );
            }
        }

        return $data;
    }

    public function testSave()
    {
        $this->markTestIncomplete('In Progress');
        $this->_object->setData($this->_validFormattedAuthArray);
        $configModel = $this->mockModel('core/config', array('saveConfig'));
        $i = 0;

        foreach ($this->_paramsForSaveConfig() as $in) {
            $configModel->expects($this->at($i))->method('saveConfig')->with($in[0], $in[1]);
            $i++;
        }

        $this->_object->save();
    }
}
