<?php
class Xcom_Test_Config extends Mage_Core_Model_Config
{
    public function saveConfig($path, $value, $scope = 'default', $scopeId = 0)
    {
        if (Mage::registry('disable_save_config')) {
            return true;
        }
        return parent::saveConfig($path, $value, $scope, $scopeId);
    }

    public function cleanCache()
    {
        if (Mage::registry('disable_clean_cache')) {
            return true;
        }
        return parent::cleanCache();
    }

    protected function _getDeclaredModuleFiles()
    {
        $declaredModuleFiles = parent::_getDeclaredModuleFiles();
        $moduleFiles = glob(BP . DS . 'tests' . DS . 'integration' . DS . 'etc' . DS . 'modules' . DS . '*.xml');

        if ($moduleFiles) {
            foreach ($moduleFiles as $v) {
                $declaredModuleFiles[] = $v;
            }
        }

        return $declaredModuleFiles;
    }

    public function getModuleDir($type, $moduleName)
    {
        if (strtolower(substr($moduleName, 0, 5)) == 'test_') {
            $dir = BP . DS . 'tests' . DS . 'integration' . DS . 'modules' . DS . uc_words($moduleName, DS);

            switch ($type) {
                case 'etc':
                    $dir .= DS.'etc';
                    break;

                case 'controllers':
                    $dir .= DS.'controllers';
                    break;

                case 'sql':
                    $dir .= DS.'sql';
                    break;
                case 'data':
                    $dir .= DS.'data';
                    break;

                case 'locale':
                    $dir .= DS.'locale';
                    break;
            }

            $dir = str_replace('/', DS, $dir);
            return $dir;
        } else {
            return parent::getModuleDir($type, $moduleName);
        }
    }
}
